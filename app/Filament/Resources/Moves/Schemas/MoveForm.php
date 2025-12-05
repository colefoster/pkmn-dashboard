<?php

namespace App\Filament\Resources\Moves\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MoveForm
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
                TextInput::make('power')
                    ->numeric(),
                TextInput::make('pp')
                    ->numeric(),
                TextInput::make('accuracy')
                    ->numeric(),
                TextInput::make('priority')
                    ->numeric(),
                Select::make('type_id')
                    ->relationship('type', 'name'),
                TextInput::make('damage_class'),
                TextInput::make('effect_chance')
                    ->numeric(),
                TextInput::make('contest_type'),
                TextInput::make('generation'),
                Textarea::make('effect')
                    ->columnSpanFull(),
                Textarea::make('short_effect')
                    ->columnSpanFull(),
                Textarea::make('flavor_text')
                    ->columnSpanFull(),
                TextInput::make('target'),
                TextInput::make('ailment'),
                TextInput::make('meta_category'),
                TextInput::make('min_hits')
                    ->numeric(),
                TextInput::make('max_hits')
                    ->numeric(),
                TextInput::make('min_turns')
                    ->numeric(),
                TextInput::make('max_turns')
                    ->numeric(),
                TextInput::make('drain')
                    ->numeric(),
                TextInput::make('healing')
                    ->numeric(),
                TextInput::make('crit_rate')
                    ->numeric(),
                TextInput::make('ailment_chance')
                    ->numeric(),
                TextInput::make('flinch_chance')
                    ->numeric(),
                TextInput::make('stat_chance')
                    ->numeric(),
            ]);
    }
}
