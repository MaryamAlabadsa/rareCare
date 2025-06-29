<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Image;
use App\Models\Comment;
use App\Models\Likes;
use App\Models\User;

class Likes extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'post_id', 'created_at', 'updated_at'];
    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
        'user_name',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }
    public function getUpdatedAtFormattedAttribute()
    {
        return $this->updated_at->format('Y-m-d H:i:s');
    }
    public function getUserNameAttribute()
    {
        return $this->user ? $this->user->name : 'Unknown User';
    }
}
