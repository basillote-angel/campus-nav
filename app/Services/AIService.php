<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Support\Facades\Http;

class AIService
{
  protected $baseUrl;
  protected $apiKey;
  protected $topK;
  protected $threshold;

  public function __construct() {
    $this->baseUrl = config('services.navistfind_ai.base_url', config('services.ai_service.base_url'));
    $this->apiKey = config('services.ai_service.api_key');
    $this->topK = config('services.navistfind_ai.top_k', 10);
    $this->threshold = config('services.navistfind_ai.threshold', 0.6);
  }

  /**
   * @param  Item   $referenceItem
   * @param  Item[] $candidateItems
   * @return Item[]
   */
  public function matchLostAndFound(Item $referenceItem, array $candidateItems): array
  {
    $response = Http::withHeaders([
      'Authorization' => 'Bearer ' . $this->apiKey,
      'Content-Type' => 'application/json',
    ])->post($this->baseUrl . '/v1/match-items', [
      'reference_item' => $referenceItem,
      'candidate_items' => $candidateItems,
      'top_k' => $this->topK,
      'threshold' => $this->threshold,
    ]);

    if ($response->successful()) {
      $data = $response->json();
      $matchedItems = [];

      if($data['matched_items'] && count($data['matched_items']) > 0) {
        foreach ($data['matched_items'] as $item) {
          $matchedItems[] = [
            'item' => Item::with(['owner', 'finder'])->find($item['id']),
            'score' => $item['score'] ?? null,
          ];
        }
      }

      return $matchedItems;
    }

    throw new \Exception('AI Service error: ' . $response->body());
  }

  /**
   * @param  Item   $referenceItem
   * @param  Item[] $candidateItems
   * @return array{highest_best: ?Item, lower_best: ?Item}
   */
  public function matchBestLostAndFound(Item $referenceItem, array $candidateItems): array
  {
    $response = Http::withHeaders([
      'Authorization' => 'Bearer ' . $this->apiKey,
      'Content-Type' => 'application/json',
    ])->post($this->baseUrl . '/v1/match-items/best', [
      'reference_item' => $referenceItem,
      'candidate_items' => $candidateItems,
      'top_k' => $this->topK,
      'threshold' => $this->threshold,
    ]);

    if ($response->successful()) {
      $data = $response->json();

      $highestBest = $data['highest_best'] ?? null;
      $lowerBest = $data['lower_best'] ?? null;

      $highestBestItem = $highestBest ? [
        'item' => Item::find($highestBest['id']),
        'score' => $highestBest['score'] ?? null,
      ] : null;

      $lowerBestItem = $lowerBest ? [
        'item' => Item::find($lowerBest['id']),
        'score' => $lowerBest['score'] ?? null,
      ] : null;

      return [
        'highest_best' => $highestBestItem,
        'lower_best' => $lowerBestItem,
      ];
    }

    throw new \Exception('AI Service error: ' . $response->body());
  }
}