<?php

namespace App\Filament\Widgets;

use App\Models\Ability;
use App\Models\EvolutionChain;
use App\Models\Item;
use App\Models\Move;
use App\Models\Pokemon;
use App\Models\PokemonSpecies;
use App\Models\Type;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DatabaseStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pokemon', Pokemon::count())
                ->description('Total Pokemon imported')
                ->url('/pokemon/')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('success')
                ->chart($this->getWeeklyTrend(Pokemon::class)),

            Stat::make('Species', PokemonSpecies::count())
                ->url('/pokemon/')

                ->description('Pokemon species')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),

            Stat::make('Types', Type::count())
                ->description('Pokemon types')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('warning'),

            Stat::make('Abilities', Ability::count())
                ->description('Unique abilities')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('primary'),

            Stat::make('Moves', Move::count())
                ->description('Unique moves')
                ->url('/moves/')
                ->descriptionIcon('heroicon-m-fire')
                ->color('danger'),

            Stat::make('Items', Item::count())
                ->description('Unique items')
                ->descriptionIcon('heroicon-m-gift')
                ->color('gray'),

            Stat::make('Evolution Chains', EvolutionChain::count())
                ->description('Evolution chains')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];
    }

    /**
     * Get a simple trend chart for the last 7 days
     */
    private function getWeeklyTrend(string $model): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $data[] = $model::where('created_at', '<', $date->copy()->addDay())->count();
        }
        return $data;
    }
}
