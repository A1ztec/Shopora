<?php

namespace App\Enums\System;

enum ApiStatus: string
{
    case SUCCESS = 'success';
    case ERROR = 'error';
    case VALIDATION_FAILED = 'validation_failed';
    case UNAUTHORIZED = 'unauthorized';
    case NOT_FOUND = 'not_found';

    public function message(): string
    {
        return match ($this) {
            self::SUCCESS => __('Request successful'),
            self::ERROR => __('An error occurred'),
            self::VALIDATION_FAILED => __('Validation failed'),
            self::UNAUTHORIZED => __('Unauthorized access'),
            self::NOT_FOUND => __('Resource not found'),
        };
    }

    public function httpCode(): int
    {
        return match ($this) {
            self::SUCCESS => 200,
            self::ERROR => 500,
            self::VALIDATION_FAILED => 422,
            self::UNAUTHORIZED => 401,
            self::NOT_FOUND => 404,
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($item) => [$item->value => $item->message()])
            ->toArray();
    }
}
