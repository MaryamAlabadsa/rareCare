<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{
    protected function apiResponse($status, $code, $message, $data = null, $errors = null)
    {
        return response()->json([
            'status'  => $status,
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
            'errors'  => $errors,
        ], $code);
    }

    public function index()
    {
        $users = User::all();
        return $this->apiResponse('success', 200, 'Users retrieved successfully', $users);
    }

    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return $this->apiResponse('success', 200, 'User retrieved successfully', $user);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse('error', 404, 'User not found');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $request->validate([
                'name'  => 'sometimes|string',
                'email' => 'sometimes|email|unique:users,email,' . $id,
                'role'  => 'sometimes|string',
                'dob'   => 'sometimes|date',
            ]);

            $user->update($request->only('name', 'email', 'role', 'dob'));

            return $this->apiResponse('success', 200, 'User updated successfully', $user);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse('error', 404, 'User not found');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiResponse('error', 422, 'Validation failed', null, $e->errors());
        }
    }

    public function destroy($id)
    {
        try {
            User::findOrFail($id)->delete();
            return $this->apiResponse('success', 200, 'User deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse('error', 404, 'User not found');
        }
    }
    public function userWithPosts($id)
{
    $user = User::with('posts.images', 'posts.likes', 'posts.comments')->find($id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    return response()->json([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url,
        ],
        'posts' => $user->posts,
    ]);
}
    public function userWithSavedPosts($id)
    {
        $user = User::with('savedPosts.images', 'savedPosts.likes', 'savedPosts.comments')->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
            ],
            'saved_posts' => $user->savedPosts,
        ]);
    }
    public function userWithLikedPosts($id)
    {
        $user = User::with('likedPosts.images', 'likedPosts.likes', 'likedPosts.comments')->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
            ],
            'liked_posts' => $user->likedPosts,
        ]);
    }
    public function userWithComments($id)
    {
        $user = User::with('comments.post.images', 'comments.post.likes', 'comments.post.comments')->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
            ],
            'comments' => $user->comments,
        ]);
    }
    public function userWithLikes($id)
    {
        $user = User::with('likes.post.images', 'likes.post.likes', 'likes.post.comments')->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
            ],
            'likes' => $user->likes,
        ]);
    }
    public function userWithPostsAndComments($id)
    {
        $user = User::with(['posts.images', 'posts.likes', 'posts.comments', 'comments.post.images', 'comments.post.likes', 'comments.post.comments'])->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
            ],
            'posts' => $user->posts,
            'comments' => $user->comments,
        ]);
    }

public function updateAvatar(Request $request)
{
        dd(Auth::user()); // مؤقتًا فقط للاختبار

    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized',
        ], 401);
    }
    $request->validate([
        'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB Max
    ]);

    try {
        // حذف الصورة القديمة إن وجدت
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // رفع الصورة الجديدة
        $avatarPath = $request->file('avatar')->store('avatars', 'public');

        // تحديث المستخدم
        $user->avatar = $avatarPath;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Avatar updated successfully',
            'avatar_url' => asset('storage/' . $avatarPath),
        ]);

    } catch (\Exception $e) {
        \Log::error('Avatar update failed', ['exception' => $e]);
        return response()->json([
            'status' => false,
            'message' => 'Unexpected error occurred',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
