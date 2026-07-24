<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Enums;

enum DripType: string
{
    case Off = 'off';
    case AfterDays = 'after_days';
    case ByDate = 'by_date';
    case Sequential = 'sequential';
    case Prerequisite = 'prerequisite';

    public function label(): string
    {
        return match ($this) {
            self::Off => __('No drip'),
            self::AfterDays => __('X days after enrollment'),
            self::ByDate => __('On a specific date'),
            self::Sequential => __('Sequentially (finish previous)'),
            self::Prerequisite => __('After a prerequisite lesson'),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->label()])
            ->all();
    }
}
