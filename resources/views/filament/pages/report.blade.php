{{-- resources/views/filament/pages/report.blade.php --}}
<x-filament-panels::page>

    {{-- ── FILTER BULAN & TAHUN ─────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-3 mb-4">
        <div class="flex items-center gap-2">
            <label class="ks-filter-label">Bulan:</label>
            <select wire:model.live="filterMonth" class="ks-filter-select">
                @foreach(range(1, 12) as $m)
                    <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">
                        {{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-2">
            <label class="ks-filter-label">Tahun:</label>
            <select wire:model.live="filterYear" class="ks-filter-select">
                @foreach(range(now()->year - 3, now()->year) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <span style="font-size:0.875rem; color:var(--ks-text-muted);">
            Periode: <strong style="color:var(--ks-text-secondary);">{{ $monthLabel }}</strong>
        </span>
    </div>

    {{-- ── SUMMARY CARDS ────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        {{-- Pemasukan --}}
        <div class="ks-card-income rounded-xl p-5 text-center"
             style="border:1px solid rgba(74,140,111,0.2); border-radius:var(--ks-radius-xl); box-shadow:var(--ks-shadow-sm);">
            <p class="ks-card-label text-sm font-semibold mb-1">Total Pemasukan</p>
            <p class="ks-card-value text-2xl font-bold">
                Rp {{ number_format($totalIncome, 0, ',', '.') }}
            </p>
        </div>

        {{-- Pengeluaran --}}
        <div class="ks-card-expense rounded-xl p-5 text-center"
             style="border:1px solid rgba(192,57,43,0.2); border-radius:var(--ks-radius-xl); box-shadow:var(--ks-shadow-sm);">
            <p class="ks-card-label text-sm font-semibold mb-1">Total Pengeluaran</p>
            <p class="ks-card-value text-2xl font-bold">
                Rp {{ number_format($totalExpense, 0, ',', '.') }}
            </p>
        </div>

        {{-- Saldo --}}
        <div class="ks-card-balance rounded-xl p-5 text-center"
             style="border:1px solid rgba(184,76,101,0.2); border-radius:var(--ks-radius-xl); box-shadow:var(--ks-shadow-sm);">
            <p class="ks-card-label text-sm font-semibold mb-1">Saldo Akhir</p>
            <p class="text-2xl font-bold {{ $saldoAkhir >= 0 ? 'ks-card-value' : 'ks-card-value-minus' }}">
                @if($saldoAkhir < 0)- @endif
                Rp {{ number_format(abs($saldoAkhir), 0, ',', '.') }}
                @if($saldoAkhir < 0)<span style="font-size:.85rem;opacity:.8;">(Minus)</span>@endif
            </p>
        </div>
    </div>

    {{-- ── CATATAN KAS ──────────────────────────────────────────────── --}}
    <div class="fi-section rounded-xl mb-6"
         style="background:var(--ks-surface); border:1px solid var(--ks-border); border-radius:var(--ks-radius-lg); box-shadow:var(--ks-shadow-sm);">
        <div class="fi-section-header px-6 py-4" style="border-bottom:1px solid var(--ks-border);">
            <h2 class="fi-section-header-heading text-base font-semibold">
                Catatan Pemasukan &amp; Pengeluaran
            </h2>
        </div>
        <div class="fi-section-content px-6 py-4 overflow-x-auto">
            @if($cashFlows->isEmpty())
                <p style="text-align:center; color:var(--ks-text-muted); padding:24px 0; font-size:.875rem;">
                    Belum ada catatan kas bulan ini.
                </p>
            @else
                <table class="w-full" style="font-size:.875rem; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--ks-border);">
                            <th class="fi-ta-header-cell text-left py-2 px-3">Tanggal</th>
                            <th class="fi-ta-header-cell text-left py-2 px-3">Keterangan</th>
                            <th class="fi-ta-header-cell text-left py-2 px-3">Tipe</th>
                            <th class="fi-ta-header-cell text-right py-2 px-3">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cashFlows as $flow)
                        <tr style="border-bottom:1px solid var(--ks-border); transition:background var(--ks-ease-fast);"
                            onmouseover="this.style.background='var(--ks-primary-muted)'"
                            onmouseout="this.style.background='transparent'">
                            <td class="fi-ta-cell py-2.5 px-3" style="white-space:nowrap;">
                                {{ $flow->date->format('d M Y') }}
                            </td>
                            <td class="fi-ta-cell py-2.5 px-3">
                                {{ $flow->title }}
                                @if($flow->description)
                                    <span style="font-size:.75rem; color:var(--ks-text-muted); display:block;">
                                        {{ $flow->description }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3">
                                @if($flow->type === 'income')
                                    <span class="ks-badge-income">Pemasukan</span>
                                @else
                                    <span class="ks-badge-expense">Pengeluaran</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-right font-semibold {{ $flow->type === 'income' ? 'ks-text-income' : 'ks-text-expense' }}">
                                Rp {{ number_format($flow->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- ── PENJUALAN SERVICE ────────────────────────────────────────── --}}
    <div class="fi-section rounded-xl mb-6"
         style="background:var(--ks-surface); border:1px solid var(--ks-border); border-radius:var(--ks-radius-lg); box-shadow:var(--ks-shadow-sm);">
        <div class="fi-section-header px-6 py-4" style="border-bottom:1px solid var(--ks-border);">
            <h2 class="fi-section-header-heading text-base font-semibold">
                Total Penjualan Service
            </h2>
        </div>
        <div class="fi-section-content px-6 py-4 overflow-x-auto">
            @if(empty($serviceSales))
                <p style="text-align:center; color:var(--ks-text-muted); padding:24px 0; font-size:.875rem;">
                    Belum ada penjualan service bulan ini.
                </p>
            @else
                <table class="w-full" style="font-size:.875rem; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--ks-border);">
                            <th class="fi-ta-header-cell text-left py-2 px-3">Produk / Service</th>
                            <th class="fi-ta-header-cell text-center py-2 px-3">Jumlah Terjual</th>
                            <th class="fi-ta-header-cell text-right py-2 px-3">Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($serviceSales as $sale)
                        <tr style="border-bottom:1px solid var(--ks-border); transition:background var(--ks-ease-fast);"
                            onmouseover="this.style.background='var(--ks-primary-muted)'"
                            onmouseout="this.style.background='transparent'">
                            <td class="fi-ta-cell py-2.5 px-3">{{ $sale['name'] }}</td>
                            <td class="fi-ta-cell py-2.5 px-3 text-center">{{ $sale['quantity'] }}</td>
                            <td class="py-2.5 px-3 text-right font-semibold ks-text-income">
                                Rp {{ number_format($sale['revenue'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="border-top:2px solid var(--ks-border); background:var(--ks-surface-alt);">
                            <td class="py-2.5 px-3 font-bold" style="color:var(--ks-text);">Total</td>
                            <td class="py-2.5 px-3 text-center font-bold" style="color:var(--ks-text);">
                                {{ collect($serviceSales)->sum('quantity') }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-bold ks-text-income">
                                Rp {{ number_format(collect($serviceSales)->sum('revenue'), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>
    </div>

    {{-- ── GRAFIK 6 BULAN ───────────────────────────────────────────── --}}
    <div class="fi-section rounded-xl"
         style="background:var(--ks-surface); border:1px solid var(--ks-border); border-radius:var(--ks-radius-lg); box-shadow:var(--ks-shadow-sm);">
        <div class="fi-section-header px-6 py-4" style="border-bottom:1px solid var(--ks-border);">
            <h2 class="fi-section-header-heading text-base font-semibold">
                Grafik Keuangan 6 Bulan Terakhir
            </h2>
        </div>
        <div class="fi-section-content px-6 py-6">
            <div wire:ignore>
                <canvas id="financeChart" style="max-height:320px;"></canvas>
            </div>
        </div>
    </div>

    @assets
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endassets

    @script
    <script>
        (() => {
            const canvas = document.getElementById('financeChart');
            if (!canvas) return;
            if (canvas._chartInstance) { canvas._chartInstance.destroy(); }

            const raw = @json($chartData);

            /* Warna chart pakai design tokens */
            const colorIncome  = '#4a8c6f';            /* --ks-success */
            const colorExpense = '#b84c65';            /* --ks-primary  */
            const bgIncome     = 'rgba(74,140,111,0.15)';
            const bgExpense    = 'rgba(184,76,101,0.15)';

            canvas._chartInstance = new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: raw.map(d => d.label),
                    datasets: [
                        {
                            label: 'Pemasukan',
                            data: raw.map(d => d.income),
                            backgroundColor: bgIncome,
                            borderColor: colorIncome,
                            borderWidth: 2,
                            borderRadius: 6,
                        },
                        {
                            label: 'Pengeluaran',
                            data: raw.map(d => d.expense),
                            backgroundColor: bgExpense,
                            borderColor: colorExpense,
                            borderWidth: 2,
                            borderRadius: 6,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { usePointStyle: true, padding: 16 }
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => ' Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw)
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(226,216,210,0.5)' }, /* --ks-border */
                            ticks: {
                                color: '#a08888', /* --ks-text-muted */
                                callback: val => 'Rp ' + new Intl.NumberFormat('id-ID').format(val)
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#6b5050' } /* --ks-text-secondary */
                        }
                    }
                }
            });
        })();
    </script>
    @endscript

</x-filament-panels::page>