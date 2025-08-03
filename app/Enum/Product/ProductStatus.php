<?php

// app/Enums/ProductStatus.php
namespace App\Enums;

enum ProductStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case DRAFT = 'draft';
    case OUT_OF_STOCK = 'out_of_stock';

    public function title(): string
    {
        return match ($this) {
            self::ACTIVE => __('Active'),
            self::INACTIVE => __('Inactive'),
            self::DRAFT => __('Draft'),
            self::OUT_OF_STOCK => __('Out of Stock'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'danger',
            self::DRAFT => 'warning',
            self::OUT_OF_STOCK => 'secondary',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($item) => [$item->value => $item->title()])
            ->toArray();
    }
}
