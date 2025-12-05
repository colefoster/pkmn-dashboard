<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PokemonGameIndex extends Model
{
    protected $table = 'pokemon_game_indices';

    protected $fillable = [
        'pokemon_id',
        'game_index',
        'version',
    ];

    public function pokemon(): BelongsTo
    {
        return $this->belongsTo(Pokemon::class);
    }
}
