<?php

namespace App\Filament\Resources\Pokemon\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PokemonInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('api_id')
                    ->numeric(),
                TextEntry::make('name'),
                TextEntry::make('height')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('weight')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('base_experience')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('is_default')
                    ->boolean(),
                TextEntry::make('species.name')
                    ->label('Species')
                    ->placeholder('-'),
                TextEntry::make('sprite_front_default')
                    ->placeholder('-'),
                TextEntry::make('sprite_front_shiny')
                    ->placeholder('-'),
                TextEntry::make('sprite_back_default')
                    ->placeholder('-'),
                TextEntry::make('sprite_back_shiny')
                    ->placeholder('-'),
                TextEntry::make('cry_latest')
                    ->placeholder('-'),
                TextEntry::make('cry_legacy')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
