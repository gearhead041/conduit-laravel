<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;
    protected $fillable = ['body','user_id','article_id'];

    protected $appends = ['author'];

    protected $hidden = ['user_id', 'article_id'];

    public function author() : BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function article() : BelongsTo
    {
        return $this->belongsTo(Article::class,'article_id');
    }

    public function getAuthorAttribute()
    {
        $author = $this->author()->first();
        return [
            'username' => $author->username,
            'bio' => $author->bio,
            'image' => $author->image,
            'following' => auth()->user() ? auth()->user()->isFollowing($author) : false,
        ];
    }
}
