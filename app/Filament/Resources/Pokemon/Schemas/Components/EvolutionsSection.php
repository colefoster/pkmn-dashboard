<?php

namespace App\Filament\Resources\Pokemon\Schemas\Components;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;

class EvolutionsSection
{
    public static function make(): Fieldset
    {

        return FieldSet::make('Evolution Chain')
            ->hidden(fn($record) => !($record->getEvolutionChainData()['has_evolutions'] ?? false))
            ->columns(fn($record) => [
                'default' => $record->getEvolutionChainData()['stage_count'],
                'sm' => $record->getEvolutionChainData()['stage_count'] == 2 ? 3 : 5,
                'md' => $record->getEvolutionChainData()['stage_count'] == 2 ? 3 : 5,
                'lg' => $record->getEvolutionChainData()['stage_count'] == 2 ? 3 : 5,
            ])
            ->schema([
                // Stage 1
                ImageEntry::make('stage_1_sprite')
                    ->aboveContent(fn($record) => strtolower($record->getEvolutionChainData()['stage_1_name']) !== $record->name ? $record->getEvolutionChainData()['stage_1_name'] : null)
                    ->label(fn($record) => $record->getEvolutionChainData()['stage_1_name'])
                    ->hiddenLabel(fn($record) => $record->getEvolutionChainData()['stage_1_name'] !== $record->name)
                    ->state(fn($record) => $record->getEvolutionChainData()['stage_1_sprite'] ?? null)
                    ->extraImgAttributes([
                        'class' => 'pixelated',
                        'style' => 'image-rendering: pixelated; image-rendering: -moz-crisp-edges; image-rendering: crisp-edges;',
                    ])
                    ->defaultImageUrl(url('/images/sprite-placeholder.png')),

                IconEntry::make('arrow_1')
                    ->hiddenLabel()
                    ->icon('heroicon-o-arrow-right')
                    ->state(true)
                    ->hidden(fn($record) => ($record->getEvolutionChainData()['stage_count'] ?? 0) < 2),

                // Stage 2
                ImageEntry::make('stage_2_sprite')
                    ->aboveContent(fn($record) => strtolower($record->getEvolutionChainData()['stage_2_name']) !== $record->name ? $record->getEvolutionChainData()['stage_2_name'] : null)
                    ->label(fn($record) => $record->getEvolutionChainData()['stage_2_name'])
                    ->hiddenLabel(fn($record) => strtolower($record->getEvolutionChainData()['stage_2_name']) !== $record->name)
                    ->state(fn($record) => $record->getEvolutionChainData()['stage_2_sprite'] ?? null)
                    ->hidden(fn($record) => ($record->getEvolutionChainData()['stage_count'] ?? 0) < 2)
                    ->extraImgAttributes([
                        'class' => 'pixelated',
                        'style' => 'image-rendering: pixelated; image-rendering: -moz-crisp-edges; image-rendering: crisp-edges;',
                    ])
                    ->defaultImageUrl(url('/images/sprite-placeholder.png')),

                // Arrow 2->3
                IconEntry::make('arrow_2')
                    ->hiddenLabel()
                    ->icon('heroicon-o-arrow-right')
                    ->state(true)
                    ->alignCenter()
                    ->hidden(fn($record) => ($record->getEvolutionChainData()['stage_count'] ?? 0) < 3),

                // Stage 3
                ImageEntry::make('stage_3_sprite')
                    ->aboveContent(fn($record) => strtolower($record->getEvolutionChainData()['stage_3_name']) !== $record->name ? $record->getEvolutionChainData()['stage_3_name'] : null)
                    ->label(fn($record) => $record->getEvolutionChainData()['stage_3_name'])
                    ->hiddenLabel(fn($record) => strtolower($record->getEvolutionChainData()['stage_3_name']) !== $record->name)
                    ->state(fn($record) => $record->getEvolutionChainData()['stage_3_sprite'] ?? null)
                    ->hidden(fn($record) => ($record->getEvolutionChainData()['stage_count'] ?? 0) < 3)
                    ->extraImgAttributes([
                        'class' => 'pixelated',
                        'style' => 'image-rendering: pixelated; image-rendering: -moz-crisp-edges; image-rendering: crisp-edges;',
                    ])
                    ->defaultImageUrl(url('/images/sprite-placeholder.png')),


                // Evolution methods (commented out for now)
                TextEntry::make('stage_1_method')
                    ->hiddenLabel()
                    ->state(fn($record) => $record->getEvolutionChainData()['stage_1_method'] ?? null)
                    ->hidden(fn($record) => empty($record->getEvolutionChainData()['stage_1_method'] ?? null))
                    ->badge()
                    ->alignCenter()
                    //->columnSpan(["sm" => "2", "md" => "2", "lg" => "2"])
                    ->columnStart(["sm" => "1", "md" => "1", "lg" => "1"])
                    ->color('info'),

                TextEntry::make('stage_2_method')
                    ->hiddenLabel()
                    ->state(fn($record) => $record->getEvolutionChainData()['stage_2_method'] ?? null)
                    ->hidden(fn($record) => empty($record->getEvolutionChainData()['stage_2_method'] ?? null))
                    ->badge()
                    ->columnStart(["sm" => "3", "md" => "3", "lg" => "3"])
                    ->alignCenter()
                    ->color('info'),

            ]);
    }
}
