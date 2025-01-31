<?php

namespace App\Providers;

use Filament\Forms;
use Filament\Tables;
use Filament\Navigation;
use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;
use Filament\Navigation\NavigationGroup;
class FilamentServiceProvider extends ServiceProvider
{
    public function boot()
{
    Filament::registerNavigation(function ($builder) {
        $builder->group('Gestión', [
            NavigationGroup::make('Recursos')
                ->icon('heroicon-o-cog')
                ->items([
                    \App\Filament\Resources\ProductoResource::getNavigation(),
                    \App\Filament\Resources\BancoResource::getNavigation(),
                    \App\Filament\Resources\LineaResource::getNavigation(),
                    \App\Filament\Resources\TipoTasaResource::getNavigation(),
                    \App\Filament\Resources\EmpresaResource::getNavigation(),
                ]),
        ]);
    });
}

    public function register()
    {
        // Puedes registrar servicios aquí si es necesario
    }
}