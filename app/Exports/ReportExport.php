<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportExport implements FromCollection, WithHeadings
{
    public function __construct(protected array $data) {}

    public function collection(): Collection
    {
        return collect($this->data['details']);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Type',
            'Title',
            'Description',
            'Created By',
            'Amount',
        ];
    }
}
