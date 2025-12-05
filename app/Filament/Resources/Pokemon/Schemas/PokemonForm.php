<?php

namespace App\Filament\Resources\Pokemon\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PokemonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('api_id')
                    ->required()
                    ->numeric(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('height')
                    ->numeric(),
                TextInput::make('weight')
                    ->numeric(),
                TextInput::make('base_experience')
                    ->numeric(),
                Toggle::make('is_default')
                    ->required(),
                Select::make('species_id')
                    ->relationship('species', 'name'),
                TextInput::make('sprite_front_default'),
                TextInput::make('sprite_front_shiny'),
                TextInput::make('sprite_back_default'),
                TextInput::make('sprite_back_shiny'),
                TextInput::make('cry_latest'),
                TextInput::make('cry_legacy'),
            ]);
    }
}
