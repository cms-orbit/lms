<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Entities;

use CmsOrbit\Core\Foundation\Entity\Entity;
use CmsOrbit\Core\Screen\Fields\DateTimer;
use CmsOrbit\Core\Screen\Fields\Input;
use CmsOrbit\Core\Screen\Fields\Select;
use CmsOrbit\Core\Screen\TD;
use CmsOrbit\Lms\Concerns\HasLmsPermissions;
use CmsOrbit\Lms\Enums\EnrollmentStatus;
use CmsOrbit\Lms\Models\Course;
use CmsOrbit\Lms\Models\Enrollment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class EnrollmentEntity extends Entity
{
    use HasLmsPermissions;

    public static function uriKey(): string
    {
        return 'lms-enrollments';
    }

    public function model(): string
    {
        return Enrollment::class;
    }

    public function icon(): string
    {
        return 'bs.person-check';
    }

    public function sort(): int
    {
        return 5350;
    }

    public function section(): string
    {
        return __('Learning');
    }

    public function sectionKey(): string
    {
        return 'lms';
    }

    public function label(): string
    {
        return __('Enrollments');
    }

    public function singularLabel(): string
    {
        return __('Enrollment');
    }

    public function query(): Builder
    {
        return parent::query()->with(['course', 'student']);
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
            Select::make('status')->title(__('Status'))
                ->options(EnrollmentStatus::options())
                ->value(EnrollmentStatus::Active->value),
            Input::make('progress')->title(__('Progress (%)'))->type('number')->value(0),
            DateTimer::make('enrolled_at')->title(__('Enrolled at'))->enableTime()->format('Y-m-d H:i:S'),
            DateTimer::make('completed_at')->title(__('Completed at'))->enableTime()->format('Y-m-d H:i:S'),
        ];
    }

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('course.title', __('Course')),
            TD::make('student.name', __('Student')),
            TD::make('status', __('Status'))
                ->render(fn (Enrollment $enrollment) => $enrollment->status?->label() ?? '—')
                ->filter(TD::FILTER_SELECT, EnrollmentStatus::options()),
            TD::make('progress', __('Progress'))
                ->render(fn (Enrollment $enrollment) => $enrollment->progress.'%')
                ->sort(),
            TD::make('enrolled_at', __('Enrolled'))->sort(),
        ];
    }

    public function rules(Model $model): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:lms_courses,id'],
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'status' => ['required', Rule::enum(EnrollmentStatus::class)],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
