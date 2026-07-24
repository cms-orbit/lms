<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Entities;

use CmsOrbit\Core\Foundation\Entity\Entity;
use CmsOrbit\Core\Screen\Fields\Input;
use CmsOrbit\Core\Screen\Fields\Select;
use CmsOrbit\Core\Screen\Fields\TextArea;
use CmsOrbit\Core\Screen\TD;
use CmsOrbit\Lms\Concerns\HasLmsPermissions;
use CmsOrbit\Lms\Models\Course;
use CmsOrbit\Lms\Models\CourseSection;
use CmsOrbit\Lms\Models\Quiz;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class QuizEntity extends Entity
{
    use HasLmsPermissions;

    public static function uriKey(): string
    {
        return 'lms-quizzes';
    }

    public function model(): string
    {
        return Quiz::class;
    }

    public function icon(): string
    {
        return 'bs.patch-question';
    }

    public function sort(): int
    {
        return 5330;
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
        return __('Quizzes');
    }

    public function singularLabel(): string
    {
        return __('Quiz');
    }

    public function query(): Builder
    {
        return parent::query()->with(['course', 'section'])->withCount('questions');
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
            TextArea::make('description')->title(__('Description'))->rows(2),
            Input::make('pass_mark')->title(__('Pass mark (%)'))->type('number')->value(70),
            Input::make('time_limit_minutes')->title(__('Time limit (minutes)'))->type('number')
                ->help(__('Leave blank for no limit.')),
            Input::make('max_attempts')->title(__('Max attempts'))->type('number')
                ->help(__('Leave blank for unlimited.')),
            Input::make('order')->title(__('Order'))->type('number')->value(0),
        ];
    }

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('course.title', __('Course')),
            TD::make('title', __('Title')),
            TD::make('questions_count', __('Questions'))->sort(),
            TD::make('pass_mark', __('Pass %'))->sort(),
            TD::make('order', __('Order'))->sort(),
        ];
    }

    public function rules(Model $model): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:lms_courses,id'],
            'section_id' => ['nullable', 'integer', 'exists:lms_course_sections,id'],
            'title' => ['required', 'string', 'max:255'],
            'pass_mark' => ['required', 'integer', 'min:0', 'max:100'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
