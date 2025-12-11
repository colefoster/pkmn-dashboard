<?php

namespace App\Services;

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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PokeApiImporter
{
    private string $baseUrl = 'https://pokeapi.co/api/v2';
    private int $delay;
    private string $cacheKey = 'pokeapi_import_progress';
    public array $progress = [];
    public int $successCount = 0;
    public int $errorCount = 0;

    public function __construct(int $delay = 100)
    {
        $this->delay = $delay;
        $this->resetProgress();
    }

    public function resetProgress(): void
    {
        $this->progress = [
            'current_step' => '',
            'total' => 0,
            'current' => 0,
            'message' => '',
        ];
        $this->successCount = 0;
        $this->errorCount = 0;
        $this->saveProgress();
    }

    private function saveProgress(): void
    {
        Cache::put($this->cacheKey, [
            'progress' => $this->progress,
            'successCount' => $this->successCount,
            'errorCount' => $this->errorCount,
        ], now()->addHours(1));
    }

    public function getProgress(): array
    {
        return Cache::get($this->cacheKey, [
            'progress' => $this->progress,
            'successCount' => 0,
            'errorCount' => 0,
        ]);
    }

    public function importAll(?int $maxPokemon = null, int $limit = 50, array $types = []): void
    {
        set_time_limit(0);

        // If no types specified, import all
        if (empty($types)) {
            $types = ['types', 'abilities', 'moves', 'items', 'species', 'evolution_chains', 'pokemon'];
        }

        if (in_array('types', $types)) {
            $this->importTypes();
        }

        if (in_array('abilities', $types)) {
            $this->importAbilities();
        }

        if (in_array('moves', $types)) {
            $this->importMoves();
        }

        if (in_array('items', $types)) {
            $this->importItems();
        }

        if (in_array('species', $types)) {
            $this->importPokemonSpecies($maxPokemon);
        }

        if (in_array('evolution_chains', $types)) {
            $this->importEvolutionChains();
        }

        if (in_array('pokemon', $types)) {
            $this->importPokemon($limit, $maxPokemon);
        }

        // Mark as complete
        $this->progress['current_step'] = 'complete';
        $this->progress['message'] = 'Import completed!';
        $this->saveProgress();
    }

    public function importTypes(): void
    {
        $this->progress['current_step'] = 'types';
        $response = $this->fetchFromApi('/type');
        $types = $response['results'] ?? [];
        $this->progress['total'] = count($types);

        foreach ($types as $index => $typeData) {
            try {
                $typeId = $this->extractIdFromUrl($typeData['url']);
                $typeDetails = $this->fetchFromApi("/type/{$typeId}");

                Type::updateOrCreate(
                    ['api_id' => $typeDetails['id']],
                    ['name' => $typeDetails['name']]
                );

                $this->successCount++;
                $this->progress['current'] = $index + 1;
                $this->progress['message'] = "Imported: {$typeDetails['name']}";
                $this->saveProgress();
                usleep($this->delay * 1000);
            } catch (\Exception $e) {
                $this->errorCount++;
            }
        }
    }

    public function importAbilities(): void
    {
        $this->progress['current_step'] = 'abilities';
        $offset = 0;
        $limit = 100;

        do {
            $response = $this->fetchFromApi("/ability?limit={$limit}&offset={$offset}");
            $abilities = $response['results'] ?? [];
            if (empty($abilities)) break;

            $this->progress['total'] = $offset + count($abilities);

            foreach ($abilities as $index => $abilityData) {
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
                    $this->progress['current'] = $offset + $index + 1;
                    $this->progress['message'] = "Imported: {$abilityDetails['name']}";
                    $this->saveProgress();
                    usleep($this->delay * 1000);
                } catch (\Exception $e) {
                    $this->errorCount++;
                }
            }

            $offset += $limit;
        } while (!empty($abilities));
    }

    public function importMoves(): void
    {
        $this->progress['current_step'] = 'moves';
        $offset = 0;
        $limit = 100;

        do {
            $response = $this->fetchFromApi("/move?limit={$limit}&offset={$offset}");
            $moves = $response['results'] ?? [];
            if (empty($moves)) break;

            $this->progress['total'] = $offset + count($moves);

            foreach ($moves as $index => $moveData) {
                try {
                    $moveId = $this->extractIdFromUrl($moveData['url']);
                    $moveDetails = $this->fetchFromApi("/move/{$moveId}");

                    $typeId = null;
                    if (isset($moveDetails['type']['name'])) {
                        $type = Type::where('name', $moveDetails['type']['name'])->first();
                        $typeId = $type?->id;
                    }

                    $effectEntry = collect($moveDetails['effect_entries'] ?? [])
                        ->firstWhere('language.name', 'en');
                    $flavorTextEntry = collect($moveDetails['flavor_text_entries'] ?? [])
                        ->firstWhere('language.name', 'en');
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
                    $this->progress['current'] = $offset + $index + 1;
                    $this->progress['message'] = "Imported: {$moveDetails['name']}";
                    $this->saveProgress();
                    usleep($this->delay * 1000);
                } catch (\Exception $e) {
                    $this->errorCount++;
                }
            }

            $offset += $limit;
        } while (!empty($moves));
    }

    public function importItems(): void
    {
        $this->progress['current_step'] = 'items';
        $offset = 0;
        $limit = 100;

        do {
            $response = $this->fetchFromApi("/item?limit={$limit}&offset={$offset}");
            $items = $response['results'] ?? [];
            if (empty($items)) break;

            $this->progress['total'] = $offset + count($items);

            foreach ($items as $index => $itemData) {
                try {
                    $itemId = $this->extractIdFromUrl($itemData['url']);
                    $itemDetails = $this->fetchFromApi("/item/{$itemId}");

                    $effectEntry = collect($itemDetails['effect_entries'] ?? [])
                        ->firstWhere('language.name', 'en');
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
                    $this->progress['current'] = $offset + $index + 1;
                    $this->progress['message'] = "Imported: {$itemDetails['name']}";
                    $this->saveProgress();
                    usleep($this->delay * 1000);
                } catch (\Exception $e) {
                    $this->errorCount++;
                }
            }

            $offset += $limit;
        } while (!empty($items));
    }

    public function importPokemonSpecies(?int $maxPokemon): void
    {
        $this->progress['current_step'] = 'species';
        $offset = 0;
        $limit = 100;
        $totalImported = 0;

        do {
            $response = $this->fetchFromApi("/pokemon-species?limit={$limit}&offset={$offset}");
            $speciesList = $response['results'] ?? [];

            if (empty($speciesList) || ($maxPokemon && $totalImported >= $maxPokemon)) {
                break;
            }

            $this->progress['total'] = $maxPokemon ?? ($offset + count($speciesList));

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
                    $this->progress['current'] = $totalImported;
                    $this->progress['message'] = "Imported: {$speciesDetails['name']}";
                    $this->saveProgress();
                    usleep($this->delay * 1000);
                } catch (\Exception $e) {
                    $this->errorCount++;
                }
            }

            $offset += $limit;
        } while (!empty($speciesList) && (!$maxPokemon || $totalImported < $maxPokemon));
    }

    public function importEvolutionChains(): void
    {
        $this->progress['current_step'] = 'evolution_chains';
        $offset = 0;
        $limit = 100;

        do {
            $response = $this->fetchFromApi("/evolution-chain?limit={$limit}&offset={$offset}");
            $chains = $response['results'] ?? [];
            if (empty($chains)) break;

            $this->progress['total'] = $offset + count($chains);

            foreach ($chains as $index => $chainData) {
                try {
                    $chainId = $this->extractIdFromUrl($chainData['url']);
                    $chainDetails = $this->fetchFromApi("/evolution-chain/{$chainId}");

                    $evolutionChain = EvolutionChain::updateOrCreate(
                        ['api_id' => $chainDetails['id']],
                        ['baby_trigger_item' => $chainDetails['baby_trigger_item']['name'] ?? null]
                    );

                    $this->parseEvolutionChain($evolutionChain, $chainDetails['chain']);

                    $this->successCount++;
                    $this->progress['current'] = $offset + $index + 1;
                    $this->progress['message'] = "Imported chain #{$chainId}";
                    $this->saveProgress();
                    usleep($this->delay * 1000);
                } catch (\Exception $e) {
                    $this->errorCount++;
                }
            }

            $offset += $limit;
        } while (!empty($chains));
    }

    private function parseEvolutionChain(EvolutionChain $evolutionChain, array $chainNode, ?int $fromSpeciesId = null): void
    {
        $speciesName = $chainNode['species']['name'];
        $species = PokemonSpecies::where('name', $speciesName)->first();

        if (!$species) {
            return;
        }

        $species->update(['evolution_chain_id' => $evolutionChain->id]);

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

        foreach ($chainNode['evolves_to'] ?? [] as $evolution) {
            $this->parseEvolutionChain($evolutionChain, $evolution, $species->id);
        }
    }

    public function importPokemon(int $limit, ?int $maxPokemon): void
    {
        $this->progress['current_step'] = 'pokemon';
        $offset = 0;
        $totalImported = 0;

        do {
            $response = $this->fetchFromApi("/pokemon?limit={$limit}&offset={$offset}");
            $pokemonList = $response['results'] ?? [];

            if (empty($pokemonList) || ($maxPokemon && $totalImported >= $maxPokemon)) {
                break;
            }

            $this->progress['total'] = $maxPokemon ?? ($offset + count($pokemonList));

            foreach ($pokemonList as $pokemonData) {
                if ($maxPokemon && $totalImported >= $maxPokemon) break;

                try {
                    $pokemonId = $this->extractIdFromUrl($pokemonData['url']);
                    $pokemonDetails = $this->fetchFromApi("/pokemon/{$pokemonId}");

                    $speciesId = null;
                    if (isset($pokemonDetails['species']['name'])) {
                        $species = PokemonSpecies::where('name', $pokemonDetails['species']['name'])->first();
                        $speciesId = $species?->id;
                    }

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

                    // Sync Held Items
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

                    // Import Game Indices
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
                    $this->progress['current'] = $totalImported;
                    $this->progress['message'] = "Imported: {$pokemonDetails['name']}";
                    $this->saveProgress();
                    usleep($this->delay * 1000);

                } catch (\Exception $e) {
                    $this->errorCount++;
                }
            }

            $offset += $limit;
        } while (!empty($pokemonList) && (!$maxPokemon || $totalImported < $maxPokemon));
    }

    private function fetchFromApi(string $endpoint): array
    {
        $url = $this->baseUrl . $endpoint;
        $response = Http::timeout(30)->get($url);

        if (!$response->successful()) {
            throw new \Exception("Failed to fetch from {$url}: {$response->status()}");
        }

        return $response->json();
    }

    private function extractIdFromUrl(string $url): int
    {
        $parts = explode('/', rtrim($url, '/'));
        return (int) end($parts);
    }
}
