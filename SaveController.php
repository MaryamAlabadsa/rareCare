<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use App\Models\Save;
use App\Http\Resources\PostResource;

class SaveController extends BaseController
{
    public function toggle(Post $post)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->apiResponse('error', 401, 'Unauthorized');
            }

            $isSaved = $user->savedPosts()->where('post_id', $post->id)->exists();

            if ($isSaved) {
                $user->savedPosts()->detach($post->id);
                return $this->apiResponse('success', 200, 'Post unsaved', ['saved' => false]);
            } else {
                $user->savedPosts()->attach($post->id);
                return $this->apiResponse('success', 200, 'Post saved', ['saved' => true]);
            }

        } catch (\Throwable $e) {
            return $this->apiResponse('error', 500, 'Unexpected error', null, [
                'exception' => $e->getMessage(),
            ]);
        }
    }
public function index()
{
    try {
        $user = Auth::user();

        if (!$user) {
            return $this->apiResponse('error', 401, 'Unauthorized');
        }

        $savedPosts = $user->savedPosts()
            ->with(['user', 'images', 'comments', 'likes'])
            ->latest()
            ->get();

        if ($savedPosts->isEmpty()) {
            return $this->apiResponse('error', 404, 'No saved posts found');
        }

        return $this->apiResponse('success', 200, 'Saved posts retrieved', [
            'posts' => \App\Http\Resources\PostResource::collection($savedPosts),
        ]);

    } catch (\Exception $e) {
        \Log::error('Error fetching saved posts', ['exception' => $e]);
        return $this->apiResponse('error', 500, 'Unexpected error occurred', null, [
            'exception' => $e->getMessage(),
        ]);
    }
}

}
