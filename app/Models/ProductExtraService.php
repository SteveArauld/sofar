<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class ProductExtraService extends Model
{
    use HasTranslations;

    protected $guarded = [];

    public $timestamps = false;

    /** @var list<string> */
    public array $translatable = [
        'name',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
