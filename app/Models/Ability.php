<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ability extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'api_id',
        'name',
        'effect',
        'short_effect',
        'is_main_series',
    ];

    protected $casts = [
        'is_main_series' => 'boolean',
    ];

    public function pokemon(): BelongsToMany
    {
        return $this->belongsToMany(Pokemon::class, 'ability_pokemon')
            ->withPivot('is_hidden', 'slot')
            ->withTimestamps();
    }
}
