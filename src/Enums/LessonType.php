<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Enums;

enum LessonType: string
{
    case Video = 'video';
    case Text = 'text';

    public function label(): string
    {
        return match ($this) {
            self::Video => __('Video'),
            self::Text => __('Text'),
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
