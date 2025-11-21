<?php

namespace App\Exports;

use App\Models\FoundItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
class FoundItemsExport implements FromCollection, WithHeadings, WithMapping, WithTitle
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
        return 'Found Items';
    }

    public function headings(): array
    {
        return [
            'ID',
            'Title',
            'Category',
            'Description',
            'Location',
            'Date Found',
            'Status',
            'Posted By',
            'Posted By Email',
            'Claim Status',
            'Claimed By',
            'Claimed At',
            'Approved By',
            'Approved At',
            'Collection Deadline',
            'Collected At',
            'Collection Notes',
            'Match Count',
            'Created At',
            'Updated At',
        ];
    }

    public function map($item): array
    {
        // Load relationships if not already loaded
        if (!isset($item['claim_status'])) {
            $foundItem = FoundItem::with([
                'category', 
                'user', 
                'matches',
                'claimedBy',
                'approvedBy',
                'collectedBy',
                'claims'
            ])->find($item['id']);
            
            $claimCount = $foundItem ? $foundItem->claims()->count() : 0;
            $pendingClaims = $foundItem ? $foundItem->pendingClaims()->count() : 0;
            $claimStatus = $foundItem ? $foundItem->status->value : ($item['status'] ?? '');
            $claimedBy = $foundItem && $foundItem->claimedBy ? $foundItem->claimedBy->name : null;
            $claimedAt = $foundItem && $foundItem->claimed_at ? $foundItem->claimed_at->format('Y-m-d H:i:s') : null;
            $approvedBy = $foundItem && $foundItem->approvedBy ? $foundItem->approvedBy->name : null;
            $approvedAt = $foundItem && $foundItem->approved_at ? $foundItem->approved_at->format('Y-m-d H:i:s') : null;
            $collectionDeadline = $foundItem && $foundItem->collection_deadline ? $foundItem->collection_deadline->format('Y-m-d H:i:s') : null;
            $collectedAt = $foundItem && $foundItem->collected_at ? $foundItem->collected_at->format('Y-m-d H:i:s') : null;
            $collectionNotes = $foundItem ? $foundItem->collection_notes : null;
            $matchCount = $foundItem ? $foundItem->matches()->count() : 0;
        } else {
            $claimStatus = $item['claim_status'] ?? $item['status'] ?? '';
            $claimedBy = $item['claimed_by'] ?? null;
            $claimedAt = $item['claimed_at'] ?? null;
            $approvedBy = $item['approved_by'] ?? null;
            $approvedAt = $item['approved_at'] ?? null;
            $collectionDeadline = $item['collection_deadline'] ?? null;
            $collectedAt = $item['collected_at'] ?? null;
            $collectionNotes = $item['collection_notes'] ?? null;
            $matchCount = $item['match_count'] ?? 0;
        }

        return [
            $item['id'],
            $item['name'] ?? $item['title'] ?? '',
            $item['category'] ?? 'N/A',
            $item['description'] ?? '',
            $item['location'] ?? '',
            $item['date'] ?? $item['date_found'] ?? '',
            $item['status'] ?? '',
            $item['posted_by'] ?? $item['user_name'] ?? 'System',
            $item['posted_by_email'] ?? $item['user_email'] ?? '',
            $claimStatus,
            $claimedBy ?? 'N/A',
            $claimedAt ?? 'N/A',
            $approvedBy ?? 'N/A',
            $approvedAt ?? 'N/A',
            $collectionDeadline ?? 'N/A',
            $collectedAt ?? 'N/A',
            $collectionNotes ?? 'N/A',
            $matchCount,
            $item['created_at'] ?? '',
            $item['updated_at'] ?? '',
        ];
    }

}

