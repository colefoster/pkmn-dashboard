<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            PokéAPI Data Importer
        </x-slot>

        <x-slot name="description">
            Import Pokemon, Moves, Abilities, Items, and related data from PokéAPI
        </x-slot>

        <div class="space-y-6">
            @if (!$isImporting)
                <!-- Configuration Form -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Delay (ms)</label>
                        <input type="number" wire:model="delay" class="fi-input block w-full rounded-lg shadow-sm border-gray-300 dark:border-gray-600" min="0" max="2000" />
                        <p class="mt-1 text-xs text-gray-500">Delay between API requests</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Limit per Page</label>
                        <input type="number" wire:model="limit" class="fi-input block w-full rounded-lg shadow-sm border-gray-300 dark:border-gray-600" min="1" max="100" />
                        <p class="mt-1 text-xs text-gray-500">Items fetched per request</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Max Pokemon</label>
                        <input type="number" wire:model="maxPokemon" class="fi-input block w-full rounded-lg shadow-sm border-gray-300 dark:border-gray-600" min="1" max="10000" placeholder="All" />
                        <p class="mt-1 text-xs text-gray-500">Leave empty for all</p>
                    </div>
                </div>

                <!-- Start Button -->
                <div class="flex justify-end">
                    <button wire:click="startImport" class="fi-btn fi-btn-primary">
                        🚀 Start Import
                    </button>
                </div>
            @else
                <!-- Progress Display -->
                <div class="space-y-4">
                    <!-- Current Step -->
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">{{ $this->getStepLabel() }}</h3>
                        <button wire:click="stopImport" class="fi-btn fi-btn-danger text-sm">
                            ⏹️ Stop
                        </button>
                    </div>

                    <!-- Progress Bar -->
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span>{{ $progress['current'] ?? 0 }} / {{ $progress['total'] ?? 0 }}</span>
                            <span>{{ $this->getProgressPercentage() }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                            <div class="bg-primary-600 h-4 rounded-full transition-all duration-300"
                                 style="width: {{ $this->getProgressPercentage() }}%">
                            </div>
                        </div>
                    </div>

                    <!-- Current Message -->
                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <p class="text-sm font-mono">{{ $progress['message'] ?? 'Processing...' }}</p>
                    </div>

                    <!-- Statistics -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <div class="text-sm text-green-600 dark:text-green-400">Success</div>
                            <div class="text-2xl font-bold text-green-700 dark:text-green-300">{{ $successCount }}</div>
                        </div>
                        <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                            <div class="text-sm text-red-600 dark:text-red-400">Errors</div>
                            <div class="text-2xl font-bold text-red-700 dark:text-red-300">{{ $errorCount }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>

    @script
    <script>
        let pollInterval = null;

        $wire.on('import-started', () => {
            // Poll every 100ms for progress updates
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
            alert('Import error: ' + event.message);
        });
    </script>
    @endscript
</x-filament-widgets::widget>
