<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/reports/export/excel', [ReportController::class, 'exportToExcel'])
        ->name('reports.export.excel');

    Route::get('/reports/export/pdf', [ReportController::class, 'exportToPdf'])
        ->name('reports.export.pdf');
});