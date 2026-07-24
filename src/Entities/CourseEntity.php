<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Entities;

use CmsOrbit\Core\Foundation\Entity\Entity;
use CmsOrbit\Core\Screen\Fields\CheckBox;
use CmsOrbit\Core\Screen\Fields\DateTimer;
use CmsOrbit\Core\Screen\Fields\Input;
use CmsOrbit\Core\Screen\Fields\RichText;
use CmsOrbit\Core\Screen\Fields\Select;
use CmsOrbit\Core\Screen\Fields\TextArea;
use CmsOrbit\Core\Screen\TD;
use CmsOrbit\Lms\Concerns\HasLmsPermissions;
use CmsOrbit\Lms\Enums\CourseLevel;
use CmsOrbit\Lms\Enums\CourseStatus;
use CmsOrbit\Lms\Models\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class CourseEntity extends Entity
{
    use HasLmsPermissions;

    public static function uriKey(): string
    {
        return 'lms-courses';
    }

    public function model(): string
    {
        return Course::class;
    }

    public function icon(): string
    {
        return 'bs.mortarboard';
    }

    public function sort(): int
    {
        return 5300;
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
        return __('Courses');
    }

    public function singularLabel(): string
    {
        return __('Course');
    }

    public function query(): Builder
    {
        return parent::query()->with('instructor');
    }

    public function fields(): array
    {
        return [
            Input::make('title')->title(__('Title'))->required(),
            Input::make('slug')->title(__('Slug'))->help(__('Leave blank to auto-generate from title.')),
            Input::make('subtitle')->title(__('Subtitle')),
            RichText::make('description')->title(__('Description')),
            Input::make('thumbnail')->title(__('Thumbnail URL')),
            Select::make('instructor_id')->title(__('Instructor'))
                ->fromModel((string) config('lms.user_model'), 'name', 'id')
                ->empty(__('No instructor')),
            Select::make('level')->title(__('Level'))
                ->options(CourseLevel::options())
                ->value(CourseLevel::AllLevels->value),
            Select::make('status')->title(__('Status'))
                ->options(CourseStatus::options())
                ->value(CourseStatus::Draft->value),
            CheckBox::make('is_free')->title(__('Free course'))->sendTrueOrFalse()->value(true),
            Input::make('price')->title(__('Price'))->type('number')->value(0),
            Input::make('sale_price')->title(__('Sale price'))->type('number')
                ->help(__('Optional discounted price.')),
            Input::make('currency')->title(__('Currency'))->value('USD'),
            Input::make('commission_rate')->title(__('Instructor commission (%)'))->type('number')
                ->help(__('Leave blank to use the marketplace default.')),
            Input::make('category')->title(__('Category')),
            Input::make('duration_minutes')->title(__('Duration (minutes)'))->type('number'),
            Input::make('max_students')->title(__('Max students'))->type('number')
                ->help(__('Leave blank for unlimited.')),
            DateTimer::make('published_at')->title(__('Publish at'))->enableTime()->format('Y-m-d H:i:S'),
            Input::make('meta_title')->title(__('Meta Title')),
            TextArea::make('meta_description')->title(__('Meta Description'))->rows(2),
        ];
    }

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('title', __('Title')),
            TD::make('instructor.name', __('Instructor')),
            TD::make('level', __('Level'))
                ->render(fn (Course $course) => $course->level?->label() ?? '—')
                ->filter(TD::FILTER_SELECT, CourseLevel::options()),
            TD::make('status', __('Status'))
                ->render(fn (Course $course) => $course->status?->label() ?? '—')
                ->filter(TD::FILTER_SELECT, CourseStatus::options()),
            TD::make('category', __('Category')),
            TD::make('published_at', __('Published'))->sort(),
            TD::make('created_at', __('Created'))->sort(),
        ];
    }

    public function rules(Model $model): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('lms_courses', 'slug')->ignore($model->getKey()),
            ],
            'level' => ['required', Rule::enum(CourseLevel::class)],
            'status' => ['required', Rule::enum(CourseStatus::class)],
            'instructor_id' => ['nullable', 'integer'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'max_students' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
