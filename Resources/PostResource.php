<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'txt' => $this->txt,
            'short_text' => \Illuminate\Support\Str::limit($this->txt, 100),

            // User info
            'user_id' => $this->user_id,
            'user_name' => optional($this->user)->name,
            'user_avatar_url' => optional($this->user)->avatar_url,

            // Images
            'images' => $this->images->pluck('path'),
            'image_count' => $this->images->count(),

            // Comments
            'comment_count' => $this->comments->count(),
            'commented_by_user' => auth()->check() ? $this->comments->contains('user_id', auth()->id()) : false,

            // Likes
            'like_count' => $this->likes->count(),
            'liked_by_user' => auth()->check() ? $this->likes->contains('user_id', auth()->id()) : false,

            // Timestamps
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
