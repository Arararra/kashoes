<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\OrderReceiptController;

Route::redirect('/', '/admin');

Route::middleware(['auth'])->group(function () {
    Route::get('/reports/export/excel', [ReportController::class, 'exportToExcel'])
        ->name('reports.export.excel');

    Route::get('/reports/export/pdf', [ReportController::class, 'exportToPdf'])
        ->name('reports.export.pdf');

    Route::get('/orders/{order}/receipt', [OrderReceiptController::class, 'show'])
        ->name('orders.receipt');
});