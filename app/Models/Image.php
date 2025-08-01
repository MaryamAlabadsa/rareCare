<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
  public function post()
    {
        return $this->belongsTo(Post::class);
    }
    protected $fillable = ['post_id', 'path', 'created_at', 'updated_at'];
    protected $appends = [
        'url',
        'created_at_formatted',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];



    public function getUrlAttribute()
    {
        return asset($this->path); // Assuming the path is a relative URL
    }
    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }

}
