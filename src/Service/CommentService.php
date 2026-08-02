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
}
