<?php

namespace App\Enum\Product;

enum ProductVariationType: string
{
    case SIZE = 'size';
    case COLOR = 'color';
    case MATERIAL = 'material';


    public function title(): string
    {
        return match ($this) {
            self::SIZE => __('Size'),
            self::COLOR => __('Color'),
            self::MATERIAL => __('Material'),
        };
    }

    public function commonValues(): array
    {
        return match ($this) {
            self::SIZE => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
            self::COLOR => ['Red', 'Blue', 'Green', 'Black', 'White', 'Yellow'],
            self::MATERIAL => ['Cotton', 'Polyester', 'Wool', 'Silk', 'Leather'],
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($item) => [$item->value => $item->title()])
            ->toArray();
    }
}
