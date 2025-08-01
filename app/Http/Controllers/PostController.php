<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Log;
use App\Models\Image;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Comment;
use App\Models\Like;

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
        // تحقق من صحة البيانات
        $request->validate([
            'txt' => 'nullable|string',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

   $post = Post::create([
            'txt' => $request->txt,
            'user_id' => Auth::id(),
        ]);
        // رفع الصور المرتبطة بالمنشور
        if ($request->hasFile('images')) {
            // dd($request->images);
            foreach ($request->images as $file) {
                $path = $file->store('posts', 'public'); // تخزين في public/posts
                $post->images()->create([
                    'post_id' => $post->id,
                    'path' => $path,
                ]);
            }
        }   

return $this->apiResponse('success', 201, 'Post created successfully', $post->fresh()->load('images'));

    } catch (\Illuminate\Validation\ValidationException $e) {
        return $this->apiResponse('error', 422, 'Validation error', null, $e->errors());
    } catch (\Exception $e) {
        Log::error('Post store error: ', ['exception' => $e]);
        return $this->apiResponse('error', 500, 'Unexpected error', null, ['exception' => $e->getMessage()]);
    }
}



//////////////////////////////////////////////////////////////////////////


public function show(Post $post)
{
    try {
        $postWithRelations = $post->load(['images', 'comments.user', 'likes']);
        return $this->apiResponse('success', 200, 'Post retrieved', $postWithRelations);
    } catch (ModelNotFoundException $e) {
        Log::warning('Post not found: ', ['exception' => $e]);
        return $this->apiResponse('error', 404, 'Post not found');
    } catch (\Exception $e) {
        Log::error('Unexpected error during post retrieval: ', ['exception' => $e]);
        return $this->apiResponse('error', 500, 'Unexpected error occurred', null, ['exception' => $e->getMessage()]);
    }
}

public function destroy(Post $post)
{
    try {
        if ($post->user_id !== Auth::id()) {
            return $this->apiResponse('error', 403, 'You are not authorized to delete this post');
        }

        foreach ($post->images as $image) {
            try {
                if (Storage::disk('public')->exists($image->path)) {
                    Storage::disk('public')->delete($image->path);
                }
                $image->delete(); // remove from DB
            } catch (\Exception $fileError) {
                Log::warning('Failed to delete image file: ', ['path' => $image->path, 'exception' => $fileError]);
            }
        }

        $post->delete();

        return $this->apiResponse('success', 200, 'Post deleted successfully');

    } catch (ModelNotFoundException $e) {
        Log::warning('Post not found: ', ['exception' => $e]);
        return $this->apiResponse('error', 404, 'Post not found');
    } catch (\Exception $e) {
        Log::error('Unexpected error during post deletion: ', ['exception' => $e]);
        return $this->apiResponse('error', 500, 'Unexpected error occurred', null, ['exception' => $e->getMessage()]);
    }
}

public function update(Request $request, Post $post)
{
    try {
        // تحقق من ملكية المنشور
        if ($post->user_id !== Auth::id()) {
            return $this->apiResponse('error', 403, 'You are not authorized to update this post');
        }

        // تحقق من صحة البيانات المدخلة
        $request->validate([
            'txt' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // تحديث نص المنشور
        $post->update(['txt' => $request->txt]);

        // التعامل مع الصور الجديدة (إن وجدت)
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            if (!is_array($images)) {
                $images = [$images]; // تأكد أنها قابلة للتكرار
            }

            foreach ($images as $imageFile) {
                try {
                    $path = $imageFile->store('posts', 'public');

                    // حفظ الصورة في قاعدة البيانات
                    $savedImage = $post->images()->create([
                        'path' => 'storage/' . $path
                    ]);

                    Log::info('Image uploaded and saved: ' . $savedImage->path);

                } catch (\Exception $fileException) {
                    Log::warning('Image upload failed: ', ['exception' => $fileException->getMessage()]);
                }
            }
        }

        // تحميل البيانات المحدثة والعلاقات
        $post = $post->fresh()->load(['images', 'comments', 'likes', 'user', 'saves']);

        return $this->apiResponse('success', 200, 'Post updated successfully', $post);

    } catch (ValidationException $e) {
        Log::warning('Validation failed during update: ', ['errors' => $e->errors()]);
        return $this->apiResponse('error', 422, 'Validation failed', null, $e->errors());

    } catch (ModelNotFoundException $e) {
        Log::warning('Post not found during update: ', ['exception' => $e]);
        return $this->apiResponse('error', 404, 'Post not found');

    } catch (\Exception $e) {
        Log::error('Unexpected error during post update: ', ['exception' => $e]);
        return $this->apiResponse('error', 500, 'Unexpected error occurred', null, [
            'exception' => $e->getMessage(),
        ]);
    }
}


public function deleteImage(Post $post, Image $image)
{
    try {
        // تحقق من ملكية المستخدم للمنشور
        if ($post->user_id !== Auth::id()) {
            return $this->apiResponse('error', 403, 'You are not authorized to delete this image');
        }

        // تحقق أن الصورة تابعة للمنشور بالفعل
        if ($image->post_id !== $post->id) {
            return $this->apiResponse('error', 400, 'This image does not belong to the specified post');
        }

        // حذف الصورة من التخزين
        if (Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }

        // حذف السجل من قاعدة البيانات
        $image->delete();

        return $this->apiResponse('success', 200, 'Image deleted successfully');

    } catch (\Exception $e) {
        \Log::error('Error deleting image: ', ['exception' => $e]);
        return $this->apiResponse('error', 500, 'Failed to delete image', null, ['exception' => $e->getMessage()]);
    }
}


}