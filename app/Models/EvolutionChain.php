<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvolutionChain extends Model
{
    protected $fillable = [
        'api_id',
        'baby_trigger_item',
    ];

    public function species(): HasMany
    {
        return $this->hasMany(PokemonSpecies::class, 'evolution_chain_id');
    }

    public function evolutions(): HasMany
    {
        return $this->hasMany(Evolution::class);
    }
}
