<?php

namespace App\Exports;

use App\Models\LostItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
class LostItemsExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $items;

    public function __construct($items)
    {
        $this->items = $items;
    }

    public function collection()
    {
        // Ensure we return a collection
        if (is_array($this->items)) {
            return collect($this->items);
        }
        // If it's already a collection, return as is
        return $this->items instanceof \Illuminate\Support\Collection 
            ? $this->items 
            : collect($this->items);
    }

    public function title(): string
    {
        return 'Lost Items';
    }

    public function headings(): array
    {
        return [
            'ID',
            'Title',
            'Category',
            'Description',
            'Location',
            'Date Lost',
            'Status',
            'Posted By',
            'Posted By Email',
            'Match Count',
            'Best Match Score',
            'Created At',
            'Updated At',
        ];
    }

    public function map($item): array
    {
        // Load relationships if not already loaded
        if (!isset($item['match_count'])) {
            $lostItem = LostItem::with(['category', 'user', 'matches'])->find($item['id']);
            
            $matchCount = $lostItem ? $lostItem->matches()->count() : 0;
            $bestMatchScore = $lostItem && $lostItem->matches()->count() > 0
                ? $lostItem->matches()->max('similarity_score')
                : null;
        } else {
            $matchCount = $item['match_count'] ?? 0;
            $bestMatchScore = $item['best_match_score'] ?? null;
        }

        return [
            $item['id'],
            $item['name'] ?? $item['title'] ?? '',
            $item['category'] ?? 'N/A',
            $item['description'] ?? '',
            $item['location'] ?? '',
            $item['date'] ?? $item['date_lost'] ?? '',
            $item['status'] ?? '',
            $item['posted_by'] ?? $item['user_name'] ?? 'System',
            $item['posted_by_email'] ?? $item['user_email'] ?? '',
            $matchCount,
            $bestMatchScore ? number_format($bestMatchScore, 2) : 'N/A',
            $item['created_at'] ?? '',
            $item['updated_at'] ?? '',
        ];
    }

}

