<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Fillable attributes for mass assignment
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'bio',
        'role',
        'last_seen',
        'created_at',
        'updated_at',
    ];

    // Attributes hidden from arrays
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
    ];

    // Type casting for attributes
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen' => 'datetime',
    ];

    /***************
     * Relationships
     ***************/

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function saves()
    {
        return $this->hasMany(Save::class);
    }
public function savedPosts()
{
    return $this->belongsToMany(Post::class, 'saves', 'user_id', 'post_id');
}


    public function healthRecords()
    {
        return $this->hasMany(HealthRecord::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function appointmentsAsPatient()
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function appointmentsAsDoctor()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    public function communities()
    {
        return $this->belongsToMany(Community::class, 'memberships')->withTimestamps();
    }

    /******************
     * Accessors (get...)
     ******************/
public function getAvatarUrlAttribute()
{
    return $this->attributes['avatar_url'] 
        ? url('storage/' . $this->attributes['avatar_url']) 
        : null;
}



    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at?->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtFormattedAttribute()
    {
        return $this->updated_at?->format('Y-m-d H:i:s');
    }

    public function getFullNameAttribute()
    {
        return $this->name;
    }

    public function getEmailDomainAttribute()
    {
        return substr(strrchr($this->email, '@'), 1);
    }

    public function getProfileLinkAttribute()
    {
        return route('users.show', $this->id);
    }

    public function getIsOnlineAttribute()
    {
        return $this->last_seen && $this->last_seen->diffInMinutes(now()) < 5;
    }

    public function getShortBioAttribute()
    {
        return \Illuminate\Support\Str::limit($this->bio, 100);
    }

    public function getIsAdminAttribute()
    {
        return $this->role === 'admin';
    }

    public function getIsModeratorAttribute()
    {
        return $this->role === 'moderator';
    }

    public function getIsMemberOfCommunityAttribute()
    {
        return $this->communities()->exists();
    }

    public function getUnreadMessagesCountAttribute()
    {
        return $this->receivedMessages()->where('is_read', false)->count();
    }
}
