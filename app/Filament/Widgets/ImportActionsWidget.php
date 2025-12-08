<?php

namespace App\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Artisan;

class ImportActionsWidget extends Widget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    protected string $view = 'filament.widgets.import-actions';

    protected int | string | array $columnSpan = 'full';

    public function importGen1Action(): Action
    {
        return Action::make('import_gen1')
            ->label('Import Gen 1 (151 Pokemon)')
            ->icon('heroicon-m-arrow-down-tray')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Import Generation 1 Pokemon')
            ->modalDescription('This will import the first 151 Pokemon (Kanto region) including all related data: species, types, abilities, moves, items, and evolution chains. This process may take several minutes.')
            ->modalSubmitActionLabel('Start Import')
            ->action(function () {
                $this->importPokemon(151);
            });
    }

    public function importGen2Action(): Action
    {
        return Action::make('import_gen2')
            ->label('Import Gen 1-2 (251 Pokemon)')
            ->icon('heroicon-m-arrow-down-tray')
            ->color('info')
            ->disabled()
            ->tooltip('Coming soon');
    }

    public function importAllAction(): Action
    {
        return Action::make('import_all')
            ->label('Import All Pokemon')
            ->icon('heroicon-m-arrow-down-tray')
            ->color('warning')
            ->disabled()
            ->tooltip('Coming soon - Use CLI for large imports');
    }

    public function importPokemon(int $max = 151): void
    {
        try {
            Notification::make()
                ->title('Import started')
                ->info()
                ->body("Importing up to {$max} Pokemon from PokeAPI...")
                ->send();

            // TODO(human): Implement async import logic
            // For now, this will run synchronously which may cause timeouts
            Artisan::call('pokemon:import', [
                '--max' => $max,
            ]);

            Notification::make()
                ->title('Import completed')
                ->success()
                ->body('Successfully imported Pokemon data from PokeAPI.')
                ->send();

            // Refresh the page to update stats
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Import failed')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }
}
