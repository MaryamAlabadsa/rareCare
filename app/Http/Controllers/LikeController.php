<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;

class LikeController extends BaseController
{
    /**
     * Toggle like/unlike for a given post.
     */
    public function toggle(Post $post)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->apiResponse('error', 401, 'Unauthorized');
            }

            $existingLike = $post->likes()->where('user_id', $user->id)->first();

            if ($existingLike) {
                $existingLike->delete();
                return $this->apiResponse('success', 200, 'Post unliked', ['liked' => false]);
            } else {
                $post->likes()->create(['user_id' => $user->id]);
                return $this->apiResponse('success', 200, 'Post liked', ['liked' => true]);
            }
        } catch (\Throwable $e) {
            return $this->apiResponse('error', 500, 'Unexpected error', null, [
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
