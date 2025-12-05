<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Move extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'api_id',
        'name',
        'power',
        'pp',
        'accuracy',
        'priority',
        'type_id',
        'damage_class',
        'effect_chance',
        'contest_type',
        'generation',
        'effect',
        'short_effect',
        'flavor_text',
        'target',
        'ailment',
        'meta_category',
        'min_hits',
        'max_hits',
        'min_turns',
        'max_turns',
        'drain',
        'healing',
        'crit_rate',
        'ailment_chance',
        'flinch_chance',
        'stat_chance',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function pokemon(): BelongsToMany
    {
        return $this->belongsToMany(Pokemon::class, 'move_pokemon')
            ->withPivot('learn_method', 'level_learned_at')
            ->withTimestamps();
    }
}
