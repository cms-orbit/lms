<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Entities;

use CmsOrbit\Core\Foundation\Entity\Entity;
use CmsOrbit\Core\Screen\Fields\CheckBox;
use CmsOrbit\Core\Screen\Fields\Input;
use CmsOrbit\Core\Screen\Fields\ReactField;
use CmsOrbit\Core\Screen\Fields\Select;
use CmsOrbit\Core\Screen\TD;
use CmsOrbit\Lms\Concerns\HasLmsPermissions;
use CmsOrbit\Lms\Models\CertificateTemplate;
use Illuminate\Database\Eloquent\Model;

/**
 * Admin CRUD for certificate templates. The positioned {{placeholder}} elements
 * are authored in the published GUI builder (see `lms:install-frontend`); this
 * screen manages template metadata (name, size, background, default).
 */
class CertificateTemplateEntity extends Entity
{
    use HasLmsPermissions;

    public static function uriKey(): string
    {
        return 'lms-certificate-templates';
    }

    public function model(): string
    {
        return CertificateTemplate::class;
    }

    public function icon(): string
    {
        return 'bs.easel';
    }

    public function sort(): int
    {
        return 5540;
    }

    public function section(): string
    {
        return __('Engagement');
    }

    public function sectionKey(): string
    {
        return 'lms-engagement';
    }

    public function label(): string
    {
        return __('Certificate Templates');
    }

    public function singularLabel(): string
    {
        return __('Certificate Template');
    }

    public function fields(): array
    {
        return [
            Input::make('name')->title(__('Name'))->required(),
            Select::make('orientation')->title(__('Orientation'))
                ->options(['landscape' => __('Landscape'), 'portrait' => __('Portrait')])
                ->value('landscape'),
            Input::make('width')->title(__('Width (px)'))->type('number')->value(1123),
            Input::make('height')->title(__('Height (px)'))->type('number')->value(794),
            Input::make('background')->title(__('Background image URL')),
            CheckBox::make('is_default')->title(__('Default template'))->sendTrueOrFalse(),
            ReactField::make('elements')
                ->title(__('Certificate layout'))
                ->component('lms-certificate-builder')
                ->props([
                    'placeholders' => ['student_name', 'course_title', 'instructor_name', 'issued_date', 'serial'],
                ]),
        ];
    }

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('name', __('Name')),
            TD::make('orientation', __('Orientation')),
            TD::make('is_default', __('Default')),
        ];
    }

    public function rules(Model $model): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'orientation' => ['required', 'in:landscape,portrait'],
            'width' => ['required', 'integer', 'min:200'],
            'height' => ['required', 'integer', 'min:200'],
            'elements' => ['nullable', 'array'],
        ];
    }
}
