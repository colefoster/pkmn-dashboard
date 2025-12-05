<?php

namespace App\Filament\Resources\Abilities\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AbilityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('api_id')
                    ->numeric(),
                TextEntry::make('name'),
                TextEntry::make('effect')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('short_effect')
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('is_main_series')
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
