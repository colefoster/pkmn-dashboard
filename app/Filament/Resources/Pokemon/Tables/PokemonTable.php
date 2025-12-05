<?php

namespace App\Filament\Resources\Pokemon\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PokemonTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('api_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name')
                    ->formatStateUsing(fn ($state) => str_replace(" ", "-", (ucwords(str_replace("-", " ", $state)))))
                    ->searchable(),
                ImageColumn::make('sprite_front_default')
                    ->imageSize("20"),

                TextColumn::make('height')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state / 10, 1) . " m")
                    ->sortable(),
                TextColumn::make('weight')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state / 10, 1). " kg")
                    ->sortable(),
                TextColumn::make('base_experience')
                    ->label("Base XP")
                    ->numeric()
                    ->formatStateUsing(fn ($state) => ($state . " points"))
                    ->sortable(),
//                IconColumn::make('is_default')
//                    ->boolean(),
//                TextColumn::make('species.name')
//                    ->searchable(),
//                TextColumn::make('sprite_front_shiny')
//                    ->searchable(),
//                TextColumn::make('sprite_back_default')
//                    ->searchable(),
//                TextColumn::make('sprite_back_shiny')
//                    ->searchable(),
//                TextColumn::make('cry_latest')
//                    ->searchable(),
//                TextColumn::make('cry_legacy')
//                    ->searchable(),
//                TextColumn::make('created_at')
//                    ->dateTime()
//                    ->sortable()
//                    ->toggleable(isToggledHiddenByDefault: true),
//                TextColumn::make('updated_at')
//                    ->dateTime()
//                    ->sortable()
//                    ->toggleable(isToggledHiddenByDefault: true),
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
