<?php

declare(strict_types=1);

namespace App\Services\Travel;


use App\Repositories\Travel\TravelCommentRepository;

final readonly class TravelCommentService
{
    public function __construct(
        private TravelCommentRepository $repository
    ) {}

    public function toggleUpVote(int $commentId, int $userId): void
    {
        $this->repository->toggleUpVote($commentId, $userId);
    }

    public function getTravelCommentsTree(int $travelId): array
    {
        $list = $this->repository->getTravelComments($travelId);

        return $this->buildTree($list);
    }

    public function buildTree($comments): array
    {
        $tree = [];
        $stack = [];

        foreach ($comments as $comment) {

            $comment->children = [];

            while (!empty($stack) && end($stack)->depth >= $comment->depth) {
                array_pop($stack);
            }

            if (empty($stack)) {
                $tree[] = $comment;
            } else {
                $parent = end($stack);
                $parent->children[] = $comment;
            }

            $stack[] = $comment;
        }

        return $tree;
    }

    public function updateCommentContent(int $commentId, string $content): void
    {
        $this->repository->updateCommentContent($commentId, $content);
    }

    public function addComment(int $travelId, int $userId, ?int $parentId, string $comment): int
    {
        $parent = null;

        if ($parentId) {
            $parent = $this->repository->getById($parentId);

            if (!$parent || $parent->travel_id !== $travelId) {
                throw new \InvalidArgumentException('Invalid parent comment ID');
            }
        }

        $depth = $parent ? $parent->depth + 1 : 0;

        return $this->repository->insertComment(
            travelId: $travelId,
            userId: $userId,
            parentId: $parent?->id,
            depth: $depth,
            content: $comment,
        );
    }

    public function deleteComment(int $commentId): void
    {
        if (empty($this->repository->getCommentsTree($commentId))) {
            $this->repository->deleteComment($commentId);

            return;
        }

        $this->repository->saveComment($commentId, [
            'content'    => '[deleted]',
            'is_deleted' => true
        ]);
    }

    public function purgeTravelComments(int $travelId): void
    {
        $this->repository->purgeTravelComments($travelId);
    }
}