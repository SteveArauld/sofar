<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
        'items_snapshot' => 'array',
        'shipping_cost'  => 'decimal:2',
        'subtotal'       => 'decimal:2',
        'total'          => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingLabel(): string
    {
        return \App\Http\Controllers\CheckoutController::SHIPPING[$this->shipping_method]['label'] ?? $this->shipping_method;
    }

    public function paymentLabel(): string
    {
        return \App\Http\Controllers\CheckoutController::PAYMENT[$this->payment_method]['label'] ?? $this->payment_method;
    }

    public function money($value): string
    {
        return number_format((float) $value, 2, ',', ' ').'€';
    }
}
