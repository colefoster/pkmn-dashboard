<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Type extends Model
{
    protected $fillable = [
        'api_id',
        'name',
    ];

    public function pokemon(): BelongsToMany
    {
        return $this->belongsToMany(Pokemon::class, 'pokemon_type')
            ->withPivot('slot')
            ->withTimestamps();
    }

    public function moves(): HasMany
    {
        return $this->hasMany(Move::class);
    }
}

