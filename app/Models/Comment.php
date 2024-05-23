<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Comment extends Model
{
    use HasFactory;

    public int $numOfComments = 0;
    public array $childComments = [];

    protected $fillable = ['post_id', 'user_id', 'parent_id', 'comment'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(PostReaction::class, 'object');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function isOwner($userId)
    {
        return $this->user_id == $userId;
    }

    public static function getAllChildrenComments($comment): array
    {
        $comments = Comment::query()
            ->where('post_id', $comment->post_id)
            ->get();
        $result = [$comment];
        self::_getAllChildrenComments($comments, $comment->id, $result);
        return $result;
    }

    private static function _getAllChildrenComments($comments, $parentId, &$result = []): void
    {
        foreach ($comments as $comment) {
            if($comment->parent_id === $parentId) {
                $result[] = $comment;
                self::_getAllChildrenComments($comments, $comment->id, $result);
            }
        }
    }

}
