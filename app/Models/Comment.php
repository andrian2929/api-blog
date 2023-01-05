<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $fillable = [
        'comment',
    ];

    public function post()
    {
        return $this->hasManyThrough(Post::class, PostCommentUser::class, 'comment_id', 'id', 'id', 'post_id');
    }

    public function user()
    {
        return $this->hasManyThrough(User::class, PostCommentUser::class, 'comment_id', 'id', 'id', 'user_id');
    }
}
