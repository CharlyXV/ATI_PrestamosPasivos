<?php
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PrestamosController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ReportPayController;
use Illuminate\Support\Facades\Route;


$routes = function () {
    Route::get('/', function () {
        return view('welcome');
    });

// Ruta corregida (agrega el parámetro {prestamo} a la URL)
Route::get('/loan/{prestamo}', [ReportPayController::class, 'generateReport'])->name('pay.report');
};
Route::middleware('custom.throttle')->group(function () {
    // Rutas que requieren limitación de tasa
});

Route::get('change-language/{lang}', [LanguageController::class, 'changeLanguage'])
    ->name('change.language');

Route::group(['prefix' => ''], $routes);
Route::group(['prefix' => 'ProyectoLaravel'], $routes);
