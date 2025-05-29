<?php

namespace App\Http\Controllers;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;



class CommentController extends BaseController
{
    public function store(Request $request, Post $post)
{
    try {
        $validated = $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $comment = $post->comments()->create([
            'content' => $validated['content'],
            'user_id' => Auth::id(),
        ]);

        return $this->apiResponse('success', 201, 'Comment added', $comment->load('user'));
    } catch (\Illuminate\Validation\ValidationException $e) {
        return $this->apiResponse('error', 422, 'Validation error', null, $e->errors());
    } catch (\Exception $e) {
        return $this->apiResponse('error', 500, 'Unexpected error', null, ['exception' => $e->getMessage()]);
    }
}

public function destroy(Comment $comment)
{
    if ($comment->user_id !== Auth::id()) {
        return $this->apiResponse('error', 403, 'You are not authorized to delete this comment');
    }

    $comment->delete();

    return $this->apiResponse('success', 200, 'Comment deleted');
}

}
