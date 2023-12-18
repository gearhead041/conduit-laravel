<?php

namespace App\Models;

use App\Models\Favorite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Article extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'title', 'description', 'body', 'tagList', 'user_id'];

    protected $hidden = ['user_id', 'id'];
    protected $appends = ['author', 'favoritesCount', 'tagList','favorited'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function addFavorite(User $user)
    {
        $this->favorites()->createOrFirst(['user_id' => $user->id]);
    }

    public function removeFavorite(User $user)
    {
        $this->favorites()->where('user_id', $user->id)->delete();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, );
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

    public function getFavoritesCountAttribute()
    {
        return $this->favorites()->count();
    }

    public function getTagListAttribute()
    {
        return explode(',', $this->attributes['tagList']);
    }

    public function getFavoritedAttribute()
    {
        $user = auth()->user();
        return $this->favorites()->where('user_id', $user->id)->first() != null;
    }
}
