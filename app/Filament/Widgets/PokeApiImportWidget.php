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
use Filament\Forms\Components\Checkbox;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class PokeApiImportWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.widgets.poke-api-import-widget';

    protected int | string | array $columnSpan = 'full';

    public ?array $data = [];

    public bool $isImporting = false;
    public array $progress = [];
    public int $successCount = 0;
    public int $errorCount = 0;

    public function mount(): void
    {
        $this->resetProgress();
        $this->form->fill([
            'delay' => 100,
            'limit' => 50,
            'maxPokemon' => null,
            'importTypes' => false,
            'importAbilities' => false,
            'importMoves' => false,
            'importItems' => false,
            'importSpecies' => false,
            'importEvolutionChains' => false,
            'importPokemon' => false,
        ]);
    }

    protected function getFormSchema(): array
    {
        $counts = $this->getCounts();

        return [
            Section::make('Import Configuration')
                ->description('Configure API request parameters')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('delay')
                                ->label('Delay (ms)')
                                ->helperText('Delay between API requests')
                                ->numeric()
                                ->default(100)
                                ->minValue(0)
                                ->maxValue(2000)
                                ->required(),

                            TextInput::make('limit')
                                ->label('Limit per Page')
                                ->helperText('Items fetched per request')
                                ->numeric()
                                ->default(50)
                                ->minValue(1)
                                ->maxValue(100)
                                ->required(),

                            TextInput::make('maxPokemon')
                                ->label('Max Pokemon')
                                ->helperText('Leave empty for all')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(10000),
                        ]),
                ])
                ->collapsible(),

            Section::make('Select Data Types')
                ->description('Choose which data types to import (dependencies will be validated)')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Checkbox::make('importTypes')
                                ->label('Types')
                                ->helperText($counts['types'] . ' in database')
                                ->disabled(!$this->canImportTypes()),

                            Checkbox::make('importAbilities')
                                ->label('Abilities')
                                ->helperText($counts['abilities'] . ' in database')
                                ->disabled(!$this->canImportAbilities()),

                            Checkbox::make('importMoves')
                                ->label('Moves')
                                ->helperText($this->canImportMoves() ? $counts['moves'] . ' in database' : $this->getDependencyMessage('moves'))
                                ->disabled(!$this->canImportMoves()),

                            Checkbox::make('importItems')
                                ->label('Items')
                                ->helperText($counts['items'] . ' in database')
                                ->disabled(!$this->canImportItems()),

                            Checkbox::make('importSpecies')
                                ->label('Pokemon Species')
                                ->helperText($counts['species'] . ' in database')
                                ->disabled(!$this->canImportSpecies()),

                            Checkbox::make('importEvolutionChains')
                                ->label('Evolution Chains')
                                ->helperText($this->canImportEvolutionChains() ? EvolutionChain::count() . ' in database' : $this->getDependencyMessage('evolution_chains'))
                                ->disabled(!$this->canImportEvolutionChains()),

                            Checkbox::make('importPokemon')
                                ->label('Pokemon')
                                ->helperText($this->canImportPokemon() ? $counts['pokemon'] . ' in database' : $this->getDependencyMessage('pokemon'))
                                ->disabled(!$this->canImportPokemon())
                                ->columnSpan(2),
                        ]),
                ]),
        ];
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function startImport(): void
    {
        $data = $this->form->getState();

        // Validate at least one type is selected
        if (!($data['importTypes'] ?? false) && !($data['importAbilities'] ?? false) && !($data['importMoves'] ?? false) &&
            !($data['importItems'] ?? false) && !($data['importSpecies'] ?? false) && !($data['importEvolutionChains'] ?? false) &&
            !($data['importPokemon'] ?? false)) {
            $this->dispatch('import-error', message: 'Please select at least one data type to import');
            return;
        }

        $this->isImporting = true;
        $this->resetProgress();

        // Build the command with selected types
        $flags = [];
        if ($data['importTypes'] ?? false) $flags[] = '--types';
        if ($data['importAbilities'] ?? false) $flags[] = '--abilities';
        if ($data['importMoves'] ?? false) $flags[] = '--moves';
        if ($data['importItems'] ?? false) $flags[] = '--items';
        if ($data['importSpecies'] ?? false) $flags[] = '--species';
        if ($data['importEvolutionChains'] ?? false) $flags[] = '--evolution-chains';
        if ($data['importPokemon'] ?? false) $flags[] = '--pokemon';

        $command = sprintf(
            'php %s/artisan pokeapi:import --delay=%d --limit=%d%s %s > /dev/null 2>&1 &',
            base_path(),
            $data['delay'],
            $data['limit'],
            !empty($data['maxPokemon']) ? " --max={$data['maxPokemon']}" : '',
            implode(' ', $flags)
        );

        // Run in background
        exec($command);

        // Dispatch browser event to start polling
        $this->dispatch('import-started');
    }

    public function updateProgress(): void
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

        // Auto-stop when complete
        if (($this->progress['current_step'] ?? '') === 'complete') {
            $this->stopImport();
            $this->dispatch('import-completed');
        }
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
