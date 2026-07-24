<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Entities;

use CmsOrbit\Core\Foundation\Entity\Entity;
use CmsOrbit\Core\Screen\Fields\Input;
use CmsOrbit\Core\Screen\Fields\Select;
use CmsOrbit\Core\Screen\Fields\TextArea;
use CmsOrbit\Core\Screen\TD;
use CmsOrbit\Lms\Concerns\HasLmsPermissions;
use CmsOrbit\Lms\Enums\QuestionType;
use CmsOrbit\Lms\Models\Quiz;
use CmsOrbit\Lms\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class QuizQuestionEntity extends Entity
{
    use HasLmsPermissions;

    public static function uriKey(): string
    {
        return 'lms-questions';
    }

    public function model(): string
    {
        return QuizQuestion::class;
    }

    public function icon(): string
    {
        return 'bs.question-circle';
    }

    public function sort(): int
    {
        return 5340;
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
        return __('Quiz Questions');
    }

    public function singularLabel(): string
    {
        return __('Quiz Question');
    }

    public function query(): Builder
    {
        return parent::query()->with('quiz');
    }

    public function fields(): array
    {
        return [
            Select::make('quiz_id')->title(__('Quiz'))
                ->fromModel(Quiz::class, 'title', 'id')
                ->required(),
            TextArea::make('question')->title(__('Question'))->rows(2)->required(),
            Select::make('type')->title(__('Type'))
                ->options(QuestionType::options())
                ->value(QuestionType::Single->value),
            TextArea::make('options')->title(__('Options'))->rows(4)
                ->help(__('One option per line.')),
            Input::make('correct')->title(__('Correct option indexes'))
                ->help(__('Zero-based, comma-separated (e.g. 0,2).')),
            Input::make('points')->title(__('Points'))->type('number')->value(1),
            Input::make('order')->title(__('Order'))->type('number')->value(0),
            TextArea::make('explanation')->title(__('Explanation'))->rows(2),
        ];
    }

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('quiz.title', __('Quiz')),
            TD::make('question', __('Question'))
                ->render(fn (QuizQuestion $q) => Str::limit((string) $q->question, 60)),
            TD::make('type', __('Type'))
                ->render(fn (QuizQuestion $q) => $q->type?->label() ?? '—')
                ->filter(TD::FILTER_SELECT, QuestionType::options()),
            TD::make('points', __('Points'))->sort(),
            TD::make('order', __('Order'))->sort(),
        ];
    }

    /**
     * Normalise the newline-delimited options and comma-separated correct
     * indexes from the admin form into the JSON columns the model casts.
     */
    public function save(Request $request, Model $model): void
    {
        $options = collect(preg_split('/\r\n|\r|\n/', (string) $request->input('options')))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();

        $correct = collect(explode(',', (string) $request->input('correct')))
            ->map(fn ($index) => trim((string) $index))
            ->filter(fn ($index) => $index !== '')
            ->map(fn ($index) => (int) $index)
            ->values()
            ->all();

        $model->fill($request->except(['options', 'correct']));
        $model->setAttribute('options', $options);
        $model->setAttribute('correct', $correct);
        $model->save();
    }

    public function rules(Model $model): array
    {
        return [
            'quiz_id' => ['required', 'integer', 'exists:lms_quizzes,id'],
            'question' => ['required', 'string'],
            'type' => ['required', Rule::enum(QuestionType::class)],
            'points' => ['required', 'integer', 'min:1'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
