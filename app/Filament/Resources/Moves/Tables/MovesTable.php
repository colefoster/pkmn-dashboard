<?php

namespace App\Filament\Resources\Moves\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class MovesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('api_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('power')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pp')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('accuracy')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('priority')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('type.name')
                    ->searchable(),
                TextColumn::make('damage_class')
                    ->searchable(),
                TextColumn::make('effect_chance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('contest_type')
                    ->searchable(),
                TextColumn::make('generation')
                    ->searchable(),
                TextColumn::make('target')
                    ->searchable(),
                TextColumn::make('ailment')
                    ->searchable(),
                TextColumn::make('meta_category')
                    ->searchable(),
                TextColumn::make('min_hits')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_hits')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_turns')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_turns')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('drain')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('healing')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('crit_rate')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ailment_chance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('flinch_chance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stat_chance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
