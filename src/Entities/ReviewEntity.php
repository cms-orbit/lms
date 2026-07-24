<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Entities;

use CmsOrbit\Core\Foundation\Entity\Entity;
use CmsOrbit\Core\Screen\Fields\CheckBox;
use CmsOrbit\Core\Screen\Fields\Input;
use CmsOrbit\Core\Screen\Fields\Select;
use CmsOrbit\Core\Screen\Fields\TextArea;
use CmsOrbit\Core\Screen\TD;
use CmsOrbit\Lms\Concerns\HasLmsPermissions;
use CmsOrbit\Lms\Models\Course;
use CmsOrbit\Lms\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReviewEntity extends Entity
{
    use HasLmsPermissions;

    public static function uriKey(): string
    {
        return 'lms-reviews';
    }

    public function model(): string
    {
        return Review::class;
    }

    public function icon(): string
    {
        return 'bs.star';
    }

    public function sort(): int
    {
        return 5500;
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
        return __('Reviews');
    }

    public function singularLabel(): string
    {
        return __('Review');
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
            Input::make('rating')->title(__('Rating (1-5)'))->type('number')->value(5),
            TextArea::make('comment')->title(__('Comment'))->rows(3),
            CheckBox::make('approved')->title(__('Approved'))->sendTrueOrFalse()->value(true),
        ];
    }

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('course.title', __('Course')),
            TD::make('student.name', __('Student')),
            TD::make('rating', __('Rating'))->sort(),
            TD::make('approved', __('Approved')),
            TD::make('created_at', __('Created'))->sort(),
        ];
    }

    public function rules(Model $model): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:lms_courses,id'],
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }
}
