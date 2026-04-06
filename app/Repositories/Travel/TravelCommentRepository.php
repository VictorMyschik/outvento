<?php

declare(strict_types=1);

namespace App\Repositories\Travel;

use App\Models\Travel\TravelComment;
use App\Models\User;
use App\Repositories\DatabaseRepository;

final readonly class TravelCommentRepository extends DatabaseRepository
{
    public function getById(int $id): ?TravelComment
    {
        return TravelComment::where('id', $id)->first();
    }

    public function updateCommentContent(int $commentId, string $content): void
    {
        $this->db->table(TravelComment::getTableName())->where("id", $commentId)->update(["content" => $content]);
    }

    public function getTravelComments(int $travelId): array
    {
        return $this->db->table(TravelComment::getTableName())
            ->join(User::TABLE, User::TABLE . '.id', '=', TravelComment::getTableName() . '.user_id')
            ->where('travel_id', $travelId)
            ->orderBy('path')
            ->limit(500)
            ->selectRaw(implode(', ', [
                TravelComment::getTableName() . '.*',
                User::TABLE . '.name',
                User::TABLE . '.id as user_id',
            ]))
            ->get()->all();
    }

    public function saveComment(int $commentId, array $data): int
    {
        if ($commentId > 0) {
            $this->db->table(TravelComment::getTableName())->where('id', $commentId)->update($data);

            return $commentId;
        }

        return $this->db->table(TravelComment::getTableName())->insertGetId($data);
    }

    public function insertComment(int $travelId, int $userId, ?int $parentId, int $depth, string $content): int
    {
        $sql = <<<SQL
        WITH new_id AS (SELECT nextval('travel_comments_id_seq') AS id),
             parent AS (SELECT path
                        FROM travel_comments
                        WHERE id = :parent_id)
        INSERT
        INTO travel_comments (id, travel_id, user_id, parent_id, depth, content, path)
        SELECT new_id.id, :travel_id, :user_id, :parent_id, :depth, :content, COALESCE(
                       parent.path || text2ltree(new_id.id::text),
                       text2ltree(new_id.id::text)
               )
        FROM new_id
                 LEFT JOIN parent ON true
        RETURNING id;
        SQL;

        $result = $this->db->selectOne($sql, [
            'travel_id' => $travelId,
            'user_id'   => $userId,
            'parent_id' => $parentId,
            'depth'     => $depth,
            'content'   => $content,
        ]);

        return (int)$result->id;
    }

    public function getCommentsTree(int $commentId): array
    {
        $comment = $this->db->table(TravelComment::getTableName())
            ->select('path')
            ->where('id', $commentId)
            ->first();

        if (!$comment) {
            return [];
        }

        return $this->db->table(TravelComment::getTableName())
            ->whereRaw('path <@ text2ltree(?)', [$comment->path])
            ->where('id', '!=', $commentId)
            ->get()
            ->all();
    }

    public function deleteComment(int $commentId): void
    {
        $this->db->table(TravelComment::getTableName())->where('id', $commentId)->delete();
    }

    public function purgeTravelComments(int $travelId): void
    {
        $this->db->table(TravelComment::getTableName())->where('travel_id', $travelId)->delete();
    }

    public function toggleUpVote(int $commentId, int $userId): void
    {
        $this->db->transaction(function () use ($commentId, $userId) {
            $vote = $this->db->table('travel_comment_votes')
                ->where('comment_id', $commentId)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            // 1. нет голоса → ставим +1
            if (!$vote) {
                $this->db->table('travel_comment_votes')->insert([
                    'comment_id' => $commentId,
                    'user_id'    => $userId,
                    'vote'       => 1,
                ]);

                $this->db->table('travel_comments')
                    ->where('id', $commentId)
                    ->increment('score', 1);

                return;
            }

            // 2. уже +1 → убираем голос
            if ($vote->vote === 1) {
                $this->db->table('travel_comment_votes')
                    ->where('id', $vote->id)
                    ->delete();

                $this->db->table('travel_comments')
                    ->where('id', $commentId)
                    ->decrement('score', 1);

                return;
            }

            // 3. было -1 → делаем +1
            $this->db->table('travel_comment_votes')
                ->where('id', $vote->id)
                ->update(['vote' => 1]);

            $this->db->table('travel_comments')
                ->where('id', $commentId)
                ->increment('score', 2);
        });
    }
}
