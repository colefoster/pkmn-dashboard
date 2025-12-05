<?php

namespace App\Filament\Resources\Items\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ItemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('api_id')
                    ->numeric(),
                TextEntry::make('name'),
                TextEntry::make('cost')
                    ->money()
                    ->placeholder('-'),
                TextEntry::make('fling_power')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('fling_effect')
                    ->placeholder('-'),
                TextEntry::make('category')
                    ->placeholder('-'),
                TextEntry::make('effect')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('short_effect')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('flavor_text')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('sprite')
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
