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

                <!-- Import Type Selection -->
                <div>
                    <label class="block text-sm font-medium mb-3">Select Data Types to Import</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @php
                            $counts = $this->getCounts();
                        @endphp

                        <!-- Types -->
                        <div class="flex items-start space-x-3 p-3 rounded-lg border {{ $this->canImportTypes() ? 'border-gray-300 dark:border-gray-600' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50' }}">
                            <input type="checkbox" wire:model="importTypes" {{ $this->canImportTypes() ? '' : 'disabled' }} class="mt-1 fi-checkbox rounded" />
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium">Types</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700">{{ $counts['types'] }} in DB</span>
                                        @if($counts['types'] > 0)
                                            <button wire:click="clearTypes" wire:confirm="Are you sure you want to delete all Types?" type="button" class="text-xs text-red-600 dark:text-red-400 hover:underline">
                                                🗑️ Clear
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                @if(!$this->canImportTypes())
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $this->getDependencyMessage('types') }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Abilities -->
                        <div class="flex items-start space-x-3 p-3 rounded-lg border {{ $this->canImportAbilities() ? 'border-gray-300 dark:border-gray-600' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50' }}">
                            <input type="checkbox" wire:model="importAbilities" {{ $this->canImportAbilities() ? '' : 'disabled' }} class="mt-1 fi-checkbox rounded" />
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium">Abilities</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700">{{ $counts['abilities'] }} in DB</span>
                                        @if($counts['abilities'] > 0)
                                            <button wire:click="clearAbilities" wire:confirm="Are you sure you want to delete all Abilities?" type="button" class="text-xs text-red-600 dark:text-red-400 hover:underline">
                                                🗑️ Clear
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                @if(!$this->canImportAbilities())
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $this->getDependencyMessage('abilities') }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Moves -->
                        <div class="flex items-start space-x-3 p-3 rounded-lg border {{ $this->canImportMoves() ? 'border-gray-300 dark:border-gray-600' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50' }}">
                            <input type="checkbox" wire:model="importMoves" {{ $this->canImportMoves() ? '' : 'disabled' }} class="mt-1 fi-checkbox rounded" />
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium">Moves</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700">{{ $counts['moves'] }} in DB</span>
                                        @if($counts['moves'] > 0)
                                            <button wire:click="clearMoves" wire:confirm="Are you sure you want to delete all Moves?" type="button" class="text-xs text-red-600 dark:text-red-400 hover:underline">
                                                🗑️ Clear
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                @if(!$this->canImportMoves())
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $this->getDependencyMessage('moves') }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Items -->
                        <div class="flex items-start space-x-3 p-3 rounded-lg border {{ $this->canImportItems() ? 'border-gray-300 dark:border-gray-600' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50' }}">
                            <input type="checkbox" wire:model="importItems" {{ $this->canImportItems() ? '' : 'disabled' }} class="mt-1 fi-checkbox rounded" />
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium">Items</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700">{{ $counts['items'] }} in DB</span>
                                        @if($counts['items'] > 0)
                                            <button wire:click="clearItems" wire:confirm="Are you sure you want to delete all Items?" type="button" class="text-xs text-red-600 dark:text-red-400 hover:underline">
                                                🗑️ Clear
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                @if(!$this->canImportItems())
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $this->getDependencyMessage('items') }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Species -->
                        <div class="flex items-start space-x-3 p-3 rounded-lg border {{ $this->canImportSpecies() ? 'border-gray-300 dark:border-gray-600' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50' }}">
                            <input type="checkbox" wire:model="importSpecies" {{ $this->canImportSpecies() ? '' : 'disabled' }} class="mt-1 fi-checkbox rounded" />
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium">Species</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700">{{ $counts['species'] }} in DB</span>
                                        @if($counts['species'] > 0)
                                            <button wire:click="clearSpecies" wire:confirm="Are you sure you want to delete all Species?" type="button" class="text-xs text-red-600 dark:text-red-400 hover:underline">
                                                🗑️ Clear
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                @if(!$this->canImportSpecies())
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $this->getDependencyMessage('species') }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Evolution Chains -->
                        <div class="flex items-start space-x-3 p-3 rounded-lg border {{ $this->canImportEvolutionChains() ? 'border-gray-300 dark:border-gray-600' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50' }}">
                            <input type="checkbox" wire:model="importEvolutionChains" {{ $this->canImportEvolutionChains() ? '' : 'disabled' }} class="mt-1 fi-checkbox rounded" />
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium">Evolution Chains</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700">{{ \App\Models\EvolutionChain::count() }} in DB</span>
                                        @if(\App\Models\EvolutionChain::count() > 0)
                                            <button wire:click="clearEvolutionChains" wire:confirm="Are you sure you want to delete all Evolution Chains?" type="button" class="text-xs text-red-600 dark:text-red-400 hover:underline">
                                                🗑️ Clear
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                @if(!$this->canImportEvolutionChains())
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $this->getDependencyMessage('evolution_chains') }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Pokemon -->
                        <div class="flex items-start space-x-3 p-3 rounded-lg border {{ $this->canImportPokemon() ? 'border-gray-300 dark:border-gray-600' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50' }} md:col-span-2">
                            <input type="checkbox" wire:model="importPokemon" {{ $this->canImportPokemon() ? '' : 'disabled' }} class="mt-1 fi-checkbox rounded" />
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium">Pokemon</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700">{{ $counts['pokemon'] }} in DB</span>
                                        @if($counts['pokemon'] > 0)
                                            <button wire:click="clearPokemon" wire:confirm="Are you sure you want to delete all Pokemon and related data?" type="button" class="text-xs text-red-600 dark:text-red-400 hover:underline">
                                                🗑️ Clear
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                @if(!$this->canImportPokemon())
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $this->getDependencyMessage('pokemon') }}</p>
                                @endif
                            </div>
                        </div>
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
