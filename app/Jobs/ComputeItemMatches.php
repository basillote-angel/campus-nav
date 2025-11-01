<?php

namespace App\Jobs;

use App\Models\FoundItem;
use App\Models\ItemMatch;
use App\Models\LostItem;
use App\Jobs\SendNotificationJob;
use App\Services\AIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeItemMatches implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $referenceType; // 'found' | 'lost'
    protected int $referenceId;

    public function __construct(string $referenceType, int $referenceId)
    {
        $this->referenceType = $referenceType;
        $this->referenceId = $referenceId;
    }

    public function handle(AIService $aiService): void
    {
        $threshold = (float) config('services.navistfind_ai.threshold', 0.6);
        $limit = (int) env('AI_CANDIDATE_LIMIT', 200);

        if ($this->referenceType === 'found') {
            $reference = FoundItem::query()->find($this->referenceId);
            if (!$reference) { return; }
            $candidates = LostItem::where('status', 'open')
                ->latest('created_at')
                ->limit($limit)
                ->get();

            if ($candidates->isEmpty()) { return; }

            $matches = $aiService->matchLostAndFound($reference, $candidates->all(), LostItem::class);
            foreach ($matches as $m) {
                $item = $m['item'] ?? null;
                $score = (float) ($m['score'] ?? 0);
                if (!$item || $score < $threshold) { continue; }

                // Check if match already exists
                $existingMatch = ItemMatch::where('lost_id', $item->id)
                    ->where('found_id', $reference->id)
                    ->first();

                $match = ItemMatch::updateOrCreate(
                    [ 'lost_id' => $item->id, 'found_id' => $reference->id ],
                    [ 'similarity_score' => $score, 'status' => 'pending' ]
                );

                // Send notification only for NEW matches
                if (!$existingMatch && $item->user_id) {
                    $scorePercent = number_format($score * 100, 1);
                    SendNotificationJob::dispatch(
                        $item->user_id, // Notify owner of the lost item
                        'Potential Match Found! 🎯',
                        "A found item matches your lost item '{$item->title}' ({$scorePercent}% match). Check it out!",
                        'matchFound',
                        $reference->id, // Related item ID (the found item)
                        $scorePercent
                    );
                }
            }
            return;
        }

        // referenceType === 'lost'
        $reference = LostItem::query()->find($this->referenceId);
        if (!$reference) { return; }
        $candidates = FoundItem::where('status', 'unclaimed')
            ->latest('created_at')
            ->limit($limit)
            ->get();

        if ($candidates->isEmpty()) { return; }

        $matches = $aiService->matchLostAndFound($reference, $candidates->all(), FoundItem::class);
        foreach ($matches as $m) {
            $item = $m['item'] ?? null;
            $score = (float) ($m['score'] ?? 0);
            if (!$item || $score < $threshold) { continue; }

            // Check if match already exists
            $existingMatch = ItemMatch::where('lost_id', $reference->id)
                ->where('found_id', $item->id)
                ->first();

            $match = ItemMatch::updateOrCreate(
                [ 'lost_id' => $reference->id, 'found_id' => $item->id ],
                [ 'similarity_score' => $score, 'status' => 'pending' ]
            );

            // Send notification only for NEW matches
            if (!$existingMatch && $item->user_id) {
                $scorePercent = number_format($score * 100, 1);
                SendNotificationJob::dispatch(
                    $item->user_id, // Notify owner of the found item
                    'Potential Match Found! 🎯',
                    "A lost item matches your found item '{$item->title}' ({$scorePercent}% match). Check it out!",
                    'matchFound',
                    $reference->id, // Related item ID (the lost item)
                    $scorePercent
                );
            }
        }
    }
}


