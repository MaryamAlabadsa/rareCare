<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Image;
use App\Models\Comment;
use App\Models\Likes;
use App\Models\User;
use App\Models\Save;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['txt', 'user_id', 'created_at', 'updated_at'];

    protected $appends = [
        'image_urls',
        'comment_count',
        'like_count',
        'is_liked_by_user',
        'created_at_formatted',
        'updated_at_formatted',
        'short_text',
        'user_name',
        'user_avatar_url',
        'image_count',
        'commented_by_user',
        'liked_by_user',
            'is_saved_by_user',
        'saves_count',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Likes::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Accessors
    public function getImageUrlsAttribute()
    {
        return $this->images->pluck('path');
    }

    public function getCommentCountAttribute()
    {
        return $this->comments->count();
    }

    public function getLikeCountAttribute()
    {
        return $this->likes->count();
    }

    public function getIsLikedByUserAttribute()
    {
        return auth()->check() ? $this->likes->contains('user_id', auth()->id()) : false;
    }

    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtFormattedAttribute()
    {
        return $this->updated_at->format('Y-m-d H:i:s');
    }

    public function getShortTextAttribute()
    {
        return Str::limit($this->txt, 100);
    }

    public function getUserNameAttribute()
    {
        return $this->user ? $this->user->name : 'Unknown User';
    }

    public function getUserAvatarUrlAttribute()
    {
        return $this->user ? $this->user->avatar_url : null;
    }

    public function getImageCountAttribute()
    {
        return $this->images->count();
    }

    public function getCommentedByUserAttribute()
    {
        return auth()->check() ? $this->comments->contains('user_id', auth()->id()) : false;
    }

    public function getLikedByUserAttribute()
    {
        return auth()->check() ? $this->likes->contains('user_id', auth()->id()) : false;
    }
public function saves()
{
    return $this->hasMany(Save::class);
}

public function getIsSavedByUserAttribute()
{
    return auth()->check() ? $this->saves->contains('user_id', auth()->id()) : false;
}


public function getSavesCountAttribute()
{
    return $this->saves()->count();
}


}
