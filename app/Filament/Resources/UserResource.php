<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Forms;
use Filament\Tables;
use Filament\Infolists;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use App\Filament\Resources\UserResource\Pages;
use App\Enum\User\UserStatus;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';


    public static function getNavigationLabel(): string
    {
        return __('Users');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Personal Information'))
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('Name'))
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label(__('Email'))
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('phone')
                        ->label(__('Phone'))
                        ->tel()
                        ->nullable()
                        ->rule(function (?User $record) {
                            return ['nullable', Rule::unique('users', 'phone')->ignoreModel($record)];
                        })
                        ->maxLength(255)
                        ->placeholder('+1234567890'),

                    Forms\Components\FileUpload::make('avatar')
                        ->label(__('Avatar'))
                        ->image()
                        ->disk('s3')
                        ->directory('profile/images')
                        ->imageEditor(false)
                        ->circleCropper(false),
                ])
                ->columns(2),

            Forms\Components\Section::make(__('Account Settings'))
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label(__('Status'))
                        ->options(UserStatus::options())
                        ->required()
                        ->default(UserStatus::PENDING_VERIFICATION->value),
                ]),

            Forms\Components\Section::make(__('Password'))
                ->schema([
                    Forms\Components\TextInput::make('password')
                        ->label(__('Password'))
                        ->password()
                        ->dehydrateStateUsing(fn($state) => bcrypt($state))
                        ->dehydrated(fn($state) => filled($state))
                        ->required(fn(string $context) => $context === 'create')
                        ->rules([
                            Password::min(8)
                                ->mixedCase()
                                ->numbers()
                                ->symbols()
                                ->uncompromised(),
                        ]),
                ])
                ->visible(fn(string $context) => in_array($context, ['create', 'edit'])),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label(__('Avatar'))
                    ->disk('s3')
                    ->circular()
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name ?? 'User')),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn($state) => $state instanceof UserStatus ? $state->color() : UserStatus::from($state)->color()),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label(__('Verified'))
                    ->boolean()
                    ->getStateUsing(fn($record) => filled($record->email_verified_at)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(UserStatus::options()),

                Tables\Filters\TernaryFilter::make('email_verified')
                    ->label(__('Email Verified'))
                    ->queries(
                        true: fn($query) => $query->whereNotNull('email_verified_at'),
                        false: fn($query) => $query->whereNull('email_verified_at'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('verify_emails')
                        ->label(__('Verify Emails'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if (!$record->email_verified_at) {
                                    $record->update([
                                        'email_verified_at' => now(),
                                        'status' => UserStatus::ACTIVE->value,
                                    ]);
                                }
                            });
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make(__('User Information'))
                ->schema([
                    Infolists\Components\ImageEntry::make('avatar')
                        ->disk('s3')
                        ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name ?? 'User'))
                        ->label(__('Avatar'))
                        ->circular(),

                    Infolists\Components\TextEntry::make('name')->label(__('Name')),
                    Infolists\Components\TextEntry::make('email')->copyable()->label(__('Email')),
                    Infolists\Components\TextEntry::make('phone')->label(__('Phone')),

                    Infolists\Components\TextEntry::make('status')
                        ->label(__('Status'))
                        ->badge()
                        ->color(fn(UserStatus $state) => $state->color()),
                ]),

            Infolists\Components\Section::make(__('Account Dates'))
                ->schema([
                    Infolists\Components\TextEntry::make('email_verified_at')->label(__('Email Verified At'))->dateTime(),
                    Infolists\Components\TextEntry::make('created_at')->label(__('Created At'))->dateTime(),
                    Infolists\Components\TextEntry::make('updated_at')->label(__('Updated At'))->dateTime(),
                ])
                ->columns(3),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', UserStatus::PENDING_VERIFICATION->value)
            ->count();
    }
}
