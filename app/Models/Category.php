<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    public $incrementing = false;
    protected $keyType = 'int';
    protected $guarded = [];

    protected $casts = [
        'level' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('name');
    }

    /** Produits dont c'est la catégorie feuille. */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /** Tous les produits rattachés (via le fil d'Ariane complet). */
    public function allProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_product')->withPivot('depth');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
