<?php

namespace App\Filament\Widgets;

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

        $this->isImporting = true;
        $this->resetProgress();

        // Build the command
        $command = sprintf(
            'php %s/artisan pokeapi:import --delay=%d --limit=%d%s > /dev/null 2>&1 &',
            base_path(),
            $this->delay,
            $this->limit,
            $this->maxPokemon ? " --max={$this->maxPokemon}" : ''
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
}
