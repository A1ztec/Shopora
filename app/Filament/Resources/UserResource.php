<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Infolists;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enum\User\UserRole;
use App\Enum\User\UserStatus;
use Filament\Facades\Filament;
use Illuminate\Validation\Rule;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Password;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Infolists\Components\ImageEntry;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\UserResource\Pages;
use Filament\Infolists\Components\Section as infoSection;

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
            Section::make(__('Personal Information'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label(__('Email'))
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    TextInput::make('phone')
                        ->label(__('Phone'))
                        ->tel()
                        ->nullable()
                        ->rule(function (?User $record) {
                            return $record
                                ? ['nullable', Rule::unique('users', 'phone')->ignoreModel($record)]
                                : ['nullable', Rule::unique('users', 'phone')];
                        })
                        ->maxLength(255)
                        ->placeholder('+1234567890'),

                    FileUpload::make('avatar')
                        ->label(__('Avatar'))
                        ->image()
                        ->disk('s3')
                        ->directory('profile/images')
                        ->imageEditor(false)
                        ->circleCropper(false),
                ])
                ->columns(2),

            Section::make(__('Account Settings'))
                ->schema([

                    Select::make('role')
                        ->label(__('Role'))
                        ->options(function () {
                            $currentUser = auth()->user();

                            if ($currentUser->isSuperAdmin()) {
                                return UserRole::options();
                            }

                            if ($currentUser->hasRole('admin')) {
                                return ['customer' => __('Customer')];
                            }

                            return [];
                        })
                        ->required()
                        ->default('customer'),
                ]),

            Section::make(__('Password'))
                ->schema([
                    TextInput::make('password')
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
                ImageColumn::make('avatar')
                    ->label(__('Avatar'))
                    ->disk('s3')
                    ->circular()
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name ?? 'User')),

                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('roles.name')
                    ->label(__('Roles'))
                    ->badge(),


                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn($state) => $state instanceof UserStatus ? $state->color() : UserStatus::from($state)->color()),

                IconColumn::make('email_verified_at')
                    ->label(__('Verified'))
                    ->boolean()
                    ->getStateUsing(fn($record) => filled($record->email_verified_at)),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(UserStatus::options()),

                TernaryFilter::make('email_verified')
                    ->label(__('Email Verified'))
                    ->queries(
                        true: fn($query) => $query->whereNotNull('email_verified_at'),
                        false: fn($query) => $query->whereNull('email_verified_at'),
                    ),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('verify_emails')
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
            infoSection::make(__('User Information'))
                ->schema([
                    ImageEntry::make('avatar')
                        ->disk('s3')
                        ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name ?? 'User'))
                        ->label(__('Avatar'))
                        ->circular(),

                    TextEntry::make('name')->label(__('Name')),
                    TextEntry::make('email')->copyable()->label(__('Email')),
                    TextEntry::make('phone')->label(__('Phone')),

                    TextEntry::make('status')
                        ->label(__('Status'))
                        ->badge()
                        ->color(fn(UserStatus $state) => $state->color()),
                ]),

            infoSection::make(__('Account Dates'))
                ->schema([
                    TextEntry::make('email_verified_at')->label(__('Email Verified At'))->dateTime(),
                    TextEntry::make('created_at')->label(__('Created At'))->dateTime(),
                    TextEntry::make('updated_at')->label(__('Updated At'))->dateTime(),
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
        return (string) static::getModel()::count();
    }

    public static function getEloquentQuery(): Builder
    {
        $query =  parent::getEloquentQuery();
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return $query;
        }
        if ($user->hasRole('admin')) {
            return  $query->whereHas('roles', fn($q) => $q->where('name', 'customer'));
        }

        return $query->whereRaw('1 = 0');
    }
}
