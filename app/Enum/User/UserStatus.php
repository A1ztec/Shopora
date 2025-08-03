<?php




namespace App\Enums\User;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case PENDING_VERIFICATION = 'pending_verification';
    case BANNED = 'banned';

    public function title(): string
    {
        return match ($this) {
            self::ACTIVE => __('Active'),
            self::INACTIVE => __('Inactive'),
            self::SUSPENDED => __('Suspended'),
            self::PENDING_VERIFICATION => __('Pending Verification'),
            self::BANNED => __('Banned'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'secondary',
            self::SUSPENDED => 'warning',
            self::PENDING_VERIFICATION => 'info',
            self::BANNED => 'danger',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ACTIVE => __('User account is active and can perform all actions'),
            self::INACTIVE => __('User account is inactive but can be reactivated'),
            self::SUSPENDED => __('User account is temporarily suspended'),
            self::PENDING_VERIFICATION => __('User account is pending email verification'),
            self::BANNED => __('User account is permanently banned'),
        };
    }

    public function canLogin(): bool
    {
        return match ($this) {
            self::ACTIVE => true,
            self::INACTIVE, self::SUSPENDED, self::PENDING_VERIFICATION, self::BANNED => false,
        };
    }

    public function canPlaceOrder(): bool
    {
        return match ($this) {
            self::ACTIVE => true,
            self::INACTIVE, self::SUSPENDED, self::PENDING_VERIFICATION, self::BANNED => false,
        };
    }

    public function canLeaveReview(): bool
    {
        return match ($this) {
            self::ACTIVE => true,
            self::INACTIVE, self::SUSPENDED, self::PENDING_VERIFICATION, self::BANNED => false,
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ACTIVE => '✅',
            self::INACTIVE => '⏸️',
            self::SUSPENDED => '⚠️',
            self::PENDING_VERIFICATION => '📧',
            self::BANNED => '🚫',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($item) => [$item->value => $item->title()])
            ->toArray();
    }

    public static function adminOptions(): array
    {
        // Only show certain statuses in admin panel
        return collect([self::ACTIVE, self::INACTIVE, self::SUSPENDED, self::BANNED])
            ->mapWithKeys(fn($item) => [$item->value => $item->title()])
            ->toArray();
    }
}
