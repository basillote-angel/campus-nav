<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'related_id' => $this->related_id ? (int) $this->related_id : null,
            'score' => $this->score !== null ? (string) $this->score : null,
            'created_at' => optional($this->created_at)->toISOString(),
            'read_at' => optional($this->read_at)->toISOString(),
        ];
    }
}


