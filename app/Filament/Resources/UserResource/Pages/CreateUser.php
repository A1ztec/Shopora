<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use App\Enum\User\UserStatus;
use Illuminate\Support\Facades\Log;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $currentUser = auth()->user();

        if ($currentUser->isSuperAdmin()) {
            $role = $this->data['role'] ?? 'customer';
        } else {
            // Admin can only create customers
            $role = 'customer';
        }

        $this->record->assignRole($role);
        $this->record->update([
            'email_verified_at' => now(),
            'status' => UserStatus::ACTIVE
        ]);

        Log::info('User created', [
            'id' => $this->record->id,
            'email_verified_at' => $this->record->email_verified_at,
            'status' => $this->record->status,
        ]);
    }
}
