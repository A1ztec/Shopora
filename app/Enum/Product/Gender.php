<?php

namespace App\Enum\Product;

enum Gender: string
{
    case MALE = 'male';
    case FEMALE = 'female';
    case UNISEX = 'unisex';
    case KIDS = 'kids';

    public function title(): string
    {
        return match ($this) {
            self::MALE => __('Male'),
            self::FEMALE => __('Female'),
            self::UNISEX => __('Unisex'),
            self::KIDS => __('Kids'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::MALE => '♂️',
            self::FEMALE => '♀️',
            self::UNISEX => '⚥',
            self::KIDS => '👶',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($item) => [$item->value => $item->title()])
            ->toArray();
    }
}
