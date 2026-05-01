<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { margin-bottom: 0.5rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
        .summary { margin-bottom: 1rem; }
        .summary div { margin-bottom: 0.25rem; }
    </style>
</head>
<body>
    <h1>Laporan Keuangan</h1>
    <div class="summary">
        <div>Filter: {{ ucfirst($filter) }}</div>
        @if($start_date && $end_date)
            <div>Periode: {{ $start_date }} s.d. {{ $end_date }}</div>
        @endif
        <div>Pendapatan: Rp {{ number_format($income, 2, ',', '.') }}</div>
        <div>Pengeluaran: Rp {{ number_format($expenses, 2, ',', '.') }}</div>
        <div>Saldo Bersih: Rp {{ number_format($netFlow, 2, ',', '.') }}</div>
        <div>Dibuat pada: {{ $date_generated ?? now()->format('Y-m-d H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Judul</th>
                <th>Deskripsi</th>
                <th>Dibuat oleh</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $detail)
                <tr>
                    <td>{{ $detail['date'] }}</td>
                    <td>{{ $detail['type'] }}</td>
                    <td>{{ $detail['title'] }}</td>
                    <td>{{ $detail['description'] }}</td>
                    <td>{{ $detail['created_by'] }}</td>
                    <td>Rp {{ number_format($detail['amount'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
