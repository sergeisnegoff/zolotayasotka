<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Preorder extends Model
{
    protected $table = 'preorders';

    public static function boot() {

        parent::boot();

        static::creating(function($item) {
            $item->code = \Str::slug($item->title);
        });
    }

    public function sheets(): HasMany
    {
        return $this->hasMany(PreorderTableSheet::class, 'preorder_id', 'id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(PreorderCategory::class, 'preorder_id', 'id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(PreorderProduct::class);
    }
}
