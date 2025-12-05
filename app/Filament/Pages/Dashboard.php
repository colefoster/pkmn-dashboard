<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Artisan;

class Dashboard extends BaseDashboard
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('import_pokemon')
                ->label('Import Pokemon')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Import Pokemon Data')
                ->modalDescription('This will import Pokemon data from PokeAPI. This may take several minutes.')
                ->modalSubmitActionLabel('Start Import')
                ->action(function () {
                    try {
                        // Run the import command with a max limit to prevent long execution
                        Artisan::call('pokemon:import', [
                            '--max' => 151, // Import first 151 Pokemon
                        ]);

                        Notification::make()
                            ->title('Pokemon import completed')
                            ->success()
                            ->body('Successfully imported Pokemon data from PokeAPI.')
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import failed')
                            ->danger()
                            ->body($e->getMessage())
                            ->send();
                    }
                }),
        ];
    }
}
