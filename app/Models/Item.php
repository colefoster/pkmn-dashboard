<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'api_id',
        'name',
        'cost',
        'fling_power',
        'fling_effect',
        'category',
        'effect',
        'short_effect',
        'flavor_text',
        'sprite',
    ];

    public function pokemon(): BelongsToMany
    {
        return $this->belongsToMany(Pokemon::class, 'pokemon_item')
            ->withPivot('rarity', 'version')
            ->withTimestamps();
    }
}
