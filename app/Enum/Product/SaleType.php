<?php

namespace App\Enum\Product;

enum SaleType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';
    case BUY_ONE_GET_ONE = 'buy_one_get_one';
    case CLEARANCE = 'clearance';
    case FLASH_SALE = 'flash_sale';

    public function title(): string
    {
        return match ($this) {
            self::PERCENTAGE => __('Percentage Off'),
            self::FIXED => __('Fixed Amount Off'),
            self::BUY_ONE_GET_ONE => __('Buy One Get One'),
            self::CLEARANCE => __('Clearance Sale'),
            self::FLASH_SALE => __('Flash Sale'),
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::PERCENTAGE => 'SALE',
            self::FIXED => 'DISCOUNT',
            self::BUY_ONE_GET_ONE => 'BOGO',
            self::CLEARANCE => 'CLEARANCE',
            self::FLASH_SALE => 'FLASH',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PERCENTAGE => 'danger',
            self::FIXED => 'warning',
            self::BUY_ONE_GET_ONE => 'success',
            self::CLEARANCE => 'info',
            self::FLASH_SALE => 'primary',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($item) => [$item->value => $item->title()])
            ->toArray();
    }
}
