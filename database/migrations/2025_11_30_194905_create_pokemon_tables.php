<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Types table
        Schema::create('types', function (Blueprint $table) {
            $table->id();
            $table->integer('api_id')->unique();
            $table->string('name');
            $table->timestamps();

            $table->index('api_id');
        });

        // Abilities table
        Schema::create('abilities', function (Blueprint $table) {
            $table->id();
            $table->integer('api_id')->unique();
            $table->string('name');
            $table->text('effect')->nullable();
            $table->text('short_effect')->nullable();
            $table->boolean('is_main_series')->default(true);
            $table->timestamps();
$table->softDeletes();

            $table->index('api_id');
        });

        // Moves table
        Schema::create('moves', function (Blueprint $table) {
            $table->id();
            $table->integer('api_id')->unique();
            $table->string('name');
            $table->integer('power')->nullable();
            $table->integer('pp')->nullable();
            $table->integer('accuracy')->nullable();
            $table->integer('priority')->nullable();
            $table->foreignId('type_id')->nullable()->constrained('types')->nullOnDelete();
            $table->string('damage_class')->nullable();
            $table->integer('effect_chance')->nullable();
            $table->string('contest_type')->nullable();
            $table->string('generation')->nullable();
            $table->text('effect')->nullable();
            $table->text('short_effect')->nullable();
            $table->text('flavor_text')->nullable();
            $table->string('target')->nullable();
            $table->string('ailment')->nullable();
            $table->string('meta_category')->nullable();
            $table->integer('min_hits')->nullable();
            $table->integer('max_hits')->nullable();
            $table->integer('min_turns')->nullable();
            $table->integer('max_turns')->nullable();
            $table->integer('drain')->nullable();
            $table->integer('healing')->nullable();
            $table->integer('crit_rate')->nullable();
            $table->integer('ailment_chance')->nullable();
            $table->integer('flinch_chance')->nullable();
            $table->integer('stat_chance')->nullable();
            $table->timestamps();
$table->softDeletes();

            $table->index('api_id');
            $table->index('damage_class');
            $table->index('generation');
        });

        // Evolution Chains table
        Schema::create('evolution_chains', function (Blueprint $table) {
            $table->id();
            $table->integer('api_id')->unique();
            $table->string('baby_trigger_item')->nullable();
            $table->timestamps();

            $table->index('api_id');
        });

        // Pokemon Species table
        Schema::create('pokemon_species', function (Blueprint $table) {
            $table->id();
            $table->integer('api_id')->unique();
            $table->string('name');
            $table->integer('base_happiness')->nullable();
            $table->integer('capture_rate')->nullable();
            $table->string('color')->nullable();
            $table->integer('gender_rate')->nullable();
            $table->integer('hatch_counter')->nullable();
            $table->boolean('is_baby')->default(false);
            $table->boolean('is_legendary')->default(false);
            $table->boolean('is_mythical')->default(false);
            $table->string('habitat')->nullable();
            $table->string('shape')->nullable();
            $table->string('generation')->nullable();
            $table->foreignId('evolution_chain_id')->nullable()->constrained('evolution_chains')->nullOnDelete();
            $table->timestamps();

            $table->index('api_id');
            $table->index('is_legendary');
            $table->index('is_mythical');
        });

        // Pokemon table
        Schema::create('pokemon', function (Blueprint $table) {
            $table->id();
            $table->integer('api_id')->unique();
            $table->string('name');
            $table->integer('height')->nullable();
            $table->integer('weight')->nullable();
            $table->integer('base_experience')->nullable();
            $table->boolean('is_default')->default(true);
            $table->foreignId('species_id')->nullable()->constrained('pokemon_species')->nullOnDelete();

            // Sprite URLs
            $table->string('sprite_front_default')->nullable();
            $table->string('sprite_front_shiny')->nullable();
            $table->string('sprite_back_default')->nullable();
            $table->string('sprite_back_shiny')->nullable();

            // Cries URLs
            $table->string('cry_latest')->nullable();
            $table->string('cry_legacy')->nullable();

            $table->timestamps();
$table->softDeletes();

            $table->index('api_id');
            $table->index('name');
        });

        // Pokemon Stats table
        Schema::create('pokemon_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pokemon_id')->constrained('pokemon')->cascadeOnDelete();
            $table->string('stat_name');
            $table->integer('base_stat');
            $table->integer('effort');
            $table->timestamps();

            // Covering index for efficient stat sorting in Filament tables
            $table->index(['pokemon_id', 'stat_name', 'base_stat']);
        });

        // Pokemon Types pivot table
        Schema::create('pokemon_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pokemon_id')->constrained('pokemon')->cascadeOnDelete();
            $table->foreignId('type_id')->constrained('types')->cascadeOnDelete();
            $table->integer('slot')->default(1);
            $table->timestamps();

            $table->unique(['pokemon_id', 'type_id']);
        });

        // Pokemon Abilities pivot table
        Schema::create('ability_pokemon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pokemon_id')->constrained('pokemon')->cascadeOnDelete();
            $table->foreignId('ability_id')->constrained('abilities')->cascadeOnDelete();
            $table->boolean('is_hidden')->default(false);
            $table->integer('slot')->default(1);
            $table->timestamps();

            $table->unique(['pokemon_id', 'ability_id']);
            $table->index('ability_id'); // Optimize queries for finding all pokemon with a specific ability
        });

        // Pokemon Moves pivot table
        Schema::create('move_pokemon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pokemon_id')->constrained('pokemon')->cascadeOnDelete();
            $table->foreignId('move_id')->constrained('moves')->cascadeOnDelete();
            $table->string('learn_method')->nullable();
            $table->integer('level_learned_at')->nullable();
            $table->timestamps();

            $table->index(['pokemon_id', 'move_id']);
        });

        // Items table
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->integer('api_id')->unique();
            $table->string('name');
            $table->integer('cost')->nullable();
            $table->integer('fling_power')->nullable();
            $table->string('fling_effect')->nullable();
            $table->string('category')->nullable();
            $table->text('effect')->nullable();
            $table->text('short_effect')->nullable();
            $table->text('flavor_text')->nullable();
            $table->string('sprite')->nullable();
            $table->timestamps();
$table->softDeletes();

            $table->index('api_id');
            $table->index('category');
        });

        // Pokemon Items pivot table (held items)
        Schema::create('pokemon_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pokemon_id')->constrained('pokemon')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->integer('rarity')->nullable();
            $table->string('version')->nullable();
            $table->timestamps();

            $table->index(['pokemon_id', 'item_id']);
        });

        // Pokemon Game Indices table
        Schema::create('pokemon_game_indices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pokemon_id')->constrained('pokemon')->cascadeOnDelete();
            $table->integer('game_index');
            $table->string('version');
            $table->timestamps();

            $table->index(['pokemon_id', 'version']);
        });

        // Evolutions table (stores evolution requirements between species)
        Schema::create('evolutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evolution_chain_id')->constrained('evolution_chains')->cascadeOnDelete();
            $table->foreignId('species_id')->constrained('pokemon_species')->cascadeOnDelete();
            $table->foreignId('evolves_to_species_id')->nullable()->constrained('pokemon_species')->cascadeOnDelete();
            $table->string('trigger')->nullable();
            $table->integer('min_level')->nullable();
            $table->string('item')->nullable();
            $table->string('held_item')->nullable();
            $table->string('gender')->nullable();
            $table->integer('min_happiness')->nullable();
            $table->integer('min_beauty')->nullable();
            $table->integer('min_affection')->nullable();
            $table->string('location')->nullable();
            $table->string('time_of_day')->nullable();
            $table->string('known_move')->nullable();
            $table->string('known_move_type')->nullable();
            $table->string('party_species')->nullable();
            $table->string('party_type')->nullable();
            $table->integer('relative_physical_stats')->nullable();
            $table->boolean('needs_overworld_rain')->default(false);
            $table->string('trade_species')->nullable();
            $table->boolean('turn_upside_down')->default(false);
            $table->timestamps();
$table->softDeletes();

            $table->index(['species_id', 'evolves_to_species_id']);
            $table->index('evolution_chain_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evolutions');
        Schema::dropIfExists('pokemon_game_indices');
        Schema::dropIfExists('pokemon_item');
        Schema::dropIfExists('items');
        Schema::dropIfExists('move_pokemon');
        Schema::dropIfExists('ability_pokemon');
        Schema::dropIfExists('pokemon_type');
        Schema::dropIfExists('pokemon_stats');
        Schema::dropIfExists('pokemon');
        Schema::dropIfExists('pokemon_species');
        Schema::dropIfExists('evolution_chains');
        Schema::dropIfExists('moves');
        Schema::dropIfExists('abilities');
        Schema::dropIfExists('types');
    }
};
