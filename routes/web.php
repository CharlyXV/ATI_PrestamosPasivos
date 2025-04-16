<?php
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PrestamosController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ReportPayController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReciboController;

$routes = function () {
    Route::get('/', function () {
        return view('welcome');
    });

// En routes/web.php
Route::get('/report/pay/{prestamoId}', [ReportPayController::class, 'generateReport'])
    ->name('report.pay');
};
Route::middleware('custom.throttle')->group(function () {
    // Rutas que requieren limitación de tasa
});

Route::get('change-language/{lang}', [LanguageController::class, 'changeLanguage'])
    ->name('change.language');

Route::group(['prefix' => ''], $routes);
Route::group(['prefix' => 'ProyectoLaravel'], $routes);

Route::get('/recibos/{recibo}/download', [ReciboController::class, 'download'])
     ->name('recibos.download');



