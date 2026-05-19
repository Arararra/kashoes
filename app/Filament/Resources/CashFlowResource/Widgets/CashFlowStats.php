<?php

namespace App\Filament\Resources\CashFlowResource\Widgets;

use App\Models\CashFlow;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CashFlowStats extends BaseWidget
{
    protected function getStats(): array
    {
        $startDate = now()->subMonth();

        $incomeCount = CashFlow::where('type', 'income')
            ->where('date', '>=', $startDate)
            ->count();

        $incomeTotal = CashFlow::where('type', 'income')
            ->where('date', '>=', $startDate)
            ->sum('amount');

        $expenseCount = CashFlow::where('type', 'expense')
            ->where('date', '>=', $startDate)
            ->count();

        $expenseTotal = CashFlow::where('type', 'expense')
            ->where('date', '>=', $startDate)
            ->sum('amount');

        return [
            Stat::make('Income (1 Month)', 'Rp ' . number_format($incomeTotal))
                ->description($incomeCount . ' entries')
                ->color('success'),

            Stat::make('Expense (1 Month)', 'Rp ' . number_format($expenseTotal))
                ->description($expenseCount . ' entries')
                ->color('danger'),
            // Simple anomaly detection for the last month
            Stat::make('Anomalies (1 Month)', $this->anomalyCount($startDate) . ' anomalies detected')
                ->description('Click to filter the Cash Flow list to only anomalies.')
                ->url($this->anomaliesUrl($startDate))
                ->color('warning'),
        ];
    }

    protected function anomaliesUrl($startDate): ?string
    {
        $anomalies = $this->detectAnomalies($startDate);

        if ($anomalies->isEmpty()) {
            return null;
        }

        $ids = $anomalies->pluck('id')->implode(',');

        try {
            return route('filament.admin.resources.cash-flows.index', ['anomaly_ids' => $ids]);
        } catch (\Throwable $e) {
            return url('admin/cash-flows?anomaly_ids=' . $ids);
        }
    }

    protected function anomalyCount($startDate): int
    {
        return $this->detectAnomalies($startDate)->count();
    }

    protected function formatAnomalySummary($startDate): string
    {
        $anomalies = $this->detectAnomalies($startDate);

        if ($anomalies->isEmpty()) {
            return 'No anomalies';
        }

        // Show top 1-3 anomalies concisely
        $items = $anomalies->take(3)->map(function ($a) {
            return ($a['type'] === 'expense' ? 'Expense' : 'Income') . ' Rp ' . number_format($a['amount']) . ' on ' . $a['date']->format('Y-m-d');
        })->toArray();

        return Str::limit(implode('; ', $items), 60);
    }

    protected function anomalyDescription($startDate): string
    {
        $anomalies = $this->detectAnomalies($startDate);

        if ($anomalies->isEmpty()) {
            return 'No unusual income or expense detected in the past month.';
        }

        $count = $anomalies->count();
        return $count . ' anomal' . ($count > 1 ? 'ies' : 'y') . ' found';
    }

    /**
     * Detect anomalies in CashFlow records since given start date.
     * Simple method: for each `type` compute mean and stddev and mark
     * entries that are > mean + 3 * stddev as anomalies.
     *
     * @param \Illuminate\Support\Carbon|null $startDate
     * @return \Illuminate\Support\Collection
     */
    protected function detectAnomalies($startDate): Collection
    {
        $records = CashFlow::where('date', '>=', $startDate)->get();

        if ($records->isEmpty()) {
            return collect();
        }

        $anomalies = collect();

        foreach (['income', 'expense'] as $type) {
            $group = $records->where('type', $type)->values();

            if ($group->count() < 2) {
                continue;
            }

            $amounts = $group->pluck('amount')->map(fn($v) => (float) $v)->toArray();
            $mean = array_sum($amounts) / count($amounts);
            $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $amounts)) / count($amounts);
            $std = sqrt($variance);

            // compute median
            sort($amounts, SORT_NUMERIC);
            $mid = (int) floor(count($amounts) / 2);
            if (count($amounts) % 2 === 0) {
                $median = ($amounts[$mid - 1] + $amounts[$mid]) / 2;
            } else {
                $median = $amounts[$mid];
            }

            // robust threshold: use the smaller of mean+3*std and 3*median for greater sensitivity
            $threshold = min($mean + 3 * $std, 3 * $median);

            foreach ($group as $r) {
                // If std is zero (all same amounts) still allow median-based detection
                if ($std <= 0 && $median <= 0) {
                    continue;
                }

                if ($r->amount > $threshold) {
                    $anomalies->push([
                        'id' => $r->id,
                        'type' => $type,
                        'amount' => $r->amount,
                        'date' => $r->date,
                    ]);
                }
            }
        }

        // sort anomalies by amount desc
        return $anomalies->sortByDesc('amount')->values();
    }
}
