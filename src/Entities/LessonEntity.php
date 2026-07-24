<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Entities;

use CmsOrbit\Core\Foundation\Entity\Entity;
use CmsOrbit\Core\Screen\Fields\CheckBox;
use CmsOrbit\Core\Screen\Fields\DateTimer;
use CmsOrbit\Core\Screen\Fields\Input;
use CmsOrbit\Core\Screen\Fields\RichText;
use CmsOrbit\Core\Screen\Fields\Select;
use CmsOrbit\Core\Screen\TD;
use CmsOrbit\Lms\Concerns\HasLmsPermissions;
use CmsOrbit\Lms\Enums\LessonType;
use CmsOrbit\Lms\Models\Course;
use CmsOrbit\Lms\Models\CourseSection;
use CmsOrbit\Lms\Models\Lesson;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class LessonEntity extends Entity
{
    use HasLmsPermissions;

    public static function uriKey(): string
    {
        return 'lms-lessons';
    }

    public function model(): string
    {
        return Lesson::class;
    }

    public function icon(): string
    {
        return 'bs.play-btn';
    }

    public function sort(): int
    {
        return 5320;
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
        return __('Lessons');
    }

    public function singularLabel(): string
    {
        return __('Lesson');
    }

    public function query(): Builder
    {
        return parent::query()->with(['course', 'section']);
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
            Input::make('slug')->title(__('Slug'))->help(__('Leave blank to auto-generate.')),
            Select::make('type')->title(__('Type'))
                ->options(LessonType::options())
                ->value(LessonType::Video->value),
            Input::make('video_url')->title(__('Video URL')),
            Input::make('video_provider')->title(__('Video provider'))
                ->help(__('e.g. youtube, vimeo, mp4.')),
            RichText::make('content')->title(__('Content')),
            Input::make('duration_seconds')->title(__('Duration (seconds)'))->type('number'),
            Input::make('order')->title(__('Order'))->type('number')->value(0),
            CheckBox::make('is_preview')->title(__('Free preview'))->sendTrueOrFalse(),
            Input::make('drip_days')->title(__('Drip: days after enrollment'))->type('number')
                ->help(__('Used when the course drip schedule is "X days after enrollment".')),
            DateTimer::make('drip_date')->title(__('Drip: release date'))->enableTime()->format('Y-m-d H:i:S')
                ->help(__('Used when the course drip schedule is "On a specific date".')),
            Select::make('drip_prerequisite_id')->title(__('Drip: prerequisite lesson'))
                ->fromModel(Lesson::class, 'title', 'id')
                ->empty(__('None'))
                ->help(__('Used when the course drip schedule is "After a prerequisite lesson".')),
        ];
    }

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('course.title', __('Course')),
            TD::make('section.title', __('Section')),
            TD::make('title', __('Title')),
            TD::make('type', __('Type'))
                ->render(fn (Lesson $lesson) => $lesson->type?->label() ?? '—')
                ->filter(TD::FILTER_SELECT, LessonType::options()),
            TD::make('order', __('Order'))->sort(),
        ];
    }

    public function rules(Model $model): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:lms_courses,id'],
            'section_id' => ['nullable', 'integer', 'exists:lms_course_sections,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(LessonType::class)],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
