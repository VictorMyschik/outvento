<?php

declare(strict_types=1);

namespace App\Repositories\Albums;

use App\Models\Albums\AlbumMedia;
use App\Models\Albums\AlbumMediaComment;
use App\Repositories\DatabaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use stdClass;

final readonly class AlbumMediaCommentRepository extends DatabaseRepository
{
    public function paginateRootComments(int $mediaId, int $perPage): LengthAwarePaginator
    {
        return AlbumMediaComment::query()
            ->where('media_id', $mediaId)
            ->whereNull('parent_id')
            ->where(function (Builder $query): void {
                $query->whereNull('deleted_at')
                    ->orWhere(function (Builder $nested): void {
                        $nested->whereNotNull('deleted_at')
                            ->where('replies_count', '>', 0);
                    });
            })
            ->with(['user:id,name,avatar'])
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function paginateReplies(int $mediaId, int $parentId, int $perPage): LengthAwarePaginator
    {
        return AlbumMediaComment::query()
            ->where('media_id', $mediaId)
            ->where('parent_id', $parentId)
            ->where(function (Builder $query): void {
                $query->whereNull('deleted_at')
                    ->orWhere(function (Builder $nested): void {
                        $nested->whereNotNull('deleted_at')
                            ->where('replies_count', '>', 0);
                    });
            })
            ->with(['user:id,name,avatar'])
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function getCommentById(int $commentId): ?stdClass
    {
        return $this->db->table(AlbumMediaComment::TABLE)->where('id', $commentId)->first();
    }

    public function saveComment(int $id, array $data): int
    {
        if ($id > 0) {
            $this->db->table(AlbumMediaComment::TABLE)->where('id', $id)->update($data);

            return $id;
        }

        $id = $this->db->table(AlbumMediaComment::TABLE)->insertGetId($data);

        if ($id) {
            $this->incrementMediaCommentsCount($data['media_id']);
        }

        return $id;
    }

    public function incrementMediaCommentsCount(int $mediaId): void
    {
        $this->db->table(AlbumMedia::TABLE)->where('id', $mediaId)->increment('comments_count');
    }

    public function decrementMediaCommentsCount(int $mediaId): void
    {
        $this->db->table(AlbumMedia::TABLE)
            ->where('id', $mediaId)
            ->where('comments_count', '>', 0)
            ->decrement('comments_count');
    }

    public function deleteComment(int $commentId): bool
    {
        return (bool)$this->db->table(AlbumMediaComment::TABLE)->where('id', $commentId)->delete();
    }
}

