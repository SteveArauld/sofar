<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    public $incrementing = false;
    protected $keyType = 'int';
    protected $guarded = [];

    protected $casts = [
        'dimensions'        => 'array',
        'variations'        => 'array',
        'flags'             => 'array',
        'breadcrumb'        => 'array',
        'category_path'     => 'array',
        'source_payload'    => 'array',
        'price'             => 'decimal:2',
        'price_before'      => 'decimal:2',
        'in_stock'          => 'boolean',
        'rating'            => 'float',
        'source_created_at' => 'datetime',
        'source_updated_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product')->withPivot('depth');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function specs(): HasMany
    {
        return $this->hasMany(ProductSpec::class)->orderBy('position');
    }

    public function extraServices(): HasMany
    {
        return $this->hasMany(ProductExtraService::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getMainImageAttribute(): ?string
    {
        return $this->images->first()?->path;
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->price_before !== null && (float) $this->price_before > (float) $this->price;
    }
}
