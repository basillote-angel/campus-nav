<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
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
   * @param  Model   $referenceItem
   * @param  Model[] $candidateItems
   * @param  class-string<Model> $candidateModelClass  The model class to hydrate matched items from
   * @return array<int, array{item: Model|null, score: float|null}>
   */
  public function matchLostAndFound(Model $referenceItem, array $candidateItems, string $candidateModelClass): array
  {
    $timeout = (int) env('AI_HTTP_TIMEOUT', 5);
    $retries = (int) env('AI_HTTP_RETRIES', 1);

    $candidateMap = collect($candidateItems)
      ->filter(fn ($model) => $model instanceof Model)
      ->keyBy(fn (Model $model) => $model->getKey());

    $payloadCandidates = $candidateMap
      ->values()
      ->map(fn (Model $model) => $model->only([
        'id',
        'title',
        'description',
        'category_id',
        'location',
        'updated_at',
      ]))
      ->all();

    $response = Http::withHeaders([
      'Authorization' => 'Bearer ' . $this->apiKey,
      'Content-Type' => 'application/json',
    ])->timeout($timeout)->retry($retries, 200)->post($this->baseUrl . '/v1/match-items', [
      'reference_item' => $referenceItem,
      'candidate_items' => $payloadCandidates,
      'top_k' => $this->topK,
      'threshold' => $this->threshold,
    ]);

    if ($response->successful()) {
      $data = $response->json();
      $matchedItems = [];

      if($data['matched_items'] && count($data['matched_items']) > 0) {
        foreach ($data['matched_items'] as $item) {
          $matchedItems[] = [
            'item' => $candidateMap->get($item['id']),
            'score' => $item['score'] ?? null,
          ];
        }
      }

      return $matchedItems;
    }

    throw new \Exception('AI Service error: ' . $response->body());
  }

  /**
   * @param  Model   $referenceItem
   * @param  Model[] $candidateItems
   * @param  class-string<Model> $candidateModelClass
   * @return array{highest_best: ?array{item: Model|null, score: float|null}, lower_best: ?array{item: Model|null, score: float|null}}
   */
  public function matchBestLostAndFound(Model $referenceItem, array $candidateItems, string $candidateModelClass): array
  {
    $timeout = (int) env('AI_HTTP_TIMEOUT', 5);
    $retries = (int) env('AI_HTTP_RETRIES', 1);

    $candidateMap = collect($candidateItems)
      ->filter(fn ($model) => $model instanceof Model)
      ->keyBy(fn (Model $model) => $model->getKey());

    $payloadCandidates = $candidateMap
      ->values()
      ->map(fn (Model $model) => $model->only([
        'id',
        'title',
        'description',
        'category_id',
        'location',
        'updated_at',
      ]))
      ->all();

    $response = Http::withHeaders([
      'Authorization' => 'Bearer ' . $this->apiKey,
      'Content-Type' => 'application/json',
    ])->timeout($timeout)->retry($retries, 200)->post($this->baseUrl . '/v1/match-items/best', [
      'reference_item' => $referenceItem,
      'candidate_items' => $payloadCandidates,
      'top_k' => $this->topK,
      'threshold' => $this->threshold,
    ]);

    if ($response->successful()) {
      $data = $response->json();

      $highestBest = $data['highest_best'] ?? null;
      $lowerBest = $data['lower_best'] ?? null;

      $highestBestItem = $highestBest ? [
        'item' => $candidateMap->get($highestBest['id']),
        'score' => $highestBest['score'] ?? null,
      ] : null;

      $lowerBestItem = $lowerBest ? [
        'item' => $candidateMap->get($lowerBest['id']),
        'score' => $lowerBest['score'] ?? null,
      ] : null;

      return [
        'highest_best' => $highestBestItem,
        'lower_best' => $lowerBestItem,
      ];
    }

    throw new \Exception('AI Service error: ' . $response->body());
  }

  /**
   * Check AI service health endpoint.
   * @return array{ok: bool, service?: array<string, mixed>, error?: string}
   */
  public function health(): array
  {
    $timeout = (int) env('AI_HTTP_TIMEOUT', 5);
    $retries = (int) env('AI_HTTP_RETRIES', 1);

    $request = Http::timeout($timeout)->retry($retries, 200);
    if (!empty($this->apiKey)) {
      $request = $request->withHeaders([
        'Authorization' => 'Bearer ' . $this->apiKey,
      ]);
    }

    try {
      $response = $request->get($this->baseUrl . '/v1/health');
      if ($response->successful()) {
        return [
          'ok' => true,
          'service' => $response->json(),
        ];
      }

      return [
        'ok' => false,
        'error' => 'HTTP ' . $response->status() . ': ' . $response->body(),
      ];
    } catch (\Throwable $e) {
      return [
        'ok' => false,
        'error' => $e->getMessage(),
      ];
    }
  }
}