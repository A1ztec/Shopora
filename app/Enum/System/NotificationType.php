<?php

namespace App\Enum\System;


enum NotificationType: string
{
    case ORDER_PLACED = 'order_placed';
    case ORDER_SHIPPED = 'order_shipped';
    case ORDER_DELIVERED = 'order_delivered';
    case PAYMENT_RECEIVED = 'payment_received';
    case STOCK_LOW = 'stock_low';
    case PROMOTION = 'promotion';
    case SYSTEM_ALERT = 'system_alert';

    public function title(): string
    {
        return match ($this) {
            self::ORDER_PLACED => __('Order Placed'),
            self::ORDER_SHIPPED => __('Order Shipped'),
            self::ORDER_DELIVERED => __('Order Delivered'),
            self::PAYMENT_RECEIVED => __('Payment Received'),
            self::STOCK_LOW => __('Stock Low'),
            self::PROMOTION => __('Promotion'),
            self::SYSTEM_ALERT => __('System Alert'),
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($item) => [$item->value => $item->title()])
            ->toArray();
    }
}
