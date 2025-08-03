<?php


namespace App\Enum\Order;
enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function title(): string
    {
        return match ($this) {
            self::PENDING => __('Pending'),
            self::CONFIRMED => __('Confirmed'),
            self::PROCESSING => __('Processing'),
            self::SHIPPED => __('Shipped'),
            self::DELIVERED => __('Delivered'),
            self::CANCELLED => __('Cancelled'),
            self::REFUNDED => __('Refunded'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::CONFIRMED => 'info',
            self::PROCESSING => 'primary',
            self::SHIPPED => 'success',
            self::DELIVERED => 'success',
            self::CANCELLED => 'danger',
            self::REFUNDED => 'secondary',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PENDING => __('Order is pending confirmation'),
            self::CONFIRMED => __('Order has been confirmed'),
            self::PROCESSING => __('Order is being prepared'),
            self::SHIPPED => __('Order has been shipped'),
            self::DELIVERED => __('Order has been delivered'),
            self::CANCELLED => __('Order has been cancelled'),
            self::REFUNDED => __('Order has been refunded'),
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($item) => [$item->value => $item->title()])
            ->toArray();
    }
}
