<?php

namespace App\Filament\Resources\Pokemon\Pages;

use App\Filament\Resources\Pokemon\PokemonResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPokemon extends ViewRecord
{
    protected static string $resource = PokemonResource::class;
    public function getTitle(): string
    {
        if (!empty($this->record->name)) {
            return ucwords($this->record->name);
        }
        return 'View Pokemon';

    }
    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
