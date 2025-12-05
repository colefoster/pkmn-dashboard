<?php

namespace App\Filament\Resources\Pokemon\Pages;

use App\Filament\Resources\Pokemon\PokemonResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPokemon extends ListRecords
{
    protected static string $resource = PokemonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
