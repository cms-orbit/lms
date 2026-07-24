<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Entities;

use CmsOrbit\Core\Foundation\Entity\Entity;
use CmsOrbit\Core\Screen\Fields\Select;
use CmsOrbit\Core\Screen\TD;
use CmsOrbit\Lms\Concerns\HasLmsPermissions;
use CmsOrbit\Lms\Enums\EarningStatus;
use CmsOrbit\Lms\Models\Earning;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class EarningEntity extends Entity
{
    use HasLmsPermissions;

    public static function uriKey(): string
    {
        return 'lms-earnings';
    }

    public function model(): string
    {
        return Earning::class;
    }

    public function icon(): string
    {
        return 'bs.cash-stack';
    }

    public function sort(): int
    {
        return 5420;
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
        return __('Earnings');
    }

    public function singularLabel(): string
    {
        return __('Earning');
    }

    public function query(): Builder
    {
        return parent::query()->with(['instructor', 'course'])->latest();
    }

    public function fields(): array
    {
        return [
            Select::make('status')->title(__('Status'))
                ->options(EarningStatus::options()),
        ];
    }

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('instructor.name', __('Instructor')),
            TD::make('course.title', __('Course')),
            TD::make('amount', __('Amount'))->sort(),
            TD::make('status', __('Status'))
                ->render(fn (Earning $earning) => $earning->status?->label() ?? '—')
                ->filter(TD::FILTER_SELECT, EarningStatus::options()),
            TD::make('available_at', __('Available'))->sort(),
        ];
    }

    public function rules(Model $model): array
    {
        return [
            'status' => ['required', Rule::enum(EarningStatus::class)],
        ];
    }
}
