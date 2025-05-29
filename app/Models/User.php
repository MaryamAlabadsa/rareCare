<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
        'role', // Assuming you have a role attribute
        'last_seen', // Assuming you have a last_seen attribute to track online status
        'created_at',
        'updated_at'
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function posts() {
    return $this->hasMany(Post::class);
}

public function comments() {
    return $this->hasMany(Comment::class);
}

public function healthRecords() {
    return $this->hasMany(HealthRecord::class);
}

public function sentMessages() {
    return $this->hasMany(Message::class, 'sender_id');
}

public function receivedMessages() {
    return $this->hasMany(Message::class, 'receiver_id');
}

public function appointmentsAsPatient() {
    return $this->hasMany(Appointment::class, 'patient_id');
}

public function appointmentsAsDoctor() {
    return $this->hasMany(Appointment::class, 'doctor_id');
}

public function communities() {
    return $this->belongsToMany(Community::class, 'memberships')->withTimestamps();
}
public function likes()
{
    return $this->hasMany(Like::class);
}
    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : asset('images/default-avatar.png');
    }

    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtFormattedAttribute()
    {
        return $this->updated_at->format('Y-m-d H:i:s');
    }
    public function getFullNameAttribute()
    {
        return $this->name; // Assuming 'name' is the full name
    }
    public function getEmailDomainAttribute()
    {
        return substr(strrchr($this->email, "@"), 1); // Extracts the domain from the email
    }
    public function getProfileLinkAttribute()
    {
        return route('users.show', $this->id); // Assuming you have a route named 'users.show'
    }
    public function getIsOnlineAttribute()
    {
        // Assuming you have a way to determine if the user is online
        return $this->last_seen && $this->last_seen->diffInMinutes(now()) < 5; // Example: online if last seen within 5 minutes
    }
    public function getShortBioAttribute()
    {
        return str_limit($this->bio, 100); // Adjust the limit as needed
    }
    public function getIsAdminAttribute()
    {
        return $this->role === 'admin'; // Assuming you have a 'role' attribute
    }
    public function getIsModeratorAttribute()
    {
        return $this->role === 'moderator'; // Assuming you have a 'role' attribute
    }
    public function getIsMemberOfCommunityAttribute()
    {
        return $this->communities->isNotEmpty(); // Checks if the user is a member of any community
    }
    public function getUnreadMessagesCountAttribute()
    {
        return $this->receivedMessages()->where('is_read', false)->count(); // Assuming you have an 'is_read' attribute
    }

}
