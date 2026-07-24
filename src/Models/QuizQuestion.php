<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Models;

use CmsOrbit\Lms\Database\Factories\QuizQuestionFactory;
use CmsOrbit\Lms\Enums\QuestionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizQuestion extends Model
{
    /** @use HasFactory<QuizQuestionFactory> */
    use HasFactory;

    protected $table = 'lms_quiz_questions';

    protected $fillable = [
        'quiz_id',
        'question',
        'type',
        'options',
        'correct',
        'points',
        'order',
        'explanation',
    ];

    protected function casts(): array
    {
        return [
            'type' => QuestionType::class,
            'options' => 'array',
            'correct' => 'array',
            'points' => 'integer',
            'order' => 'integer',
        ];
    }

    protected static function newFactory(): QuizQuestionFactory
    {
        return QuizQuestionFactory::new();
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Check a submitted answer (array of selected option indexes) against the
     * stored correct set. Order-insensitive exact match.
     *
     * @param  array<int, int|string>  $answer
     */
    public function isAnswerCorrect(array $answer): bool
    {
        $correct = array_map('strval', (array) $this->correct);
        $given = array_map('strval', $answer);

        sort($correct);
        sort($given);

        return $correct === $given;
    }
}
