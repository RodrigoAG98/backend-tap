<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GeneralExport implements FromView, ShouldAutoSize, WithStyles
{
    
    public function __construct(private array $headers,private array $items){}

    public function view(): View
    {
        return view('export', [
            'headers' => $this->headers,
            'items' => $this->items,
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
