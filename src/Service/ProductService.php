<?php

namespace Contatoseguro\TesteBackend\Service;

use Contatoseguro\TesteBackend\Config\DB;

class ProductService
{
    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? DB::connect();
    }

    public function getAll($adminUserId, $filters = [])
    {
        $categoryTitle = "c.title";
        $translationJoin = "";
        $binds = [];

        if (isset($filters['lang'])) {
            $categoryTitle = "COALESCE(ct.label, c.title)";
            $translationJoin = " LEFT JOIN category_translation ct ON ct.category_id = c.id AND ct.lang_code = :lang";
            $binds[':lang'] = $filters['lang'];
        }

        $query = "
            SELECT p.*, {$categoryTitle} as category_title
            FROM product p
            INNER JOIN product_category pc ON pc.product_id = p.id
            INNER JOIN category c ON c.id = pc.cat_id{$translationJoin}
            WHERE p.company_id = {$this->getCompanyFromAdminUser($adminUserId)}
        ";

        if (isset($filters['active'])) {
            $query .= " AND p.active = :active";
            $binds[':active'] = $filters['active'];
        }
        if (isset($filters['category'])) {
            $query .= " AND p.id IN (SELECT product_id FROM product_category WHERE cat_id = :category)";
            $binds[':category'] = $filters['category'];
        }

        if (isset($filters['min_stock'])) {
            $query .= " AND p.stock >= :min_stock";
            $binds[':min_stock'] = $filters['min_stock'];
        }

        if (isset($filters['order'])) {
            $query .= " ORDER BY p.created_at " . strtoupper($filters['order']);
        }

        $stm = $this->pdo->prepare($query);
        $stm->execute($binds);

        return $stm;
    }

    public function getOne($id)
    {
        $stm = $this->pdo->prepare("
            SELECT *
            FROM product
            WHERE id = {$id}
        ");
        $stm->execute();

        return $stm;
    }

    public function insertOne($body, $adminUserId)
    {
        $stock = (int)($body['stock'] ?? 0);

        $stm = $this->pdo->prepare("
            INSERT INTO product (
                company_id,
                title,
                price,
                active,
                stock
            ) VALUES (
                {$body['company_id']},
                '{$body['title']}',
                {$body['price']},
                {$body['active']},
                {$stock}
            )
        ");
        if (!$stm->execute()) {
            return false;
        }

        $productId = $this->pdo->lastInsertId();

        $stm = $this->pdo->prepare("
            INSERT INTO product_category (
                product_id,
                cat_id
            ) VALUES (
                {$productId},
                {$body['category_id']}
            );
        ");
        if (!$stm->execute()) {
            return false;
        }

        $stm = $this->pdo->prepare("
            INSERT INTO product_log (
                product_id,
                admin_user_id,
                `action`
            ) VALUES (
                {$productId},
                {$adminUserId},
                'create'
            )
        ");

        return $stm->execute();
    }


    public function updateOne($id, $body, $adminUserId)
    {
        $stockUpdate = "";
        if (isset($body['stock'])) {
            $stockUpdate = ", stock = " . (int)$body['stock'];
        }

        $stm = $this->pdo->prepare("
            UPDATE product
            SET company_id = {$body['company_id']},
                title = '{$body['title']}',
                price = {$body['price']},
                active = {$body['active']}{$stockUpdate}
            WHERE id = {$id}
        ");
        if (!$stm->execute()) {
            return false;
        }

        $stm = $this->pdo->prepare("
            UPDATE product_category
            SET cat_id = {$body['category_id']}
            WHERE product_id = {$id}
        ");
        if (!$stm->execute()) {
            return false;
        }

        $stm = $this->pdo->prepare("
            INSERT INTO product_log (
                product_id,
                admin_user_id,
                `action`
            ) VALUES (
                {$id},
                {$adminUserId},
                'update'
            )
        ");

        return $stm->execute();
    }


    public function deleteOne($id, $adminUserId)
    {
        $stm = $this->pdo->prepare("
            DELETE FROM product_category WHERE product_id = {$id}
        ");
        if (!$stm->execute()) {
            return false;
        }

        $stm = $this->pdo->prepare("DELETE FROM product WHERE id = {$id}");
        if (!$stm->execute()) {
            return false;
        }

        $stm = $this->pdo->prepare("
            INSERT INTO product_log (
                product_id,
                admin_user_id,
                `action`
            ) VALUES (
                {$id},
                {$adminUserId},
                'delete'
            )
        ");

        return $stm->execute();
    }

    public function getLog($id)
    {
        $stm = $this->pdo->prepare("
            SELECT pl.*, au.name as admin_user_name
            FROM product_log pl
            LEFT JOIN admin_user au ON au.id = pl.admin_user_id
            WHERE pl.product_id = {$id}
        ");
        $stm->execute();

        return $stm;
    }

    private function getCompanyFromAdminUser($adminUserId)
    {
        $query = "
            SELECT company_id
            FROM admin_user
            WHERE id = {$adminUserId}
        ";
        $stm = $this->pdo->prepare($query);
        $stm->execute();

        return $stm->fetch()->company_id;
    }
}
