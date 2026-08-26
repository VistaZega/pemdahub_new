<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumGroup extends Model
{
    use HasFactory;

    protected $table = 'forum_groups';

    protected $fillable = [
        'school_id',
        'classroom_id',
        'subject_id',
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'type',
        'is_official',
        'only_admin_can_post',
        'created_by_user_id',
    ];

    protected $casts = [
        'is_official' => 'boolean',
        'only_admin_can_post' => 'boolean',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function members()
    {
        return $this->hasMany(ForumGroupMember::class, 'group_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'forum_group_members', 'group_id', 'user_id')
            ->withPivot('role', 'joined_at', 'last_read_at')
            ->withTimestamps();
    }

    public function threads()
    {
        return $this->hasMany(ForumThread::class, 'group_id')->latest();
    }

    public function latestThread()
    {
        return $this->hasOne(ForumThread::class, 'group_id')->latestOfMany();
    }

    public function extracurricular()
    {
        return $this->hasOne(Extracurricular::class, 'forum_group_id');
    }
}
