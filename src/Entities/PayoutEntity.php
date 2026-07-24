<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Entities;

use CmsOrbit\Core\Foundation\Entity\Entity;
use CmsOrbit\Core\Screen\Fields\DateTimer;
use CmsOrbit\Core\Screen\Fields\Input;
use CmsOrbit\Core\Screen\Fields\Select;
use CmsOrbit\Core\Screen\Fields\TextArea;
use CmsOrbit\Core\Screen\TD;
use CmsOrbit\Lms\Concerns\HasLmsPermissions;
use CmsOrbit\Lms\Enums\PayoutStatus;
use CmsOrbit\Lms\Models\Payout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class PayoutEntity extends Entity
{
    use HasLmsPermissions;

    public static function uriKey(): string
    {
        return 'lms-payouts';
    }

    public function model(): string
    {
        return Payout::class;
    }

    public function icon(): string
    {
        return 'bs.bank';
    }

    public function sort(): int
    {
        return 5430;
    }

    public function section(): string
    {
        return __('Marketplace');
    }

    public function sectionKey(): string
    {
        return 'lms-marketplace';
    }

    public function label(): string
    {
        return __('Payouts');
    }

    public function singularLabel(): string
    {
        return __('Payout');
    }

    public function query(): Builder
    {
        return parent::query()->with('instructor')->latest();
    }

    public function fields(): array
    {
        return [
            Select::make('instructor_id')->title(__('Instructor'))
                ->fromModel((string) config('lms.user_model'), 'name', 'id')
                ->required(),
            Input::make('amount')->title(__('Amount'))->type('number')->required(),
            Select::make('status')->title(__('Status'))
                ->options(PayoutStatus::options())
                ->value(PayoutStatus::Pending->value),
            Input::make('method')->title(__('Method')),
            TextArea::make('note')->title(__('Note'))->rows(2),
            DateTimer::make('processed_at')->title(__('Processed at'))->enableTime()->format('Y-m-d H:i:S'),
        ];
    }

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('instructor.name', __('Instructor')),
            TD::make('amount', __('Amount'))->sort(),
            TD::make('status', __('Status'))
                ->render(fn (Payout $payout) => $payout->status?->label() ?? '—')
                ->filter(TD::FILTER_SELECT, PayoutStatus::options()),
            TD::make('processed_at', __('Processed'))->sort(),
        ];
    }

    public function rules(Model $model): array
    {
        return [
            'instructor_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(PayoutStatus::class)],
        ];
    }
}
