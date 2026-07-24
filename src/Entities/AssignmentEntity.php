<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Entities;

use CmsOrbit\Core\Foundation\Entity\Entity;
use CmsOrbit\Core\Screen\Fields\DateTimer;
use CmsOrbit\Core\Screen\Fields\Input;
use CmsOrbit\Core\Screen\Fields\RichText;
use CmsOrbit\Core\Screen\Fields\Select;
use CmsOrbit\Core\Screen\TD;
use CmsOrbit\Lms\Concerns\HasLmsPermissions;
use CmsOrbit\Lms\Models\Assignment;
use CmsOrbit\Lms\Models\Course;
use CmsOrbit\Lms\Models\CourseSection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AssignmentEntity extends Entity
{
    use HasLmsPermissions;

    public static function uriKey(): string
    {
        return 'lms-assignments';
    }

    public function model(): string
    {
        return Assignment::class;
    }

    public function icon(): string
    {
        return 'bs.file-earmark-text';
    }

    public function sort(): int
    {
        return 5520;
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
        return __('Assignments');
    }

    public function singularLabel(): string
    {
        return __('Assignment');
    }

    public function query(): Builder
    {
        return parent::query()->with('course');
    }

    public function fields(): array
    {
        return [
            Select::make('course_id')->title(__('Course'))
                ->fromModel(Course::class, 'title', 'id')
                ->required(),
            Select::make('section_id')->title(__('Section'))
                ->fromModel(CourseSection::class, 'title', 'id')
                ->empty(__('Unassigned')),
            Input::make('title')->title(__('Title'))->required(),
            RichText::make('instructions')->title(__('Instructions')),
            Input::make('max_points')->title(__('Max points'))->type('number')->value(100),
            Input::make('pass_points')->title(__('Pass points'))->type('number')->value(0),
            DateTimer::make('due_at')->title(__('Due at'))->enableTime()->format('Y-m-d H:i:S'),
            Input::make('order')->title(__('Order'))->type('number')->value(0),
        ];
    }

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('course.title', __('Course')),
            TD::make('title', __('Title')),
            TD::make('max_points', __('Max points'))->sort(),
            TD::make('due_at', __('Due'))->sort(),
        ];
    }

    public function rules(Model $model): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:lms_courses,id'],
            'section_id' => ['nullable', 'integer', 'exists:lms_course_sections,id'],
            'title' => ['required', 'string', 'max:255'],
            'max_points' => ['required', 'integer', 'min:1'],
            'pass_points' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
