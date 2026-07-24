<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Entities;

use CmsOrbit\Core\Foundation\Entity\Entity;
use CmsOrbit\Core\Screen\Fields\Select;
use CmsOrbit\Core\Screen\TD;
use CmsOrbit\Lms\Concerns\HasLmsPermissions;
use CmsOrbit\Lms\Enums\OrderStatus;
use CmsOrbit\Lms\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class OrderEntity extends Entity
{
    use HasLmsPermissions;

    public static function uriKey(): string
    {
        return 'lms-orders';
    }

    public function model(): string
    {
        return Order::class;
    }

    public function icon(): string
    {
        return 'bs.receipt';
    }

    public function sort(): int
    {
        return 5400;
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
        return __('Orders');
    }

    public function singularLabel(): string
    {
        return __('Order');
    }

    public function query(): Builder
    {
        return parent::query()->with('student')->latest();
    }

    public function fields(): array
    {
        return [
            Select::make('status')->title(__('Status'))
                ->options(OrderStatus::options()),
        ];
    }

    public function columns(): array
    {
        return [
            TD::make('reference', __('Reference')),
            TD::make('student.name', __('Student')),
            TD::make('status', __('Status'))
                ->render(fn (Order $order) => $order->status?->label() ?? '—')
                ->filter(TD::FILTER_SELECT, OrderStatus::options()),
            TD::make('total', __('Total'))->sort(),
            TD::make('paid_at', __('Paid'))->sort(),
            TD::make('created_at', __('Created'))->sort(),
        ];
    }

    public function rules(Model $model): array
    {
        return [
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ];
    }
}
