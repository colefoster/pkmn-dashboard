<x-filament-widgets::widget>
    <x-filament::section
        heading="Import Actions"
        description="Run import scripts to populate the database with Pokemon data from PokeAPI."
    >
        <div class="flex flex-wrap gap-3">
            {{ ($this->importGen1Action) }}
            {{ ($this->importGen2Action) }}
            {{ ($this->importAllAction) }}
        </div>

        <x-slot name="footerActions">
            <x-filament::link
                icon="heroicon-m-information-circle"
                :href="'#'"
                color="gray"
                size="sm"
            >
                Large imports may timeout. Consider using CLI: <code class="text-xs">php artisan pokemon:import --max=151</code>
            </x-filament::link>
        </x-slot>
    </x-filament::section>
</x-filament-widgets::widget>
