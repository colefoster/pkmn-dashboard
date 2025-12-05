<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pokemon extends Model
{
    use SoftDeletes;

    protected $table = 'pokemon';

    protected $fillable = [
        'api_id',
        'name',
        'height',
        'weight',
        'base_experience',
        'is_default',
        'species_id',
        'sprite_front_default',
        'sprite_front_shiny',
        'sprite_back_default',
        'sprite_back_shiny',
        'cry_latest',
        'cry_legacy',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function species(): BelongsTo
    {
        return $this->belongsTo(PokemonSpecies::class, 'species_id');
    }

    public function stats(): HasMany
    {
        return $this->hasMany(PokemonStat::class);
    }

    public function types(): BelongsToMany
    {
        return $this->belongsToMany(Type::class, 'pokemon_type')
            ->withPivot('slot')
            ->withTimestamps();
    }

    public function abilities(): BelongsToMany
    {
        return $this->belongsToMany(Ability::class, 'ability_pokemon')
            ->withPivot('is_hidden', 'slot')
            ->withTimestamps();
    }

    public function moves(): BelongsToMany
    {
        return $this->belongsToMany(Move::class, 'move_pokemon')
            ->withPivot('learn_method', 'level_learned_at')
            ->withTimestamps();
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'pokemon_item')
            ->withPivot('rarity', 'version')
            ->withTimestamps();
    }

    public function gameIndices(): HasMany
    {
        return $this->hasMany(PokemonGameIndex::class);
    }
}


