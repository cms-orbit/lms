<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Enums;

enum EarningStatus: string
{
    case Pending = 'pending';
    case Available = 'available';
    case Paid = 'paid';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::Available => __('Available'),
            self::Paid => __('Paid out'),
            self::Refunded => __('Refunded'),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->all();
    }
}
