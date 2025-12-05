<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PokemonStat extends Model
{
    protected $fillable = [
        'pokemon_id',
        'stat_name',
        'base_stat',
        'effort',
    ];

    public function pokemon(): BelongsTo
    {
        return $this->belongsTo(Pokemon::class);
    }
}
