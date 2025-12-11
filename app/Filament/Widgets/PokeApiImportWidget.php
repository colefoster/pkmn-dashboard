<?php

namespace App\Filament\Widgets;

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
use App\Services\PokeApiImporter;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class PokeApiImportWidget extends Widget
{
    protected string $view = 'filament.widgets.poke-api-import-widget';

    protected int | string | array $columnSpan = 'full';

    public int $delay = 100;
    public int $limit = 50;
    public ?int $maxPokemon = null;
    public bool $isImporting = false;
    public array $progress = [];
    public int $successCount = 0;
    public int $errorCount = 0;

    // Individual import type selections
    public bool $importTypes = false;
    public bool $importAbilities = false;
    public bool $importMoves = false;
    public bool $importItems = false;
    public bool $importSpecies = false;
    public bool $importEvolutionChains = false;
    public bool $importPokemon = false;

    public function mount(): void
    {
        $this->resetProgress();
    }

    public function startImport(): void
    {
        $this->validate([
            'delay' => 'required|integer|min:0|max:2000',
            'limit' => 'required|integer|min:1|max:100',
            'maxPokemon' => 'nullable|integer|min:1|max:10000',
        ]);

        // Validate at least one type is selected
        if (!$this->importTypes && !$this->importAbilities && !$this->importMoves &&
            !$this->importItems && !$this->importSpecies && !$this->importEvolutionChains &&
            !$this->importPokemon) {
            $this->dispatch('import-error', message: 'Please select at least one data type to import');
            return;
        }

        $this->isImporting = true;
        $this->resetProgress();

        // Build the command with selected types
        $flags = [];
        if ($this->importTypes) $flags[] = '--types';
        if ($this->importAbilities) $flags[] = '--abilities';
        if ($this->importMoves) $flags[] = '--moves';
        if ($this->importItems) $flags[] = '--items';
        if ($this->importSpecies) $flags[] = '--species';
        if ($this->importEvolutionChains) $flags[] = '--evolution-chains';
        if ($this->importPokemon) $flags[] = '--pokemon';

        $command = sprintf(
            'php %s/artisan pokeapi:import --delay=%d --limit=%d%s %s > /dev/null 2>&1 &',
            base_path(),
            $this->delay,
            $this->limit,
            $this->maxPokemon ? " --max={$this->maxPokemon}" : '',
            implode(' ', $flags)
        );

        // Run in background
        exec($command);

        // Dispatch browser event to start polling
        $this->dispatch('import-started');
    }

    #[On('poll-import')]
    public function pollImport(): void
    {
        if (!$this->isImporting) {
            return;
        }

        // Read progress from cache
        $importer = new PokeApiImporter();
        $data = $importer->getProgress();

        $this->progress = $data['progress'] ?? $this->progress;
        $this->successCount = $data['successCount'] ?? 0;
        $this->errorCount = $data['errorCount'] ?? 0;

        // Check if import is complete (no changes in progress for a while could indicate completion)
        // You could add more sophisticated completion detection here
    }

    public function stopImport(): void
    {
        $this->isImporting = false;
        $this->importer = null;
        $this->dispatch('import-stopped');
    }

    private function resetProgress(): void
    {
        $this->progress = [
            'current_step' => 'start',
            'total' => 0,
            'current' => 0,
            'message' => 'Ready to import',
        ];
        $this->successCount = 0;
        $this->errorCount = 0;
    }

    public function getProgressPercentage(): float
    {
        if (!isset($this->progress['total']) || $this->progress['total'] === 0) {
            return 0;
        }

        return round(($this->progress['current'] / $this->progress['total']) * 100, 1);
    }

    public function getStepLabel(): string
    {
        return match($this->progress['current_step'] ?? 'start') {
            'start' => 'Starting...',
            'types' => 'Importing Types',
            'abilities' => 'Importing Abilities',
            'moves' => 'Importing Moves',
            'items' => 'Importing Items',
            'species' => 'Importing Pokemon Species',
            'evolution_chains' => 'Importing Evolution Chains',
            'pokemon' => 'Importing Pokemon',
            'complete' => 'Import Complete!',
            default => 'Ready',
        };
    }

    // Dependency checking methods
    public function canImportTypes(): bool
    {
        return true; // Types have no dependencies
    }

    public function canImportAbilities(): bool
    {
        return true; // Abilities have no dependencies
    }

    public function canImportMoves(): bool
    {
        return Type::count() > 0; // Moves depend on Types
    }

    public function canImportItems(): bool
    {
        return true; // Items have no dependencies
    }

    public function canImportSpecies(): bool
    {
        return true; // Species have no dependencies
    }

    public function canImportEvolutionChains(): bool
    {
        return PokemonSpecies::count() > 0; // Evolution chains depend on Species
    }

    public function canImportPokemon(): bool
    {
        return Type::count() > 0
            && Ability::count() > 0
            && Move::count() > 0
            && Item::count() > 0
            && PokemonSpecies::count() > 0; // Pokemon depend on everything
    }

    // Get dependency message for disabled imports
    public function getDependencyMessage(string $type): string
    {
        return match($type) {
            'moves' => Type::count() === 0 ? 'Requires Types to be imported first' : '',
            'evolution_chains' => PokemonSpecies::count() === 0 ? 'Requires Species to be imported first' : '',
            'pokemon' => $this->getPokemonDependencies(),
            default => '',
        };
    }

    private function getPokemonDependencies(): string
    {
        $missing = [];
        if (Type::count() === 0) $missing[] = 'Types';
        if (Ability::count() === 0) $missing[] = 'Abilities';
        if (Move::count() === 0) $missing[] = 'Moves';
        if (Item::count() === 0) $missing[] = 'Items';
        if (PokemonSpecies::count() === 0) $missing[] = 'Species';

        return !empty($missing) ? 'Requires: ' . implode(', ', $missing) : '';
    }

    // Get current database counts
    public function getCounts(): array
    {
        return [
            'types' => Type::count(),
            'abilities' => Ability::count(),
            'moves' => Move::count(),
            'items' => Item::count(),
            'species' => PokemonSpecies::count(),
            'pokemon' => Pokemon::count(),
        ];
    }

    // Mass clear/delete methods
    public function clearTypes(): void
    {
        Type::query()->delete();
        $this->dispatch('data-cleared', type: 'Types');
    }

    public function clearAbilities(): void
    {
        Ability::query()->delete();
        $this->dispatch('data-cleared', type: 'Abilities');
    }

    public function clearMoves(): void
    {
        Move::query()->delete();
        $this->dispatch('data-cleared', type: 'Moves');
    }

    public function clearItems(): void
    {
        Item::query()->delete();
        $this->dispatch('data-cleared', type: 'Items');
    }

    public function clearSpecies(): void
    {
        PokemonSpecies::query()->delete();
        $this->dispatch('data-cleared', type: 'Species');
    }

    public function clearEvolutionChains(): void
    {
        Evolution::query()->delete();
        EvolutionChain::query()->delete();
        $this->dispatch('data-cleared', type: 'Evolution Chains');
    }

    public function clearPokemon(): void
    {
        // Clear related data first
        PokemonStat::query()->delete();
        PokemonGameIndex::query()->delete();

        // Clear pivot tables (handled by sync)
        \DB::table('pokemon_type')->truncate();
        \DB::table('ability_pokemon')->truncate();
        \DB::table('move_pokemon')->truncate();
        \DB::table('pokemon_item')->truncate();

        // Clear pokemon
        Pokemon::query()->delete();
        $this->dispatch('data-cleared', type: 'Pokemon');
    }
}
