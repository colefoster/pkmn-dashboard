<?php

namespace App\Filament\Resources\Moves\Schemas\Components;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;

class MoveDescriptionSection
{
    public static function make(): Section
    {
        return Section::make('Description & Effects')
            ->schema([
                TextEntry::make('short_effect')
                    ->label('Short Effect')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('effect')
                    ->label('Full Effect')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('flavor_text')
                    ->label('Flavor Text')
                    ->placeholder('-')
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->collapsed(false);
    }
}
