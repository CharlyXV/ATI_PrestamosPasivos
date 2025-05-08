<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Pages\Auth\Login;
use App\Http\Middleware\CustomThrottle;
use Filament\Navigation\NavigationItem;
use App\Filament\Resources\PagoResource;
use App\Filament\Resources\PrestamosResource;
use App\Filament\Resources\AplicarReciboResource;
use App\Filament\Resources\AnularReciboResource;
use App\Filament\Resources\ReciboResource;
use App\Filament\Resources\GestionContable\ConsultaAsientoResource;
use App\Filament\Resources\ReportesGerenciales\ReporteDisponibilidadResource;
use App\Filament\Resources\ReportesGerenciales\ReporteGastoResource;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {

        return $panel
            ->default()
            ->login(Login::class) // Usa la clase personalizada de Login
            ->colors([
                'primary' => Color::Blue,
            ])
            ->id('admin') // Identificador del panel
            ->path('admin') // Ruta base del panel (http://localhost:160/admin)
            ->brandName('Sistema de Préstamos')
            ->favicon(asset('images/atiicon.ico'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->navigationGroups([
                'Gestión Préstamo',
                'Configuración',
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class, // Página de inicio del panel
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
        ->widgets([
            \App\Filament\Widgets\PrestamoEstadoOverview::class,
            \App\Filament\Widgets\Pchart::class,
            \App\Filament\Widgets\PrestamoMontoChart::class,
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
                CustomThrottle::class, // Middleware personalizado para límite de tasa
            ])
            ->authMiddleware([
                Authenticate::class, // Middleware de autenticación
            ])

            ->navigationGroups([
                \Filament\Navigation\NavigationGroup::make('Gestión Préstamo')
                    ->icon('heroicon-o-currency-dollar')
                    ->collapsible(false),
                'Configuración',
            ])
            ->navigationItems([
                NavigationItem::make('Crear Préstamo')
                    ->url(fn(): string => PrestamosResource::getUrl('create'))
                    ->icon('heroicon-o-plus-circle')
                    ->group('Gestión Préstamo')
                    ->sort(1)
                    ->activeIcon('heroicon-s-plus-circle'),

                NavigationItem::make('Plan de Pagos')
                    ->url(fn(): string => PagoResource::getUrl())
                    ->icon('heroicon-o-table-cells')
                    ->group('Gestión Préstamo')
                    ->sort(2)
                    ->activeIcon('heroicon-s-table-cells'),

                NavigationItem::make('Consultar Préstamos')
                    ->url(fn(): string => PrestamosResource::getUrl())
                    ->icon('heroicon-o-clipboard-document-list')
                    ->group('Gestión Préstamo')
                    ->sort(3)
                    ->activeIcon('heroicon-s-clipboard-document-list'),
            ])
            ->resources([
                // Registra los recursos aquí pero no se mostrarán en navegación
                PagoResource::class,
                PrestamosResource::class,
            ])

            ->resources([
                ReciboResource::class,
                AplicarReciboResource::class,
                AnularReciboResource::class

            ])
            ->resources([
                \App\Filament\Resources\PrestamosResource::class,
            ])
            ->navigationItems([
                NavigationItem::make('Crear Recibo')
                    ->url(fn(): string => ReciboResource::getUrl('create'))
                    ->icon('heroicon-o-document-plus')
                    ->group('Gestión de Pagos')
                    ->sort(1)
                    ->activeIcon('heroicon-s-document-plus'),

                NavigationItem::make('Aplicar Recibos')
                    ->url(fn(): string => AplicarReciboResource::getUrl())
                    ->icon('heroicon-o-check-circle')
                    ->group('Gestión de Pagos')
                    ->sort(2)
                    ->activeIcon('heroicon-s-check-circle'),

                NavigationItem::make('Consultar Recibos')
                    ->url(fn(): string => ReciboResource::getUrl())
                    ->icon('heroicon-o-magnifying-glass')
                    ->group('Gestión de Pagos')
                    ->sort(3)
                    ->activeIcon('heroicon-s-magnifying-glass'),

                NavigationItem::make('Anular Recibos')
                    ->url(fn(): string => AnularReciboResource::getUrl())
                    ->icon('heroicon-o-x-circle')
                    ->group('Gestión de Pagos')
                    ->sort(4)
                    ->activeIcon('heroicon-s-x-circle'),
            ]);

    }
}
