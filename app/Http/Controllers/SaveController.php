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

        $existingSave = Save::where('user_id', $user->id)
                            ->where('post_id', $post->id)
                            ->first();

        if ($existingSave) {
            $existingSave->delete();
            return $this->apiResponse('success', 200, 'Post unsaved', ['saved' => false]);
        } else {
            Save::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
            ]);
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
    dd('index');
    try {
        $user = Auth::user();

        if (!$user) {
            return $this->apiResponse('error', 401, 'Unauthorized');
        }

        $savedPosts = $user->savedPosts()->with('user')->latest()->get();

        return $this->apiResponse('success', 200, 'Saved posts retrieved successfully', [
            'posts' => $savedPosts
        ]);

    } catch (\Throwable $e) {
        return $this->apiResponse('error', 500, 'Unexpected error', null, [
            'exception' => $e->getMessage(),
        ]);
    }
}
public function postWithSavedByUsers($postId)
{
    $post = Post::with('savedByUsers')->find($postId);

    if (!$post) {
        return response()->json(['message' => 'Post not found'], 404);
    }

    return response()->json([
        'post' => $post,
        'saved_by_users' => $post->savedByUsers
    ]);
}


}
