<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Entities;

use CmsOrbit\Core\Foundation\Entity\Entity;
use CmsOrbit\Core\Screen\Fields\Input;
use CmsOrbit\Core\Screen\Fields\Select;
use CmsOrbit\Core\Screen\TD;
use CmsOrbit\Lms\Concerns\HasLmsPermissions;
use CmsOrbit\Lms\Models\Certificate;
use CmsOrbit\Lms\Models\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CertificateEntity extends Entity
{
    use HasLmsPermissions;

    public static function uriKey(): string
    {
        return 'lms-certificates';
    }

    public function model(): string
    {
        return Certificate::class;
    }

    public function icon(): string
    {
        return 'bs.award';
    }

    public function sort(): int
    {
        return 5530;
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
        return __('Certificates');
    }

    public function singularLabel(): string
    {
        return __('Certificate');
    }

    public function query(): Builder
    {
        return parent::query()->with(['course', 'student'])->latest();
    }

    public function fields(): array
    {
        return [
            Select::make('course_id')->title(__('Course'))
                ->fromModel(Course::class, 'title', 'id')
                ->required(),
            Select::make('student_id')->title(__('Student'))
                ->fromModel((string) config('lms.user_model'), 'name', 'id')
                ->required(),
            Input::make('template')->title(__('Template'))->value('default'),
        ];
    }

    public function columns(): array
    {
        return [
            TD::make('serial', __('Serial')),
            TD::make('course.title', __('Course')),
            TD::make('student.name', __('Student')),
            TD::make('issued_at', __('Issued'))->sort(),
        ];
    }

    public function rules(Model $model): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:lms_courses,id'],
            'student_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
