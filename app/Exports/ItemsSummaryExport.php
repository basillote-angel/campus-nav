<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
class ItemsSummaryExport implements FromArray, WithHeadings, WithTitle
{
    protected $summary;

    public function __construct($summary)
    {
        $this->summary = $summary;
    }

    public function array(): array
    {
        return [
            ['Total Lost Items', $this->summary['total_lost'] ?? 0],
            ['Total Found Items', $this->summary['total_found'] ?? 0],
            ['Resolved Lost Items', $this->summary['resolved_lost'] ?? 0],
            ['Unclaimed Found Items', $this->summary['unclaimed_found'] ?? 0],
            ['Pending Claims', $this->summary['pending_claims'] ?? 0],
            ['Approved Claims', $this->summary['approved_claims'] ?? 0],
            ['Collected Items', $this->summary['collected_items'] ?? 0],
            ['Total Matches', $this->summary['total_matches'] ?? 0],
            ['Export Date', now()->format('Y-m-d H:i:s')],
        ];
    }

    public function headings(): array
    {
        return ['Metric', 'Value'];
    }

    public function title(): string
    {
        return 'Summary';
    }

}

