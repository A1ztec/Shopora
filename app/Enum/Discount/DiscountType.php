<?php

namespace App\Enum\Discount;


enum DiscountType: string
{
    case FIXED = 'fixed';
    case PERCENTAGE = 'percentage';

    public function title(): string
    {
        return match ($this) {
            self::FIXED => __('Fixed Amount'),
            self::PERCENTAGE => __('Percentage'),
        };
    }

    public function symbol(): string
    {
        return match ($this) {
            self::FIXED => '$',
            self::PERCENTAGE => '%',
        };
    }

    public function calculate(float $value, float $amount): float
    {
        return match ($this) {
            self::FIXED => $value,
            self::PERCENTAGE => ($amount * $value) / 100,
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($item) => [$item->value => $item->title()])
            ->toArray();
    }
}

            


