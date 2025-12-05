<?php

namespace App\Filament\Resources\Pokemon\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
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
                    ->formatStateUsing(fn ($state) => number_format($state / 10, 1). " m")
                    ->placeholder('-'),
                TextEntry::make('weight')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state / 10, 1). " kg")
                    ->placeholder('-'),
                TextEntry::make('base_experience')
                    ->numeric()
                    ->placeholder('-'),

                TextEntry::make('species.name')
                    ->label('Species')
                    ->placeholder('-'),
                ImageEntry::make('sprite_front_default')
                    ->placeholder('-'),
                ImageEntry::make('sprite_front_shiny')
                    ->placeholder('-'),
                ImageEntry::make('sprite_back_default')
                    ->placeholder('-'),
                ImageEntry::make('sprite_back_shiny')
                    ->placeholder('-'),
                TextEntry::make('cry_latest')
                    ->placeholder('-'),
                TextEntry::make('cry_legacy')
                    ->placeholder('-'),
                IconEntry::make('is_default')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
