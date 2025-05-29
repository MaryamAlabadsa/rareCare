<?php

namespace App\Http\Controllers;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\BaseController;


use Illuminate\Http\Request;

class PostController extends BaseController
{
    public function index()
{
    $posts = Post::with(['images', 'comments.user', 'likes'])->latest()->get();
    return $this->apiResponse('success', 200, 'Posts retrieved', $posts);
}

public function store(Request $request)
{
    try {
        $request->validate([
            'txt' => 'nullable|string',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $post = Post::create([
            'txt' => $request->txt,
            'user_id' => Auth::id(),
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                $path = $imageFile->store('posts', 'public');
                $post->images()->create(['path' => $path]);
            }
        }

        return $this->apiResponse('success', 201, 'Post created', $post->load('images'));
    } catch (\Illuminate\Validation\ValidationException $e) {
        return $this->apiResponse('error', 422, 'Validation error', null, $e->errors());
    } catch (\Exception $e) {
        return $this->apiResponse('error', 500, 'Unexpected error', null, ['exception' => $e->getMessage()]);
    }
}

public function show(Post $post)
{
    return $this->apiResponse('success', 200, 'Post retrieved', $post->load(['images', 'comments.user', 'likes']));
}

public function destroy(Post $post)
{
    if ($post->user_id !== Auth::id()) {
        return $this->apiResponse('error', 403, 'You are not authorized to delete this post');
    }

    foreach ($post->images as $image) {
        Storage::disk('public')->delete($image->path);
    }

    $post->delete();

    return $this->apiResponse('success', 200, 'Post deleted');
}

}
