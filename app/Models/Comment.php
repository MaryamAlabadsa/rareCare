<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    public function post()
{
    return $this->belongsTo(Post::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}
    protected $fillable = ['post_id', 'user_id', 'content'];
    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtFormattedAttribute()
    {
        return $this->updated_at->format('Y-m-d H:i:s');
    }
}
