<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserController extends Controller
{
    protected function apiResponse($status, $code, $message, $data = null, $errors = null)
    {
        return response()->json([
            'status' => $status,
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
        ], $code);
    }

    // CRUD -------------------------------

    public function index()
    {
        $users = User::all();
        return $this->apiResponse('success', 200, 'Users retrieved successfully', $users);
    }

    public function show($id)
    {
        $user = User::find($id);
        return $user
            ? $this->apiResponse('success', 200, 'User retrieved successfully', $user)
            : $this->apiResponse('error', 404, 'User not found');
    }
public function update(Request $request, $id)
{
    try {
        // الحصول على المستخدم أو رمي استثناء إذا لم يوجد
        $user = User::findOrFail($id);

        // التحقق من صحة البيانات الواردة
        $request->validate([
            'name'       => 'sometimes|string|max:255',
            'email'      => 'sometimes|email|unique:users,email,' . $id,
            'role'       => 'sometimes|string',
            'dob'        => 'sometimes|date',
            'avatar_url' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // معالجة رفع الصورة إذا وُجدت
        if ($request->hasFile('avatar_url')) {
            // حذف الصورة القديمة (اختياري، إذا كنت تريد حذف القديم)
            if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
                Storage::disk('public')->delete($user->avatar_url);
            }

            // حفظ الصورة الجديدة
            $path = $request->file('avatar_url')->store('avatars', 'public');
            $user->avatar_url = $path;
        }

        // تحديث باقي بيانات المستخدم
        $user->fill($request->only('name', 'email', 'role', 'dob'));
        $user->save();

        // تجهيز البيانات للرد، واستخدام Accessor لعرض رابط الصورة كامل
        $userData = $user->toArray();

        // إذا لديك accessor صحيح للـ avatar_url في موديل User، يمكنك الاعتماد عليه مباشرة
        // وإذا لم يكن، يمكنك بناء الرابط هنا:
        // $userData['avatar_url'] = $user->avatar_url ? asset('storage/' . $user->avatar_url) : null;

        return $this->apiResponse('success', 200, 'User updated successfully', $userData);

    } catch (ModelNotFoundException) {
        return $this->apiResponse('error', 404, 'User not found');
    } catch (ValidationException $e) {
        return $this->apiResponse('error', 422, 'Validation failed', null, $e->errors());
    } catch (\Exception $e) {
        Log::error('User update failed', ['error' => $e->getMessage()]);
        return $this->apiResponse('error', 500, 'Unexpected error', null, $e->getMessage());
    }
}





    public function destroy($id)
    {
        try {
            User::findOrFail($id)->delete();
            return $this->apiResponse('success', 200, 'User deleted successfully');
        } catch (ModelNotFoundException) {
            return $this->apiResponse('error', 404, 'User not found');
        }
    }

    // RELATIONSHIPS -----------------------

    private function basicUserData($user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url,
        ];
    }

    public function userWithPosts($id)
    {
        $user = User::with('posts.images', 'posts.likes', 'posts.comments')->find($id);

        return $user
            ? response()->json(['user' => $this->basicUserData($user), 'posts' => $user->posts])
            : response()->json(['message' => 'User not found'], 404);
    }

public function userWithSavedPosts($id)
{
    // Load user with their saved posts and related images, likes, comments
    $user = User::with(['savedPosts.images', 'savedPosts.likes', 'savedPosts.comments'])->find($id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // Assuming basicUserData() extracts basic user info
    return response()->json([
        'user' => $this->basicUserData($user),
        'saved_posts' => $user->savedPosts
    ]);
}


    public function userWithLikedPosts($id)
    {
        $user = User::with('likedPosts.images', 'likedPosts.likes', 'likedPosts.comments')->find($id);

        return $user
            ? response()->json(['user' => $this->basicUserData($user), 'liked_posts' => $user->likedPosts])
            : response()->json(['message' => 'User not found'], 404);
    }

    public function userWithComments($id)
    {
        $user = User::with('comments.post.images', 'comments.post.likes', 'comments.post.comments')->find($id);

        return $user
            ? response()->json(['user' => $this->basicUserData($user), 'comments' => $user->comments])
            : response()->json(['message' => 'User not found'], 404);
    }

  public function userWithLikes($id)
{
    // Eager load user with likes, and each liked post's images, likes, comments
    $user = User::with('likes.post.images', 'likes.post.likes', 'likes.post.comments')->find($id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // Return basic user data and likes
    return response()->json([
        'user' => $this->basicUserData($user),
        'likes' => $user->likes,
    ]);
}


    public function userWithPostsAndComments($id)
    {
        $user = User::with([
            'posts.images',
            'posts.likes',
            'posts.comments',
            'comments.post.images',
            'comments.post.likes',
            'comments.post.comments'
        ])->find($id);

        return $user
            ? response()->json([
                'user' => $this->basicUserData($user),
                'posts' => $user->posts,
                'comments' => $user->comments,
            ])
            : response()->json(['message' => 'User not found'], 404);
    }

    // UPDATE AVATAR -----------------------


public function updateAvatar(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return $this->apiResponse(false, 401, 'Unauthorized - user not authenticated');
    }

    $request->validate([
        'avatar_url' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    try {
        // Delete old avatar
        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        // Store new avatar
        $path = $request->file('avatar_url')->store('avatars', 'public');
        $user->avatar_url = $path;
        $user->save();

        return $this->apiResponse(true, 200, 'Avatar updated successfully', [
            'avatar_url' => asset('storage/' . $path)
        ]);
    } catch (\Exception $e) {
        Log::error('Avatar update failed', [
            'user_id' => $user->id,
            'error' => $e->getMessage()
        ]);
        return $this->apiResponse(false, 500, 'Unexpected error occurred', null, $e->getMessage());
    }
}
public function test(Request $request)
{
    if ($request->hasFile('avatar_url')) {
        $file = $request->file('avatar_url');

        if ($file->isValid()) {
            $path = $file->store('avatars', 'public');
            return response()->json([
                'status' => 'ok',
                'path' => $path,
                'url' => asset('storage/' . $path),
            ]);
        }
    }

    return response()->json(['status' => 'no file uploaded']);
}

}
