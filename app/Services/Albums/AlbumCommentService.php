<?php

declare(strict_types=1);

namespace App\Services\Albums;

use App\Models\Albums\AlbumMediaComment;
use App\Models\User;
use App\Repositories\Albums\AlbumMediaCommentRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use stdClass;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final readonly class AlbumCommentService
{
    private const int MAX_DEPTH = 2;

    public function __construct(
        private AlbumMediaCommentRepository $repository,
        private AlbumService                $albumService,
    ) {}

    public function getCommentById(int $commentId): stdClass
    {
        return $this->repository->getCommentById($commentId);
    }

    public function getRootComments(int $mediaId, ?User $viewer, int $perPage): LengthAwarePaginator
    {
        $this->ensureCanReadMedia($mediaId, $viewer);

        return $this->repository->paginateRootComments($mediaId, $perPage);
    }

    public function createComment(int $mediaId, User $author, string $body): int
    {
        $id = $this->repository->saveComment(0, [
            'media_id' => $mediaId,
            'user_id'  => $author->id,
            'body'     => $body,
        ]);

        $this->repository->incrementMediaCommentsCount($mediaId);

        return $id;
    }

    public function updateComment(int $commentId, string $body): void
    {
        $this->repository->saveComment($commentId, ['body' => $body]);
    }

    public function deleteComment(int $mediaId, int $commentId): void
    {
        if ($this->repository->deleteComment($commentId)) {
            $this->repository->decrementMediaCommentsCount($mediaId);
        }
    }

    public function serializeComment(AlbumMediaComment $comment, ?User $viewer): array
    {
        return [
            'id'            => (int)$comment->id,
            'media_id'      => (int)$comment->media_id,
            'parent_id'     => $comment->parent_id ? (int)$comment->parent_id : null,
            'depth'         => (int)$comment->depth,
            'body'          => $comment->trashed() ? null : (string)$comment->body,
            'is_deleted'    => $comment->trashed(),
            'replies_count' => (int)$comment->replies_count,
            'created_at'    => $comment->created_at?->toIso8601String(),
            'updated_at'    => $comment->updated_at?->toIso8601String(),
            'permissions'   => [
                'can_reply'  => !$comment->trashed() && (int)$comment->depth < self::MAX_DEPTH,
                'can_delete' => $viewer !== null && !$comment->trashed() && ((int)$comment->user_id === (int)$viewer->id || $viewer->isSuperAdmin()),
            ],
            'author'        => [
                'id'     => (int)$comment->user_id,
                'name'   => (string)($comment->user?->name ?? ''),
                'avatar' => $comment->user?->getAvatar(),
            ],
        ];
    }

    private function ensureCanReadMedia(int $mediaId, ?User $viewer): void
    {
        if (!$this->albumService->canViewAlbumMedia($mediaId, $viewer)) {
            throw new AccessDeniedHttpException('You do not have access to this media.');
        }
    }

    private function ensureCanWriteMedia(int $mediaId, User $viewer): void
    {
        if (!$this->albumService->canCommentOnAlbumMedia($mediaId, $viewer)) {
            throw new AccessDeniedHttpException('You cannot comment this media.');
        }
    }

    private function flushCommentCaches(int $mediaId, ?int $parentId = null): void
    {
        Cache::add('album_media_comments:' . $mediaId . ':version', 1);
        Cache::increment('album_media_comments:' . $mediaId . ':version');

        if ($parentId !== null) {
            Cache::add('album_media_comments:' . $mediaId . ':parent:' . $parentId . ':version', 1);
            Cache::increment('album_media_comments:' . $mediaId . ':parent:' . $parentId . ':version');
        }
    }
}

