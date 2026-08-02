<?php

namespace Contatoseguro\TesteBackend\Service;

use Contatoseguro\TesteBackend\Config\DB;

class CommentService
{
    private \PDO $pdo;
    public function __construct()
    {
        $this->pdo = DB::connect();
    }

    public function insertOne($productId, $adminUserId, $content)
    {
        $stm = $this->pdo->prepare("
            INSERT INTO comment (
                product_id,
                admin_user_id,
                content
            ) VALUES (
                :product_id,
                :admin_user_id,
                :content
            )
        ");

        return $stm->execute([
            ':product_id' => $productId,
            ':admin_user_id' => $adminUserId,
            ':content' => $content,
        ]);
    }

        public function insertReply($parentId, $adminUserId, $content)
    {
        $stm = $this->pdo->prepare("
            SELECT product_id
            FROM comment
            WHERE id = :id
        ");
        $stm->execute([':id' => $parentId]);
        $parentComment = $stm->fetch();
        if ($parentComment === false) {
            return false;
        }

        $stm = $this->pdo->prepare("
            INSERT INTO comment (
                product_id,
                admin_user_id,
                parent_id,
                content
            ) VALUES (
                :product_id,
                :admin_user_id,
                :parent_id,
                :content
            )
        ");

        return $stm->execute([
            ':product_id' => $parentComment->product_id,
            ':admin_user_id' => $adminUserId,
            ':parent_id' => $parentId,
            ':content' => $content,
        ]);
    }

        public function insertLike($commentId, $adminUserId)
    {
        $stm = $this->pdo->prepare("
            SELECT id
            FROM comment
            WHERE id = :id
        ");
        $stm->execute([':id' => $commentId]);
        if ($stm->fetch() === false) {
            return false;
        }

        $stm = $this->pdo->prepare("
            INSERT INTO comment_like (
                comment_id,
                admin_user_id
            ) VALUES (
                :comment_id,
                :admin_user_id
            )
        ");

        try {
            $stm->execute([
                ':comment_id' => $commentId,
                ':admin_user_id' => $adminUserId,
            ]);
        } catch (\PDOException $e) {
            return 'duplicado';
        }

        return true;
    }

        public function deleteOne($commentId, $adminUserId)
    {
        $stm = $this->pdo->prepare("
            SELECT admin_user_id
            FROM comment
            WHERE id = :id
        ");
        $stm->execute([':id' => $commentId]);
        $comment = $stm->fetch();
        if ($comment === false) {
            return false;
        }
        if ($comment->admin_user_id != $adminUserId) {
            return 'proibido';
        }

        $ids = [$commentId];
        $currentLevel = [$commentId];
        while (!empty($currentLevel)) {
            $placeholders = implode(',', array_fill(0, count($currentLevel), '?'));
            $stm = $this->pdo->prepare("SELECT id FROM comment WHERE parent_id IN ({$placeholders})");
            $stm->execute($currentLevel);
            $currentLevel = array_column($stm->fetchAll(\PDO::FETCH_ASSOC), 'id');
            $ids = array_merge($ids, $currentLevel);
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $this->pdo->beginTransaction();
        try {
            $stm = $this->pdo->prepare("DELETE FROM comment_like WHERE comment_id IN ({$placeholders})");
            $stm->execute($ids);
            $stm = $this->pdo->prepare("DELETE FROM comment WHERE id IN ({$placeholders})");
            $stm->execute($ids);
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            return false;
        }
        $this->pdo->commit();

        return true;
    }
        public function getByProduct($productId)
    {
        $stm = $this->pdo->prepare("
            SELECT c.id, c.admin_user_id, au.name as author, c.parent_id, c.content, c.created_at,
                (SELECT COUNT(*) FROM comment_like cl WHERE cl.comment_id = c.id) as likes
            FROM comment c
            LEFT JOIN admin_user au ON au.id = c.admin_user_id
            WHERE c.product_id = :product_id
            ORDER BY c.created_at
        ");
        $stm->execute([':product_id' => $productId]);

        return $stm;
    }
}
