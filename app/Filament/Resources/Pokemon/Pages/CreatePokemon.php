<?php

namespace App\Filament\Resources\Pokemon\Pages;

use App\Filament\Resources\Pokemon\PokemonResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePokemon extends CreateRecord
{
    protected static string $resource = PokemonResource::class;
}
