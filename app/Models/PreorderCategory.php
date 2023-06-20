<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PreorderCategory extends Model
{
    protected $fillable = [
        'title',
        'parent_id',
        'preorder_id',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(PreorderProduct::class, 'preorder_category_id', 'id');
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(PreorderCategory::class, 'parent_id', 'id');
    }

    public function preorder(): BelongsTo
    {
        return $this->belongsTo(Preorder::class, 'preorder_id', 'id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PreorderCategory::class, 'parent_id', 'id');
    }
}
