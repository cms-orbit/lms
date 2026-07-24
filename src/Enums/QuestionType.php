<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Enums;

enum QuestionType: string
{
    case Single = 'single';
    case Multiple = 'multiple';
    case TrueFalse = 'true_false';

    public function label(): string
    {
        return match ($this) {
            self::Single => __('Single choice'),
            self::Multiple => __('Multiple choice'),
            self::TrueFalse => __('True / False'),
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
