<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Animal extends Model
{
    protected $fillable = [
        'name',
        'species',
        'gender',
        'age',
        'description',
        'image',
        'category_id',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(AdoptionApplication::class, 'animal_id');
    }
}
