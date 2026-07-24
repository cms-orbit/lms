<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Entities;

use CmsOrbit\Core\Foundation\Entity\Entity;
use CmsOrbit\Core\Screen\Fields\CheckBox;
use CmsOrbit\Core\Screen\Fields\DateTimer;
use CmsOrbit\Core\Screen\Fields\Input;
use CmsOrbit\Core\Screen\Fields\Select;
use CmsOrbit\Core\Screen\TD;
use CmsOrbit\Lms\Concerns\HasLmsPermissions;
use CmsOrbit\Lms\Enums\CouponType;
use CmsOrbit\Lms\Models\Coupon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class CouponEntity extends Entity
{
    use HasLmsPermissions;

    public static function uriKey(): string
    {
        return 'lms-coupons';
    }

    public function model(): string
    {
        return Coupon::class;
    }

    public function icon(): string
    {
        return 'bs.ticket-perforated';
    }

    public function sort(): int
    {
        return 5410;
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
        return __('Coupons');
    }

    public function singularLabel(): string
    {
        return __('Coupon');
    }

    public function fields(): array
    {
        return [
            Input::make('code')->title(__('Code'))->required(),
            Select::make('type')->title(__('Type'))
                ->options(CouponType::options())
                ->value(CouponType::Percent->value),
            Input::make('amount')->title(__('Amount'))->type('number')
                ->help(__('Percentage (0-100) or fixed amount depending on type.')),
            Input::make('min_order_amount')->title(__('Minimum order amount'))->type('number'),
            Input::make('max_uses')->title(__('Max uses'))->type('number')
                ->help(__('Leave blank for unlimited.')),
            DateTimer::make('starts_at')->title(__('Starts at'))->enableTime()->format('Y-m-d H:i:S'),
            DateTimer::make('expires_at')->title(__('Expires at'))->enableTime()->format('Y-m-d H:i:S'),
            CheckBox::make('active')->title(__('Active'))->sendTrueOrFalse()->value(true),
        ];
    }

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('code', __('Code')),
            TD::make('type', __('Type'))
                ->render(fn (Coupon $coupon) => $coupon->type?->label() ?? '—'),
            TD::make('amount', __('Amount'))->sort(),
            TD::make('used', __('Used'))->sort(),
            TD::make('active', __('Active')),
        ];
    }

    public function rules(Model $model): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('lms_coupons', 'code')->ignore($model->getKey()),
            ],
            'type' => ['required', Rule::enum(CouponType::class)],
            'amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
