<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostCommentUser extends Model
{
    use HasFactory;
    protected $table = 'post_comment_user';
    protected $fillable = [
        'post_id',
        'comment_id',
        'user_id',
    ];
}
