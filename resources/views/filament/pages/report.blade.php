{{-- resources/views/filament/pages/report.blade.php --}}
<x-filament-panels::page>

    {{-- ================================================================
         FILTER BULAN & TAHUN
    ================================================================ --}}
    <div class="flex flex-wrap items-center gap-3 mb-4">
        <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Bulan:</label>
            <select
                wire:model.live="filterMonth"
                class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm text-gray-800 dark:text-gray-200 px-3 py-1.5 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
                @foreach(range(1, 12) as $m)
                    <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">
                        {{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tahun:</label>
            <select
                wire:model.live="filterYear"
                class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm text-gray-800 dark:text-gray-200 px-3 py-1.5 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
                @foreach(range(now()->year - 3, now()->year) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <span class="text-sm text-gray-500 dark:text-gray-400">
            Menampilkan data: <strong class="text-gray-700 dark:text-gray-200">{{ $monthLabel }}</strong>
        </span>
    </div>

    {{-- ================================================================
         SECTION 1: Summary Cards
    ================================================================ --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="rounded-xl p-5 text-center" style="background-color: #dcfce7;">
            <p class="text-sm font-semibold mb-1" style="color: #15803d;">Total Pemasukan</p>
            <p class="text-2xl font-bold" style="color: #16a34a;">
                Rp {{ number_format($totalIncome, 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl p-5 text-center" style="background-color: #fee2e2;">
            <p class="text-sm font-semibold mb-1" style="color: #b91c1c;">Total Pengeluaran</p>
            <p class="text-2xl font-bold" style="color: #dc2626;">
                Rp {{ number_format($totalExpense, 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl p-5 text-center" style="background-color: #dbeafe;">
            <p class="text-sm font-semibold mb-1" style="color: #1e40af;">Saldo Akhir</p>
            <p class="text-2xl font-bold" style="color: {{ $saldoAkhir >= 0 ? '#1d4ed8' : '#dc2626' }};">
                @if($saldoAkhir < 0)- @endif
                Rp {{ number_format(abs($saldoAkhir), 0, ',', '.') }}
                @if($saldoAkhir < 0)<span style="font-size: 1rem;">(Minus)</span>@endif
            </p>
        </div>
    </div>

    {{-- ================================================================
         SECTION 2: Catatan Pemasukan & Pengeluaran
    ================================================================ --}}
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 mb-6">
        <div class="fi-section-header flex items-center justify-between gap-3 px-6 py-4 border-b border-gray-200 dark:border-white/10">
            <h2 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                Catatan Pemasukan &amp; Pengeluaran
            </h2>
        </div>
        <div class="fi-section-content px-6 py-4 overflow-x-auto">
            @if($cashFlows->isEmpty())
                <p class="text-center text-gray-400 py-6 text-sm">Belum ada catatan kas bulan ini.</p>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-white/10">
                            <th class="text-left py-2 px-3 font-semibold text-gray-600 dark:text-gray-300">Tanggal</th>
                            <th class="text-left py-2 px-3 font-semibold text-gray-600 dark:text-gray-300">Keterangan</th>
                            <th class="text-left py-2 px-3 font-semibold text-gray-600 dark:text-gray-300">Tipe</th>
                            <th class="text-right py-2 px-3 font-semibold text-gray-600 dark:text-gray-300">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                        @foreach($cashFlows as $flow)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                            <td class="py-2.5 px-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                {{ $flow->date->format('d M Y') }}
                            </td>
                            <td class="py-2.5 px-3 text-gray-700 dark:text-gray-300">
                                {{ $flow->title }}
                                @if($flow->description)
                                    <span class="text-xs text-gray-400 block">{{ $flow->description }}</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3">
                                @if($flow->type === 'income')
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-green-100 text-green-700">Pemasukan</span>
                                @else
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-700">Pengeluaran</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-right font-medium {{ $flow->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                Rp {{ number_format($flow->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- ================================================================
         SECTION 3: Total Penjualan Service
    ================================================================ --}}
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 mb-6">
        <div class="fi-section-header px-6 py-4 border-b border-gray-200 dark:border-white/10">
            <h2 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                Total Penjualan Service
            </h2>
        </div>
        <div class="fi-section-content px-6 py-4 overflow-x-auto">
            @if(empty($serviceSales))
                <p class="text-center text-gray-400 py-6 text-sm">Belum ada penjualan service bulan ini.</p>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-white/10">
                            <th class="text-left py-2 px-3 font-semibold text-gray-600 dark:text-gray-300">Produk</th>
                            <th class="text-center py-2 px-3 font-semibold text-gray-600 dark:text-gray-300">Jumlah Terjual</th>
                            <th class="text-right py-2 px-3 font-semibold text-gray-600 dark:text-gray-300">Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                        @foreach($serviceSales as $sale)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                            <td class="py-2.5 px-3 text-gray-700 dark:text-gray-300">{{ $sale['name'] }}</td>
                            <td class="py-2.5 px-3 text-center text-gray-700 dark:text-gray-300">{{ $sale['quantity'] }}</td>
                            <td class="py-2.5 px-3 text-right font-semibold text-green-600">
                                Rp {{ number_format($sale['revenue'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                            <td class="py-2.5 px-3 font-semibold text-gray-700 dark:text-gray-300">Total</td>
                            <td class="py-2.5 px-3 text-center font-semibold text-gray-700 dark:text-gray-300">
                                {{ collect($serviceSales)->sum('quantity') }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-bold text-green-600">
                                Rp {{ number_format(collect($serviceSales)->sum('revenue'), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>
    </div>

    {{-- ================================================================
         SECTION 4: Grafik Keuangan Bulanan (6 bulan)
    ================================================================ --}}
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="fi-section-header px-6 py-4 border-b border-gray-200 dark:border-white/10">
            <h2 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                Grafik Keuangan 6 Bulan Terakhir
            </h2>
        </div>
        <div class="fi-section-content px-6 py-6">
            {{-- wire:ignore agar Livewire tidak hapus canvas saat re-render --}}
            <div wire:ignore>
                <canvas id="financeChart" style="max-height: 320px;"></canvas>
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

            // Hancurkan instance lama jika ada (saat Livewire re-render)
            if (canvas._chartInstance) {
                canvas._chartInstance.destroy();
                canvas._chartInstance = null;
            }

            const raw = @json($chartData);

            canvas._chartInstance = new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: raw.map(d => d.label),
                    datasets: [
                        {
                            label: 'Pemasukan',
                            data: raw.map(d => d.income),
                            backgroundColor: 'rgba(34, 197, 94, 0.7)',
                            borderColor: '#16a34a',
                            borderWidth: 1,
                            borderRadius: 6,
                        },
                        {
                            label: 'Pengeluaran',
                            data: raw.map(d => d.expense),
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderColor: '#dc2626',
                            borderWidth: 1,
                            borderRadius: 6,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: ctx => ' Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw)
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: val => 'Rp ' + new Intl.NumberFormat('id-ID').format(val)
                            }
                        }
                    }
                }
            });
        })();
    </script>
    @endscript

</x-filament-panels::page>