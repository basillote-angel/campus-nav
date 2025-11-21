<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ItemsMultiSheetExport implements WithMultipleSheets
{
    protected $lostItems;
    protected $foundItems;
    protected $summary;

    public function __construct($lostItems, $foundItems, $summary)
    {
        $this->lostItems = $lostItems;
        $this->foundItems = $foundItems;
        $this->summary = $summary;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        if ($this->lostItems->count() > 0) {
            $sheets[] = new LostItemsExport($this->lostItems);
        }
        
        if ($this->foundItems->count() > 0) {
            $sheets[] = new FoundItemsExport($this->foundItems);
        }
        
        $sheets[] = new ItemsSummaryExport($this->summary);
        
        return $sheets;
    }
}

