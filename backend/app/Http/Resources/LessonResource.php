<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'video_url' => $this->video_url,
            'duration' => $this->duration,
            'is_preview' => (bool) $this->is_preview,
            'order' => $this->order,
            'is_completed' => (bool) ($this->is_completed ?? false),
            'has_quiz' => $this->quiz()->exists(),
        ];
    }
}

