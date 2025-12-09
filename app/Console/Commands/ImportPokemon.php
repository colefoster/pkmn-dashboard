<?php

namespace App\Console\Commands;

use App\Models\Ability;
use App\Models\Evolution;
use App\Models\EvolutionChain;
use App\Models\Item;
use App\Models\Move;
use App\Models\Pokemon;
use App\Models\PokemonGameIndex;
use App\Models\PokemonSpecies;
use App\Models\PokemonStat;
use App\Models\Type;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Import Pokemon data from PokeAPI
 *
 * Usage:
 *   pz --max=50
 *   php artisan pokemon:import --delay=200 --max=151
 */
class ImportPokemon extends Command
{
    protected $signature = 'pokemon:import
                            {--limit=50 : Number of records to fetch per page}
                            {--delay=100 : Delay between requests in milliseconds}
                            {--max= : Maximum number of pokemon to import (optional)}';

    protected $description = 'Import Pokemon data from PokeAPI';

    private string $baseUrl = 'https://pokeapi.co/api/v2';
    private int $delay;
    private int $successCount = 0;
    private int $errorCount = 0;

    public function handle(): int
    {
        // Disable execution time limit for long-running import
        set_time_limit(0);

        $this->delay = (int) $this->option('delay');
        $limit = (int) $this->option('limit');
        $maxPokemon = $this->option('max') ? (int) $this->option('max') : null;

        $this->info('🚀 Starting Pokemon import from PokeAPI...');
        $this->info("Delay between requests: {$this->delay}ms");
        $this->newLine();

        try {
            $this->info('📋 Importing Types...');
            $this->importTypes();
            $this->newLine();

            $this->info('⚡ Importing Abilities...');
            $this->importAbilities();
            $this->newLine();

            $this->info('🥊 Importing Moves...');
            $this->importMoves();
            $this->newLine();

            $this->info('🎒 Importing Items...');
            $this->importItems();
            $this->newLine();

            $this->info('🧬 Importing Pokemon Species...');
            $this->importPokemonSpecies($maxPokemon);
            $this->newLine();

            $this->info('🔗 Importing Evolution Chains...');
            $this->importEvolutionChains();
            $this->newLine();

            $this->info('🎮 Importing Pokemon...');
            $this->importPokemon($limit, $maxPokemon);
            $this->newLine();

            $this->info('✅ Import completed successfully!');
            $this->info("Success: {$this->successCount} | Errors: {$this->errorCount}");

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Import failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    // SEE PART 2 FOR IMPORT METHODS
    // CONTINUATION OF ImportPokemon.php

    private function importTypes(): void
    {
        $response = $this->fetchFromApi('/type');
        $types = $response['results'] ?? [];

        $bar = $this->output->createProgressBar(count($types));
        $bar->start();

        foreach ($types as $typeData) {
            try {
                $typeId = $this->extractIdFromUrl($typeData['url']);
                $typeDetails = $this->fetchFromApi("/type/{$typeId}");

                Type::updateOrCreate(
                    ['api_id' => $typeDetails['id']],
                    ['name' => $typeDetails['name']]
                );

                $this->successCount++;
                $bar->advance();
                usleep($this->delay * 1000);
            } catch (\Exception $e) {
                $this->errorCount++;
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Types imported: ' . Type::count());
    }

    private function importAbilities(): void
    {
        $offset = 0;
        $limit = 100;

        do {
            $response = $this->fetchFromApi("/ability?limit={$limit}&offset={$offset}");
            $abilities = $response['results'] ?? [];

            if (empty($abilities)) break;

            $bar = $this->output->createProgressBar(count($abilities));
            $bar->start();

            foreach ($abilities as $abilityData) {
                try {
                    $abilityId = $this->extractIdFromUrl($abilityData['url']);
                    $abilityDetails = $this->fetchFromApi("/ability/{$abilityId}");

                    $effectEntry = collect($abilityDetails['effect_entries'] ?? [])
                        ->firstWhere('language.name', 'en');

                    Ability::updateOrCreate(
                        ['api_id' => $abilityDetails['id']],
                        [
                            'name' => $abilityDetails['name'],
                            'effect' => $effectEntry['effect'] ?? null,
                            'short_effect' => $effectEntry['short_effect'] ?? null,
                            'is_main_series' => $abilityDetails['is_main_series'] ?? true,
                        ]
                    );

                    $this->successCount++;
                    $bar->advance();
                    usleep($this->delay * 1000);
                } catch (\Exception $e) {
                    $this->errorCount++;
                }
            }

            $bar->finish();
            $this->newLine();
            $offset += $limit;

        } while (!empty($abilities));

        $this->info('Abilities imported: ' . Ability::count());
    }

    private function importMoves(): void
    {
        $offset = 0;
        $limit = 100;

        do {
            $response = $this->fetchFromApi("/move?limit={$limit}&offset={$offset}");
            $moves = $response['results'] ?? [];

            if (empty($moves)) break;

            $bar = $this->output->createProgressBar(count($moves));
            $bar->start();

            foreach ($moves as $moveData) {
                try {
                    $moveId = $this->extractIdFromUrl($moveData['url']);
                    $moveDetails = $this->fetchFromApi("/move/{$moveId}");

                    $typeId = null;
                    if (isset($moveDetails['type']['name'])) {
                        $type = Type::where('name', $moveDetails['type']['name'])->first();
                        $typeId = $type?->id;
                    }

                    // Parse English effect entries
                    $effectEntry = collect($moveDetails['effect_entries'] ?? [])
                        ->firstWhere('language.name', 'en');

                    // Parse English flavor text (get the first one available)
                    $flavorTextEntry = collect($moveDetails['flavor_text_entries'] ?? [])
                        ->firstWhere('language.name', 'en');

                    // Parse meta data
                    $meta = $moveDetails['meta'] ?? [];

                    Move::updateOrCreate(
                        ['api_id' => $moveDetails['id']],
                        [
                            'name' => $moveDetails['name'],
                            'power' => $moveDetails['power'],
                            'pp' => $moveDetails['pp'],
                            'accuracy' => $moveDetails['accuracy'],
                            'priority' => $moveDetails['priority'],
                            'type_id' => $typeId,
                            'damage_class' => $moveDetails['damage_class']['name'] ?? null,
                            'effect_chance' => $moveDetails['effect_chance'] ?? null,
                            'contest_type' => $moveDetails['contest_type']['name'] ?? null,
                            'generation' => $moveDetails['generation']['name'] ?? null,
                            'effect' => $effectEntry['effect'] ?? null,
                            'short_effect' => $effectEntry['short_effect'] ?? null,
                            'flavor_text' => $flavorTextEntry['flavor_text'] ?? null,
                            'target' => $moveDetails['target']['name'] ?? null,
                            'ailment' => $meta['ailment']['name'] ?? null,
                            'meta_category' => $meta['category']['name'] ?? null,
                            'min_hits' => $meta['min_hits'] ?? null,
                            'max_hits' => $meta['max_hits'] ?? null,
                            'min_turns' => $meta['min_turns'] ?? null,
                            'max_turns' => $meta['max_turns'] ?? null,
                            'drain' => $meta['drain'] ?? null,
                            'healing' => $meta['healing'] ?? null,
                            'crit_rate' => $meta['crit_rate'] ?? null,
                            'ailment_chance' => $meta['ailment_chance'] ?? null,
                            'flinch_chance' => $meta['flinch_chance'] ?? null,
                            'stat_chance' => $meta['stat_chance'] ?? null,
                        ]
                    );

                    $this->successCount++;
                    $bar->advance();
                    usleep($this->delay * 1000);
                } catch (\Exception $e) {
                    $this->errorCount++;
                }
            }

            $bar->finish();
            $this->newLine();
            $offset += $limit;

        } while (!empty($moves));

        $this->info('Moves imported: ' . Move::count());
    }

    private function importItems(): void
    {
        $offset = 0;
        $limit = 100;

        do {
            $response = $this->fetchFromApi("/item?limit={$limit}&offset={$offset}");
            $items = $response['results'] ?? [];

            if (empty($items)) break;

            $bar = $this->output->createProgressBar(count($items));
            $bar->start();

            foreach ($items as $itemData) {
                try {
                    $itemId = $this->extractIdFromUrl($itemData['url']);
                    $itemDetails = $this->fetchFromApi("/item/{$itemId}");

                    // Parse English effect entries
                    $effectEntry = collect($itemDetails['effect_entries'] ?? [])
                        ->firstWhere('language.name', 'en');

                    // Parse English flavor text (get the first one available)
                    $flavorTextEntry = collect($itemDetails['flavor_text_entries'] ?? [])
                        ->firstWhere('language.name', 'en');

                    Item::updateOrCreate(
                        ['api_id' => $itemDetails['id']],
                        [
                            'name' => $itemDetails['name'],
                            'cost' => $itemDetails['cost'] ?? null,
                            'fling_power' => $itemDetails['fling_power'] ?? null,
                            'fling_effect' => $itemDetails['fling_effect']['name'] ?? null,
                            'category' => $itemDetails['category']['name'] ?? null,
                            'effect' => $effectEntry['effect'] ?? null,
                            'short_effect' => $effectEntry['short_effect'] ?? null,
                            'flavor_text' => $flavorTextEntry['text'] ?? null,
                            'sprite' => $itemDetails['sprites']['default'] ?? null,
                        ]
                    );

                    $this->successCount++;
                    $bar->advance();
                    usleep($this->delay * 1000);
                } catch (\Exception $e) {
                    $this->errorCount++;
                }
            }

            $bar->finish();
            $this->newLine();
            $offset += $limit;

        } while (!empty($items));

        $this->info('Items imported: ' . Item::count());
    }

    private function importPokemonSpecies(?int $maxPokemon): void
    {
        $offset = 0;
        $limit = 100;
        $totalImported = 0;

        do {
            $response = $this->fetchFromApi("/pokemon-species?limit={$limit}&offset={$offset}");
            $speciesList = $response['results'] ?? [];

            if (empty($speciesList) || ($maxPokemon && $totalImported >= $maxPokemon)) {
                break;
            }

            $bar = $this->output->createProgressBar(count($speciesList));
            $bar->start();

            foreach ($speciesList as $speciesData) {
                if ($maxPokemon && $totalImported >= $maxPokemon) break;

                try {
                    $speciesId = $this->extractIdFromUrl($speciesData['url']);
                    $speciesDetails = $this->fetchFromApi("/pokemon-species/{$speciesId}");

                    PokemonSpecies::updateOrCreate(
                        ['api_id' => $speciesDetails['id']],
                        [
                            'name' => $speciesDetails['name'],
                            'base_happiness' => $speciesDetails['base_happiness'],
                            'capture_rate' => $speciesDetails['capture_rate'],
                            'color' => $speciesDetails['color']['name'] ?? null,
                            'gender_rate' => $speciesDetails['gender_rate'],
                            'hatch_counter' => $speciesDetails['hatch_counter'],
                            'is_baby' => $speciesDetails['is_baby'] ?? false,
                            'is_legendary' => $speciesDetails['is_legendary'] ?? false,
                            'is_mythical' => $speciesDetails['is_mythical'] ?? false,
                            'habitat' => $speciesDetails['habitat']['name'] ?? null,
                            'shape' => $speciesDetails['shape']['name'] ?? null,
                            'generation' => $speciesDetails['generation']['name'] ?? null,
                        ]
                    );

                    $this->successCount++;
                    $totalImported++;
                    $bar->advance();
                    usleep($this->delay * 1000);
                } catch (\Exception $e) {
                    $this->errorCount++;
                }
            }

            $bar->finish();
            $this->newLine();
            $offset += $limit;

        } while (!empty($speciesList) && (!$maxPokemon || $totalImported < $maxPokemon));

        $this->info('Pokemon Species imported: ' . PokemonSpecies::count());
    }

    private function importEvolutionChains(): void
    {
        $offset = 0;
        $limit = 100;

        do {
            $response = $this->fetchFromApi("/evolution-chain?limit={$limit}&offset={$offset}");
            $chains = $response['results'] ?? [];

            if (empty($chains)) break;

            $bar = $this->output->createProgressBar(count($chains));
            $bar->start();

            foreach ($chains as $chainData) {
                try {
                    $chainId = $this->extractIdFromUrl($chainData['url']);
                    $chainDetails = $this->fetchFromApi("/evolution-chain/{$chainId}");

                    // Create/Update Evolution Chain
                    $evolutionChain = EvolutionChain::updateOrCreate(
                        ['api_id' => $chainDetails['id']],
                        ['baby_trigger_item' => $chainDetails['baby_trigger_item']['name'] ?? null]
                    );

                    // Parse the chain recursively to extract all evolutions
                    $this->parseEvolutionChain($evolutionChain, $chainDetails['chain']);

                    $this->successCount++;
                    $bar->advance();
                    usleep($this->delay * 1000);
                } catch (\Exception $e) {
                    $this->errorCount++;
                    $this->warn("\nError importing evolution chain: " . $e->getMessage());
                }
            }

            $bar->finish();
            $this->newLine();
            $offset += $limit;

        } while (!empty($chains));

        $this->info('Evolution Chains imported: ' . EvolutionChain::count());
    }

    private function parseEvolutionChain(EvolutionChain $evolutionChain, array $chainNode, ?int $fromSpeciesId = null): void
    {
        // Get the current species
        $speciesName = $chainNode['species']['name'];
        $species = PokemonSpecies::where('name', $speciesName)->first();

        if (!$species) {
            return;
        }

        // Update species with evolution chain
        $species->update(['evolution_chain_id' => $evolutionChain->id]);

        // If there's a previous species, create evolution record
        if ($fromSpeciesId && isset($chainNode['evolution_details'][0])) {
            $details = $chainNode['evolution_details'][0];

            Evolution::updateOrCreate(
                [
                    'evolution_chain_id' => $evolutionChain->id,
                    'species_id' => $fromSpeciesId,
                    'evolves_to_species_id' => $species->id,
                ],
                [
                    'trigger' => $details['trigger']['name'] ?? null,
                    'min_level' => $details['min_level'] ?? null,
                    'item' => $details['item']['name'] ?? null,
                    'held_item' => $details['held_item']['name'] ?? null,
                    'gender' => $details['gender'] ?? null,
                    'min_happiness' => $details['min_happiness'] ?? null,
                    'min_beauty' => $details['min_beauty'] ?? null,
                    'min_affection' => $details['min_affection'] ?? null,
                    'location' => $details['location']['name'] ?? null,
                    'time_of_day' => $details['time_of_day'] ?? null,
                    'known_move' => $details['known_move']['name'] ?? null,
                    'known_move_type' => $details['known_move_type']['name'] ?? null,
                    'party_species' => $details['party_species']['name'] ?? null,
                    'party_type' => $details['party_type']['name'] ?? null,
                    'relative_physical_stats' => $details['relative_physical_stats'] ?? null,
                    'needs_overworld_rain' => $details['needs_overworld_rain'] ?? false,
                    'trade_species' => $details['trade_species']['name'] ?? null,
                    'turn_upside_down' => $details['turn_upside_down'] ?? false,
                ]
            );
        }

        // Recursively parse evolutions
        foreach ($chainNode['evolves_to'] ?? [] as $evolution) {
            $this->parseEvolutionChain($evolutionChain, $evolution, $species->id);
        }
    }
    // CONTINUATION OF ImportPokemon.php - FINAL PART

    private function importPokemon(int $limit, ?int $maxPokemon): void
    {
        $offset = 0;
        $totalImported = 0;

        do {
            $response = $this->fetchFromApi("/pokemon?limit={$limit}&offset={$offset}");
            $pokemonList = $response['results'] ?? [];

            if (empty($pokemonList) || ($maxPokemon && $totalImported >= $maxPokemon)) {
                break;
            }

            $bar = $this->output->createProgressBar(count($pokemonList));
            $bar->start();

            foreach ($pokemonList as $pokemonData) {
                if ($maxPokemon && $totalImported >= $maxPokemon) break;

                try {
                    $pokemonId = $this->extractIdFromUrl($pokemonData['url']);
                    $pokemonDetails = $this->fetchFromApi("/pokemon/{$pokemonId}");

                    // Find species
                    $speciesId = null;
                    if (isset($pokemonDetails['species']['name'])) {
                        $species = PokemonSpecies::where('name', $pokemonDetails['species']['name'])->first();
                        $speciesId = $species?->id;
                    }

                    // Create/Update Pokemon
                    $pokemon = Pokemon::updateOrCreate(
                        ['api_id' => $pokemonDetails['id']],
                        [
                            'name' => $pokemonDetails['name'],
                            'height' => $pokemonDetails['height'],
                            'weight' => $pokemonDetails['weight'],
                            'base_experience' => $pokemonDetails['base_experience'],
                            'is_default' => $pokemonDetails['is_default'] ?? true,
                            'species_id' => $speciesId,
                            'sprite_front_default' => $pokemonDetails['sprites']['front_default'] ?? null,
                            'sprite_front_shiny' => $pokemonDetails['sprites']['front_shiny'] ?? null,
                            'sprite_back_default' => $pokemonDetails['sprites']['back_default'] ?? null,
                            'sprite_back_shiny' => $pokemonDetails['sprites']['back_shiny'] ?? null,
                            'cry_latest' => $pokemonDetails['cries']['latest'] ?? null,
                            'cry_legacy' => $pokemonDetails['cries']['legacy'] ?? null,
                        ]
                    );

                    // Import Stats
                    foreach ($pokemonDetails['stats'] ?? [] as $statData) {
                        PokemonStat::updateOrCreate(
                            [
                                'pokemon_id' => $pokemon->id,
                                'stat_name' => $statData['stat']['name'],
                            ],
                            [
                                'base_stat' => $statData['base_stat'],
                                'effort' => $statData['effort'],
                            ]
                        );
                    }

                    // Sync Types
                    $typeIds = [];
                    foreach ($pokemonDetails['types'] ?? [] as $typeData) {
                        $type = Type::where('name', $typeData['type']['name'])->first();
                        if ($type) {
                            $typeIds[$type->id] = ['slot' => $typeData['slot']];
                        }
                    }
                    $pokemon->types()->sync($typeIds);

                    // Sync Abilities
                    $abilityIds = [];
                    foreach ($pokemonDetails['abilities'] ?? [] as $abilityData) {
                        $ability = Ability::where('name', $abilityData['ability']['name'])->first();
                        if ($ability) {
                            $abilityIds[$ability->id] = [
                                'is_hidden' => $abilityData['is_hidden'],
                                'slot' => $abilityData['slot'],
                            ];
                        }
                    }
                    $pokemon->abilities()->sync($abilityIds);

                    // Sync Moves
                    $moveIds = [];
                    foreach ($pokemonDetails['moves'] ?? [] as $moveData) {
                        $move = Move::where('name', $moveData['move']['name'])->first();
                        if ($move && !isset($moveIds[$move->id])) {
                            $versionGroupDetails = $moveData['version_group_details'][0] ?? null;
                            $moveIds[$move->id] = [
                                'learn_method' => $versionGroupDetails['move_learn_method']['name'] ?? null,
                                'level_learned_at' => $versionGroupDetails['level_learned_at'] ?? null,
                            ];
                        }
                    }
                    $pokemon->moves()->sync($moveIds);

                    // Sync Held Items (simplified - store first version details for each item)
                    $itemIds = [];
                    foreach ($pokemonDetails['held_items'] ?? [] as $heldItemData) {
                        $item = Item::where('name', $heldItemData['item']['name'])->first();
                        if ($item) {
                            $versionDetail = $heldItemData['version_details'][0] ?? null;
                            if ($versionDetail) {
                                $itemIds[$item->id] = [
                                    'rarity' => $versionDetail['rarity'] ?? null,
                                    'version' => $versionDetail['version']['name'] ?? null,
                                ];
                            }
                        }
                    }
                    $pokemon->items()->sync($itemIds);

                    // Import Game Indices (simplified - remove URLs and sub-objects)
                    PokemonGameIndex::where('pokemon_id', $pokemon->id)->delete();
                    foreach ($pokemonDetails['game_indices'] ?? [] as $gameIndexData) {
                        PokemonGameIndex::create([
                            'pokemon_id' => $pokemon->id,
                            'game_index' => $gameIndexData['game_index'],
                            'version' => $gameIndexData['version']['name'] ?? null,
                        ]);
                    }

                    $this->successCount++;
                    $totalImported++;
                    $bar->advance();
                    usleep($this->delay * 1000);

                } catch (\Exception $e) {
                    $this->errorCount++;
                    $this->warn("\nError importing pokemon: " . $e->getMessage());
                }
            }

            $bar->finish();
            $this->newLine();
            $offset += $limit;

        } while (!empty($pokemonList) && (!$maxPokemon || $totalImported < $maxPokemon));

        $this->info('Pokemon imported: ' . Pokemon::count());
    }

    /**
     * Fetch data from PokeAPI
     */
    private function fetchFromApi(string $endpoint): array
    {
        $url = $this->baseUrl . $endpoint;

        $response = Http::timeout(30)->get($url);

        if (!$response->successful()) {
            throw new \Exception("Failed to fetch from {$url}: {$response->status()}");
        }

        return $response->json();
    }

    /**
     * Extract ID from PokeAPI URL
     * Example: https://pokeapi.co/api/v2/pokemon/1/ -> 1
     */
    private function extractIdFromUrl(string $url): int
    {
        $parts = explode('/', rtrim($url, '/'));
        return (int) end($parts);
    }

}
