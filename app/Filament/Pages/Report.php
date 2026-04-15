<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\CashFlow;
use Illuminate\Support\Facades\DB;
use App\Filament\Widgets\IncomeExpenseChart;
use App\Filament\Widgets\NewCustomersStats;
use App\Filament\Widgets\LatestCashFlows;
use App\Filament\Widgets\FinancialSummary;
use App\Filament\Widgets\ExportReportButton;
use Filament\Pages\Actions;
use App\Http\Controllers\ReportController;

class Report extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Report';

    protected static ?string $navigationGroup = 'Finance';

    protected static string $view = 'filament.pages.report';

    protected function getHeaderWidgets(): array
    {
        return [
            FinancialSummary::make([
                'span' => 3
            ]),
            NewCustomersStats::make([
                'span' => 2
            ]),
            IncomeExpenseChart::make([
                'span' => 1
            ]),
            LatestCashFlows::make([
                'span' => 3
            ]),
        ];
    }

    public function getHeaderWidgetsColumns(): int
    {
        return 3;
    }

    protected function getActions(): array
    {
        return [
            Actions\Action::make('exportExcel')
                ->label('Export to Excel')
                ->color('success')
                ->url(action([ReportController::class, 'exportToExcel'])),

            Actions\Action::make('exportPdf')
                ->label('Export to PDF')
                ->color('danger')
                ->url(action([ReportController::class, 'exportToPdf'])),
        ];
    }
}
