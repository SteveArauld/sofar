<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasTranslations;

    public $incrementing = false;

    protected $keyType = 'int';

    protected $guarded = [];

    /** @var list<string> */
    public array $translatable = [
        'name',
    ];

    protected $casts = [
        'level' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        $col = 'name->'.app()->getLocale();

        return $this->hasMany(Category::class, 'parent_id')->orderBy($col);
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
