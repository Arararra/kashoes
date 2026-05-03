<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportExport implements WithMultipleSheets
{
    public function __construct(protected array $data) {}

    public function sheets(): array
    {
        return [
            'Kas'      => new CashFlowSheet($this->data),
            'Services' => new ServiceSalesSheet($this->data),
        ];
    }
}

class CashFlowSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(protected array $data) {}

    public function title(): string { return 'Kas'; }

    public function collection(): Collection
    {
        return collect($this->data['details']);
    }

    public function headings(): array
    {
        return ['Tanggal', 'Jenis', 'Keterangan', 'Deskripsi', 'Dibuat Oleh', 'Jumlah (Rp)'];
    }
}

class ServiceSalesSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(protected array $data) {}

    public function title(): string { return 'Penjualan Service'; }

    public function collection(): Collection
    {
        return collect($this->data['serviceSales'])->map(fn ($s) => [
            'name'     => $s['name'],
            'quantity' => $s['quantity'],
            'revenue'  => $s['revenue'],
        ]);
    }

    public function headings(): array
    {
        return ['Produk / Service', 'Jumlah Terjual', 'Total Pendapatan (Rp)'];
    }
}
