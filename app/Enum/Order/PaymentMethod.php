<?php

namespace App\Enum\Order;

enum PaymentMethod: string
{
    case CREDIT_CARD = 'credit_card';
    case DEBIT_CARD = 'debit_card';
    case PHONE_WALLET = 'phone_wallet';
    case CASH_ON_DELIVERY = 'cash_on_delivery';


    public function title(): string
    {
        return match ($this) {
            self::CREDIT_CARD => __('Credit Card'),
            self::DEBIT_CARD => __('Debit Card'),
            self::PHONE_WALLET => __('Phone Wallet'),
            self::CASH_ON_DELIVERY => __('Cash on Delivery'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CREDIT_CARD => '💳',
            self::DEBIT_CARD => '💳',
            self::PHONE_WALLET => '📱',
            self::CASH_ON_DELIVERY => '💵',
        };
    }

    public function isOnline(): bool
    {
        return match ($this) {
            self::CREDIT_CARD, self::PHONE_WALLET, self::DEBIT_CARD=> true,
             self::CASH_ON_DELIVERY => false,
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($item) => [$item->value => $item->title()])
            ->toArray();
    }
}
