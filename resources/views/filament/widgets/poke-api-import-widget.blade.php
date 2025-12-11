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
                <div class="flex gap-2">
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
            <form wire:submit="startImport">
                {{ $this->form }}

                <x-filament::button
                    type="submit"
                    class="mt-6"
                    icon="heroicon-o-arrow-down-tray"
                >
                    🚀 Start Import
                </x-filament::button>
            </form>

            {{-- Additional Clear Actions Grid --}}
            @php
                $counts = $this->getCounts();
            @endphp
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-medium mb-3">Quick Clear Actions</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
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
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $this->getStepLabel() }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ $progress['message'] ?? 'Processing...' }}
                        </p>
                    </div>
                    <x-filament::button
                        wire:click="stopImport"
                        color="danger"
                        size="sm"
                        icon="heroicon-o-stop-circle"
                    >
                        Stop Import
                    </x-filament::button>
                </div>

                {{-- Progress Bar --}}
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-600 dark:text-gray-400">
                            {{ $progress['current'] ?? 0 }} / {{ $progress['total'] ?? 0 }}
                        </span>
                        <span class="font-medium text-primary-600 dark:text-primary-400">
                            {{ $this->getProgressPercentage() }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                        <div
                            class="bg-primary-600 dark:bg-primary-500 h-3 rounded-full transition-all duration-300 ease-out"
                            style="width: {{ $this->getProgressPercentage() }}%"
                        >
                        </div>
                    </div>
                </div>

                {{-- Statistics Cards --}}
                <div class="grid grid-cols-2 gap-4">
                    <x-filament::section
                        :heading="'✅ Success'"
                        class="bg-success-50 dark:bg-success-900/20"
                    >
                        <div class="text-3xl font-bold text-success-600 dark:text-success-400">
                            {{ $successCount }}
                        </div>
                    </x-filament::section>

                    <x-filament::section
                        :heading="'❌ Errors'"
                        class="bg-danger-50 dark:bg-danger-900/20"
                    >
                        <div class="text-3xl font-bold text-danger-600 dark:text-danger-400">
                            {{ $errorCount }}
                        </div>
                    </x-filament::section>
                </div>
            </div>
        @endif
    </x-filament::section>

    @script
    <script>
        let pollInterval = null;

        $wire.on('import-started', () => {
            pollInterval = setInterval(() => {
                $wire.dispatch('poll-import');
            }, 100);
        });

        $wire.on('import-stopped', () => {
            if (pollInterval) {
                clearInterval(pollInterval);
                pollInterval = null;
            }
        });

        $wire.on('import-error', (event) => {
            if (pollInterval) {
                clearInterval(pollInterval);
                pollInterval = null;
            }
            new FilamentNotification()
                .title('Import Error')
                .danger()
                .body(event.message)
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
