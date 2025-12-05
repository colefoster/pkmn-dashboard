<?php

namespace App\Filament\Resources\Moves\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MoveInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('api_id')
                    ->numeric(),
                TextEntry::make('name'),
                TextEntry::make('power')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('pp')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('accuracy')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('priority')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('type.name')
                    ->label('Type')
                    ->placeholder('-'),
                TextEntry::make('damage_class')
                    ->placeholder('-'),
                TextEntry::make('effect_chance')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('contest_type')
                    ->placeholder('-'),
                TextEntry::make('generation')
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
                TextEntry::make('target')
                    ->placeholder('-'),
                TextEntry::make('ailment')
                    ->placeholder('-'),
                TextEntry::make('meta_category')
                    ->placeholder('-'),
                TextEntry::make('min_hits')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('max_hits')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('min_turns')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('max_turns')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('drain')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('healing')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('crit_rate')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('ailment_chance')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('flinch_chance')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('stat_chance')
                    ->numeric()
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
