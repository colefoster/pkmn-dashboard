<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DatabaseStatsOverview;
use App\Filament\Widgets\PokeApiImportWidget;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Artisan;

class Dashboard extends BaseDashboard
{
    protected function getHeaderActions(): array
    {
        return [

            ];
    }

    public function getWidgets(): array
    {
        return [
            DatabaseStatsOverview::class,
            PokeApiImportWidget::class,
        ];
    }
}
