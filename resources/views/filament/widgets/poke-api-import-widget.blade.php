<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            🎮 PokéAPI Data Importer
        </x-slot>

        <x-slot name="description">
            Import Pokemon, Moves, Abilities, Items, and related data from PokéAPI with real-time progress tracking
        </x-slot>

        <x-slot name="headerEnd">
            @if(!$isImporting)
                @php
                    $counts = $this->getCounts();
                @endphp
                <div class="flex flex-wrap gap-2">
                    @if($counts['types'] > 0)
                        <x-filament::button
                            wire:click="clearTypes"
                            wire:confirm="Are you sure you want to delete all Types? This may affect Moves and Pokemon."
                            color="danger"
                            size="xs"
                            icon="heroicon-o-trash"
                        >
                            Clear Types
                        </x-filament::button>
                    @endif
                    @if($counts['pokemon'] > 0)
                        <x-filament::button
                            wire:click="clearPokemon"
                            wire:confirm="Are you sure you want to delete all Pokemon and related data?"
                            color="danger"
                            size="xs"
                            icon="heroicon-o-trash"
                        >
                            Clear Pokemon
                        </x-filament::button>
                    @endif
                </div>
            @endif
        </x-slot>

        @if (!$isImporting)
            <form wire:submit="startImport" class="space-y-6">
                {{ $this->form }}

                <div class="flex items-center justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                    <x-filament::button
                        type="submit"
                        size="lg"
                        icon="heroicon-o-arrow-down-tray"
                    >
                        🚀 Start Import
                    </x-filament::button>
                </div>
            </form>

            {{-- Additional Clear Actions Grid --}}
            @php
                $counts = $this->getCounts();
            @endphp
            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Quick Clear Actions</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @if($counts['abilities'] > 0)
                        <x-filament::button
                            wire:click="clearAbilities"
                            wire:confirm="Delete all Abilities?"
                            color="gray"
                            size="xs"
                            outlined
                        >
                            Clear Abilities ({{ $counts['abilities'] }})
                        </x-filament::button>
                    @endif
                    @if($counts['moves'] > 0)
                        <x-filament::button
                            wire:click="clearMoves"
                            wire:confirm="Delete all Moves?"
                            color="gray"
                            size="xs"
                            outlined
                        >
                            Clear Moves ({{ $counts['moves'] }})
                        </x-filament::button>
                    @endif
                    @if($counts['items'] > 0)
                        <x-filament::button
                            wire:click="clearItems"
                            wire:confirm="Delete all Items?"
                            color="gray"
                            size="xs"
                            outlined
                        >
                            Clear Items ({{ $counts['items'] }})
                        </x-filament::button>
                    @endif
                    @if($counts['species'] > 0)
                        <x-filament::button
                            wire:click="clearSpecies"
                            wire:confirm="Delete all Species?"
                            color="gray"
                            size="xs"
                            outlined
                        >
                            Clear Species ({{ $counts['species'] }})
                        </x-filament::button>
                    @endif
                    @if(\App\Models\EvolutionChain::count() > 0)
                        <x-filament::button
                            wire:click="clearEvolutionChains"
                            wire:confirm="Delete all Evolution Chains?"
                            color="gray"
                            size="xs"
                            outlined
                        >
                            Clear Evolutions ({{ \App\Models\EvolutionChain::count() }})
                        </x-filament::button>
                    @endif
                </div>
            </div>
        @else
            {{-- Progress Display --}}
            <div wire:poll.250ms="updateProgress" class="space-y-8">
                {{-- Header with Current Step --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ $this->getStepLabel() }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2 font-medium">
                            {{ $progress['message'] ?? 'Initializing...' }}
                        </p>
                    </div>
                    <x-filament::button
                        wire:click="stopImport"
                        color="danger"
                        size="md"
                        icon="heroicon-o-stop-circle"
                    >
                        Stop Import
                    </x-filament::button>
                </div>

                {{-- Progress Bar Section --}}
                <div class="space-y-3">
                    <div class="flex justify-between items-baseline">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Progress
                        </span>
                        <div class="text-right">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $progress['current'] ?? 0 }} / {{ $progress['total'] ?? 0 }}
                            </span>
                            <span class="ml-2 text-lg font-bold text-primary-600 dark:text-primary-400">
                                {{ $this->getProgressPercentage() }}%
                            </span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden shadow-inner">
                        <div
                            class="bg-gradient-to-r from-primary-500 to-primary-600 dark:from-primary-600 dark:to-primary-500 h-4 rounded-full transition-all duration-500 ease-out shadow-sm"
                            style="width: {{ $this->getProgressPercentage() }}%"
                        >
                        </div>
                    </div>
                </div>

                {{-- Statistics Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="bg-success-50 dark:bg-success-900/20 rounded-xl p-6 border border-success-200 dark:border-success-800">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-12 h-12 bg-success-100 dark:bg-success-900/40 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-success-600 dark:text-success-400">Success</p>
                                <p class="text-3xl font-bold text-success-700 dark:text-success-300 mt-1">{{ $successCount }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-danger-50 dark:bg-danger-900/20 rounded-xl p-6 border border-danger-200 dark:border-danger-800">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-12 h-12 bg-danger-100 dark:bg-danger-900/40 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-danger-600 dark:text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-danger-600 dark:text-danger-400">Errors</p>
                                <p class="text-3xl font-bold text-danger-700 dark:text-danger-300 mt-1">{{ $errorCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </x-filament::section>

    @script
    <script>
        $wire.on('import-error', (event) => {
            new FilamentNotification()
                .title('Import Error')
                .danger()
                .body(event.message)
                .send();
        });

        $wire.on('import-completed', () => {
            new FilamentNotification()
                .title('Import Complete!')
                .success()
                .body('Data has been imported successfully')
                .duration(5000)
                .send();
        });

        $wire.on('data-cleared', (event) => {
            new FilamentNotification()
                .title('Data Cleared')
                .success()
                .body(event.type + ' have been deleted successfully')
                .send();
        });
    </script>
    @endscript
</x-filament-widgets::widget>
