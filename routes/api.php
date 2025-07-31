<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
 use App\Http\Controllers\SaveController;




// ✅ التسجيل
Route::post('/register', [AuthController::class, 'register']);
// يقوم المستخدم بإنشاء حساب جديد عن طريق إرسال الاسم، البريد، وكلمة المرور.

Route::post('/login', [AuthController::class, 'login']);
// يقوم المستخدم بتسجيل الدخول واستلام توكن المصادقة (Sanctum).

Route::middleware('auth:sanctum')->group(function () {

    // ✅ تسجيل الخروج
    Route::post('/logout', [AuthController::class, 'logout']);
    // تسجيل خروج المستخدم الحالي (إبطال التوكن).

    // ✅ الملف الشخصي
    Route::get('/profile', [AuthController::class, 'profile']);
    // جلب معلومات المستخدم المصادق عليه حاليًا.

    // ✅ تغيير كلمة المرور
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    // تغيير كلمة مرور المستخدم المصادق عليه.

    // ✅ جلب جميع المستخدمين (اختياري - للإدارة)
    Route::get('/users', [UserController::class, 'index']);
    // قائمة بجميع المستخدمين (ممكن تقييدها للمشرف فقط).

    // ✅ جلب مستخدم معين
    Route::get('/user/{id}', [UserController::class, 'show']);
    // جلب معلومات مستخدم حسب الـ ID.

    // ✅ تحديث بيانات مستخدم
    Route::post('/user/{id}', [UserController::class, 'update']);
    // تعديل بيانات مستخدم معين (يُفضل التحقق من الصلاحيات).
});
Route::middleware('auth:sanctum')->group(function () {

    // ✅ كل المنشورات
    Route::get('/posts', [PostController::class, 'index']);
    // جلب كل المنشورات من قاعدة البيانات.

    // ✅ إنشاء منشور جديد
    Route::post('/posts', [PostController::class, 'store']);
    // إنشاء منشور جديد للمستخدم الحالي.

    // ✅ عرض منشور واحد
    Route::get('/posts/{post}', [PostController::class, 'show']);
    // عرض تفاصيل منشور معين.

    // ✅ حذف منشور
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
    // حذف منشور معين (يفضل التحقق من ملكية المستخدم).

    // ✅ تعديل منشور
    Route::post('/posts/{post}', [PostController::class, 'update']);
    // تعديل منشور معين (مع تحقق الصلاحيات).


    // ✅ حذف صورة من منشور
    Route::delete('/posts/{post}/images/{image}', [PostController::class, 'deleteImage']);
    // حذف صورة من منشور معين.

    // ✅ إضافة تعليق
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
    // يضيف تعليق على منشور.

    // ✅ حذف تعليق
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    // حذف تعليق معين (بشرط أن يكون صاحب التعليق أو مشرف).

    // ✅ إعجاب/إلغاء إعجاب
    Route::post('/posts/{post}/like', [LikeController::class, 'toggle']);
    // إذا أعجب مسبقًا، يتم إلغاء الإعجاب، والعكس.

    // ✅ حفظ/إلغاء حفظ منشور
    Route::post('/posts/{post}/toggle-save', [SaveController::class, 'toggle']);
    // يحفظ منشور في قائمة المستخدم أو يلغيه.

    // ✅ جلب كل المنشورات المحفوظة للمستخدم
   Route::get('/user/saved-posts/{id}', [UserController::class, 'userWithSavedPosts']);
    // قائمة بالمنشورات التي حفظها المستخدم.

    // routes/api.php
// Route::get('/posts/{id}/saved-by-users', [SaveController::class, 'postWithSavedByUsers']);

    // ✅ جلب كل المنشورات التي أعجب بها المستخدم
    Route::get('/posts/liked/{id}', [UserController::class, 'userWithLikes']);

    Route::get('/user/{id}/posts', [UserController::class, 'userWithPosts']);
    Route::post('/test', [UserController::class, 'test']);

});

Route::middleware('auth:sanctum')->post('/user/avatar', [UserController::class, 'updateAvatar']);
// تحديث صورة الملف الشخصي للمستخدم. يجب أن يكون المستخدم مصادق عليه.
