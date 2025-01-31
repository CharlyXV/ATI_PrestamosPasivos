<?php
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PrestamosController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ReportPayController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// Ruta corregida (agrega el parámetro {prestamo} a la URL)
Route::get('/loan/{prestamo}', [ReportPayController::class, 'generateReport'])->name('pay.report');
// ruta para traduccion
Route::get('change-language/{lang}', [LanguageController::class, 'changeLanguage'])->name('change.language');
