<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Session;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();
        app()->setLocale('es');


        if (Session::has('error')) {
            Notification::make()
                ->title('Error del sistema')
                ->body(Session::get('error'))
                ->danger()
                ->persistent()
                ->send();
        }
    }

    

}
