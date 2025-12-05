<?php

namespace App\Filament\Resources\Pokemon\Pages;

use App\Filament\Resources\Pokemon\PokemonResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPokemon extends ViewRecord
{
    protected static string $resource = PokemonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
