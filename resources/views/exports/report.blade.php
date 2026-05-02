<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan - {{ $monthLabel ?? '' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1a1a1a; margin: 30px; }

        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #1a1a1a; padding-bottom: 12px; }
        .header h1 { font-size: 20px; font-weight: bold; margin-bottom: 4px; }
        .header p  { font-size: 11px; color: #555; }

        h2 { font-size: 13px; font-weight: bold; margin: 20px 0 8px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f0f0f0; font-weight: bold; font-size: 11px; }
        tr:nth-child(even) { background: #fafafa; }

        .badge-income  { color: #16a34a; font-weight: bold; }
        .badge-expense { color: #dc2626; font-weight: bold; }

        .summary-cards { display: table; width: 100%; margin: 16px 0; border-spacing: 8px; }
        .summary-card  { display: table-cell; width: 33.3%; padding: 10px; border-radius: 6px; text-align: center; }
        .card-green { background: #dcfce7; }
        .card-red   { background: #fee2e2; }
        .card-blue  { background: #dbeafe; }
        .card-label { font-size: 11px; font-weight: bold; margin-bottom: 4px; }
        .card-value { font-size: 14px; font-weight: bold; }
        .green-text { color: #16a34a; }
        .red-text   { color: #dc2626; }
        .blue-text  { color: #1d4ed8; }

        .footer { margin-top: 24px; font-size: 10px; color: #888; text-align: right; border-top: 1px solid #ccc; padding-top: 8px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Laporan Keuangan</h1>
        <p>Periode: {{ $monthLabel ?? ($start_date . ' s.d. ' . $end_date) }}</p>
        <p>Dibuat pada: {{ $date_generated ?? now()->format('d M Y H:i') }}</p>
    </div>

    {{-- Summary Cards --}}
    <table style="border:none; margin-bottom:20px;">
        <tr>
            <td style="border:none; width:33%; padding:8px;">
                <div style="background:#dcfce7; padding:10px; border-radius:6px; text-align:center;">
                    <div style="font-size:11px; font-weight:bold; color:#15803d;">Total Pemasukan</div>
                    <div style="font-size:15px; font-weight:bold; color:#16a34a;">Rp {{ number_format($income, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="border:none; width:33%; padding:8px;">
                <div style="background:#fee2e2; padding:10px; border-radius:6px; text-align:center;">
                    <div style="font-size:11px; font-weight:bold; color:#b91c1c;">Total Pengeluaran</div>
                    <div style="font-size:15px; font-weight:bold; color:#dc2626;">Rp {{ number_format($expenses, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="border:none; width:33%; padding:8px;">
                <div style="background:#dbeafe; padding:10px; border-radius:6px; text-align:center;">
                    <div style="font-size:11px; font-weight:bold; color:#1e40af;">Saldo Akhir</div>
                    <div style="font-size:15px; font-weight:bold; color:#1d4ed8;">Rp {{ number_format($netFlow, 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Tabel Service Sales --}}
    @if(!empty($serviceSales))
    <h2>Total Penjualan Service</h2>
    <table>
        <thead>
            <tr>
                <th>Produk / Service</th>
                <th>Jumlah Terjual</th>
                <th>Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($serviceSales as $sale)
            <tr>
                <td>{{ $sale['name'] }}</td>
                <td>{{ $sale['quantity'] }}</td>
                <td class="green-text">Rp {{ number_format($sale['revenue'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Tabel Cash Flow --}}
    <h2>Catatan Pemasukan &amp; Pengeluaran</h2>
    @if(count($details) > 0)
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Keterangan</th>
                <th>Deskripsi</th>
                <th>Dibuat Oleh</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $detail)
            <tr>
                <td>{{ $detail['date'] }}</td>
                <td class="{{ $detail['type'] === 'Pemasukan' ? 'badge-income' : 'badge-expense' }}">
                    {{ $detail['type'] }}
                </td>
                <td>{{ $detail['title'] }}</td>
                <td>{{ $detail['description'] ?? '-' }}</td>
                <td>{{ $detail['created_by'] }}</td>
                <td>Rp {{ number_format($detail['amount'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p style="color:#888; font-style:italic; padding: 8px 0;">Tidak ada data kas pada periode ini.</p>
    @endif

    <div class="footer">
        KaShoes Management System &bull; Laporan digenerate otomatis
    </div>

</body>
</html>
