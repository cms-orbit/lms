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
use CmsOrbit\Lms\Models\CourseQuestion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CourseQuestionEntity extends Entity
{
    use HasLmsPermissions;

    public static function uriKey(): string
    {
        return 'lms-qa';
    }

    public function model(): string
    {
        return CourseQuestion::class;
    }

    public function icon(): string
    {
        return 'bs.chat-left-dots';
    }

    public function sort(): int
    {
        return 5510;
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
        return __('Q&A');
    }

    public function singularLabel(): string
    {
        return __('Question');
    }

    public function query(): Builder
    {
        return parent::query()->with(['course', 'author'])->withCount('answers')->latest();
    }

    public function fields(): array
    {
        return [
            Select::make('course_id')->title(__('Course'))
                ->fromModel(Course::class, 'title', 'id')
                ->required(),
            Select::make('user_id')->title(__('Author'))
                ->fromModel((string) config('lms.user_model'), 'name', 'id')
                ->required(),
            Input::make('title')->title(__('Title')),
            TextArea::make('body')->title(__('Question'))->rows(4)->required(),
            CheckBox::make('resolved')->title(__('Resolved'))->sendTrueOrFalse(),
        ];
    }

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('course.title', __('Course')),
            TD::make('author.name', __('Author')),
            TD::make('title', __('Title')),
            TD::make('answers_count', __('Answers'))->sort(),
            TD::make('resolved', __('Resolved')),
        ];
    }

    public function rules(Model $model): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:lms_courses,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'body' => ['required', 'string'],
        ];
    }
}
