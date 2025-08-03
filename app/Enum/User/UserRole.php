<?php


namespace App\Enum\User;



enum UserRole: string
{
    case CUSTOMER = 'customer';
    case ADMIN = 'admin';
    case SUPER_ADMIN = 'super_admin';
    case MANAGER = 'manager';

    public function title(): string
    {
        return match ($this) {
            self::CUSTOMER => __('Customer'),
            self::ADMIN => __('Admin'),
            self::SUPER_ADMIN => __('Super Admin'),
            self::MANAGER => __('Manager'),
        };
    }

    public function permissions(): array
    {
        return match ($this) {
            self::CUSTOMER => ['view_products', 'create_orders', 'view_own_orders'],
            self::ADMIN => ['manage_products', 'manage_orders', 'manage_users', 'view_reports'],
            self::SUPER_ADMIN => ['*'], 
            self::MANAGER => ['manage_products', 'manage_orders', 'view_reports'],
        };
    }

    public function isAdmin(): bool
    {
        return in_array($this, [self::ADMIN, self::SUPER_ADMIN, self::MANAGER]);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($item) => [$item->value => $item->title()])
            ->toArray();
    }
}
