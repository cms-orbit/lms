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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CourseSectionEntity extends Entity
{
    use HasLmsPermissions;

    public static function uriKey(): string
    {
        return 'lms-sections';
    }

    public function model(): string
    {
        return CourseSection::class;
    }

    public function icon(): string
    {
        return 'bs.list-nested';
    }

    public function sort(): int
    {
        return 5310;
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
        return __('Sections');
    }

    public function singularLabel(): string
    {
        return __('Section');
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
            Input::make('title')->title(__('Title'))->required(),
            TextArea::make('summary')->title(__('Summary'))->rows(2),
            Input::make('order')->title(__('Order'))->type('number')->value(0),
        ];
    }

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('course.title', __('Course')),
            TD::make('title', __('Title')),
            TD::make('order', __('Order'))->sort(),
        ];
    }

    public function rules(Model $model): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:lms_courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
