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

    // Stat accessors for Filament table columns
    protected function hpStat(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->stats->firstWhere('stat_name', 'hp')?->base_stat
        );
    }

    protected function attackStat(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->stats->firstWhere('stat_name', 'attack')?->base_stat
        );
    }

    protected function defenseStat(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->stats->firstWhere('stat_name', 'defense')?->base_stat
        );
    }

    protected function specialAttackStat(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->stats->firstWhere('stat_name', 'special-attack')?->base_stat
        );
    }

    protected function specialDefenseStat(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->stats->firstWhere('stat_name', 'special-defense')?->base_stat
        );
    }

    protected function speedStat(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->stats->firstWhere('stat_name', 'speed')?->base_stat
        );
    }

    protected function totalBaseStat(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->stats->sum('base_stat')
        );
    }

    /**
     * Get flattened evolution chain data for Filament entries
     * Returns a flat array with stage_1_sprite, stage_1_name, etc.
     */
    public function getEvolutionChainData(): array
    {
        if (!$this->species || !$this->species->evolutionChain) {
            return ['has_evolutions' => false];
        }

        $chain = $this->species->evolutionChain;
        $evolutions = $chain->evolutions()->with(['species.pokemon' => function ($query) {
            $query->where('is_default', true);
        }, 'evolvesToSpecies.pokemon' => function ($query) {
            $query->where('is_default', true);
        }])->get();

        if ($evolutions->isEmpty()) {
            return ['has_evolutions' => false];
        }

        // Build a map of species to their evolutions
        $evolutionMap = [];
        foreach ($evolutions as $evolution) {
            $fromSpeciesId = $evolution->species_id;
            if (!isset($evolutionMap[$fromSpeciesId])) {
                $evolutionMap[$fromSpeciesId] = [];
            }
            $evolutionMap[$fromSpeciesId][] = $evolution;
        }

        // Find the root species (one that doesn't evolve from anything)
        $allToSpeciesIds = $evolutions->pluck('evolves_to_species_id')->filter()->unique()->toArray();
        $allFromSpeciesIds = $evolutions->pluck('species_id')->unique()->toArray();
        $rootSpeciesIds = array_diff($allFromSpeciesIds, $allToSpeciesIds);

        // If no root found, use the first species in the chain
        $rootSpeciesId = !empty($rootSpeciesIds) ? reset($rootSpeciesIds) : $chain->species()->first()?->id;

        if (!$rootSpeciesId) {
            return ['has_evolutions' => false];
        }

        // Build the linear chain (for now, we'll just follow the first evolution path)
        $chainStages = [];
        $this->buildLinearChain($rootSpeciesId, $evolutionMap, $chainStages);

        // Flatten into numbered stages for Filament
        $flatData = ['has_evolutions' => true, 'stage_count' => count($chainStages)];

        foreach ($chainStages as $index => $stage) {
            $stageNum = $index + 1;
            $flatData["stage_{$stageNum}_sprite"] = $stage['sprite'];
            $flatData["stage_{$stageNum}_name"] = ucwords(str_replace('-', ' ', $stage['name']));
            $flatData["stage_{$stageNum}_method"] = $stage['method'] ?? null;
        }

        return $flatData;
    }

    /**
     * Build a linear evolution chain (follows first evolution path)
     */
    private function buildLinearChain(int $speciesId, array $evolutionMap, array &$chainStages): void
    {
        $species = PokemonSpecies::with(['pokemon' => function ($query) {
            $query->where('is_default', true);
        }])->find($speciesId);

        if (!$species) {
            return;
        }

        $defaultPokemon = $species->pokemon->first();

        $stage = [
            'sprite' => $defaultPokemon?->sprite_front_default,
            'name' => $species->name,
            'method' => null
        ];

        // Get evolution method if this species evolves
        if (isset($evolutionMap[$speciesId]) && !empty($evolutionMap[$speciesId])) {
            $evolution = $evolutionMap[$speciesId][0]; // Take first evolution path
            if ($evolution->evolves_to_species_id) {
                $stage['method'] = $this->formatEvolutionMethod($evolution);
                $chainStages[] = $stage;
                // Continue with next stage
                $this->buildLinearChain($evolution->evolves_to_species_id, $evolutionMap, $chainStages);
                return;
            }
        }

        // No more evolutions, add final stage
        $chainStages[] = $stage;
    }

    /**
     * Format the evolution method into a human-readable string
     */
    private function formatEvolutionMethod(Evolution $evolution): string
    {
        $parts = [];

        if ($evolution->trigger) {
            $parts[] = ucfirst(str_replace('-', ' ', $evolution->trigger));
        }

        if ($evolution->min_level) {
            $parts[] = "Level {$evolution->min_level}";
        }

        if ($evolution->item) {
            $parts[] = ucfirst(str_replace('-', ' ', $evolution->item));
        }

        if ($evolution->held_item) {
            $parts[] = "Holding " . ucfirst(str_replace('-', ' ', $evolution->held_item));
        }

        if ($evolution->min_happiness) {
            $parts[] = "Happiness {$evolution->min_happiness}+";
        }

        if ($evolution->min_affection) {
            $parts[] = "Affection {$evolution->min_affection}+";
        }

        if ($evolution->location) {
            $parts[] = "At " . ucfirst(str_replace('-', ' ', $evolution->location));
        }

        if ($evolution->time_of_day) {
            $parts[] = ucfirst($evolution->time_of_day);
        }

        if ($evolution->known_move) {
            $parts[] = "Knowing " . ucfirst(str_replace('-', ' ', $evolution->known_move));
        }

        if ($evolution->needs_overworld_rain) {
            $parts[] = "During rain";
        }

        if ($evolution->turn_upside_down) {
            $parts[] = "Turn console upside down";
        }

        return !empty($parts) ? implode(', ', $parts) : 'Unknown method';
    }
}


