<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PkmnDashboardPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('pkmn-dashboard')
            ->path('')
            ->login()
            ->favicon(asset('/images/pokeball.png'))
            ->colors([
                'primary' => Color::Fuchsia,

                'physical' => '#eb5628',
                'special' => '#375ab2',
                'status' => Color::Gray,

                'normal' => Color::Neutral,
                'grass' => '#3fa129',
                'fire' => '#e62829',
                'water' => '#2980ef',
                'electric' => '#fac000',
                'ground' => '#915121',
                'psychic' => '#ee4179',
                'dark' => '#624d4e',
                'flying' => '#80b7ed',
                'rock' => '#afa981',
                'ghost' => '#704170',
                'fairy' => '#ef70ef',
                'poison' => '#9141cb',
                'fighting' => '#ff8000',
                'ice' => '#3dcef3',
                'bug' => '#91a119',
                'dragon' => '#5060e1',
                'steel' => '#60a1b8',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
