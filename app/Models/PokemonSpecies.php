<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PokemonSpecies extends Model
{
    protected $table = 'pokemon_species';

    protected $fillable = [
        'api_id',
        'name',
        'base_happiness',
        'capture_rate',
        'color',
        'gender_rate',
        'hatch_counter',
        'is_baby',
        'is_legendary',
        'is_mythical',
        'habitat',
        'shape',
        'generation',
        'evolution_chain_id',
    ];

    protected $casts = [
        'is_baby' => 'boolean',
        'is_legendary' => 'boolean',
        'is_mythical' => 'boolean',
    ];

    public function pokemon(): HasMany
    {
        return $this->hasMany(Pokemon::class, 'species_id');
    }

    public function evolutionChain(): BelongsTo
    {
        return $this->belongsTo(EvolutionChain::class);
    }

    public function evolvesFrom(): HasMany
    {
        return $this->hasMany(Evolution::class, 'evolves_to_species_id');
    }

    public function evolvesTo(): HasMany
    {
        return $this->hasMany(Evolution::class, 'species_id');
    }
}


