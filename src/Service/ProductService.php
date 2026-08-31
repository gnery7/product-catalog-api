<?php

namespace Contatoseguro\TesteBackend\Service;

use Contatoseguro\TesteBackend\Config\DB;
use Contatoseguro\TesteBackend\Exception\InvalidCategoryException;
use Contatoseguro\TesteBackend\Exception\InvalidCompanyException;
use Contatoseguro\TesteBackend\Service\Concerns\BuildsCategoryTranslationClause;

class ProductService
{
    use BuildsCategoryTranslationClause;

    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? DB::connect();
    }

    public function getAll($adminUserId, $filters = []): \PDOStatement
    {
        $clause = $this->buildCategoryTranslationClause($filters['lang'] ?? null);
        $binds = $clause['binds'];
        $binds[':company_id'] = $this->getCompanyFromAdminUser($adminUserId);

        $query = "
            SELECT p.*, {$clause['titleExpr']} as category_title
            FROM product p
            INNER JOIN product_category pc ON pc.product_id = p.id
            INNER JOIN category c ON c.id = pc.cat_id{$clause['join']}
            WHERE p.company_id = :company_id
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
            $direction = strtoupper((string)$filters['order']) === 'DESC' ? 'DESC' : 'ASC';
            $query .= " ORDER BY p.created_at {$direction}";
        }

        $stm = $this->pdo->prepare($query);
        $stm->execute($binds);

        return $stm;
    }

    public function getOne($id, $adminUserId): \PDOStatement
    {
        $stm = $this->pdo->prepare("
            SELECT *
            FROM product
            WHERE id = :id
            AND company_id = :company_id
        ");
        $stm->execute([
            ':id' => $id,
            ':company_id' => $this->getCompanyFromAdminUser($adminUserId),
        ]);

        return $stm;
    }


    public function insertOne($body, $adminUserId): bool
    {
        $companyId = $this->getCompanyFromAdminUser($adminUserId);

        if ((int)$body['company_id'] !== (int)$companyId) {
            throw new InvalidCompanyException();
        }

        $stm = $this->pdo->prepare("SELECT id FROM category WHERE id = :category_id");
        $stm->execute([':category_id' => $body['category_id']]);
        if ($stm->fetch() === false) {
            throw new InvalidCategoryException();
        }

        $stock = (int)($body['stock'] ?? 0);

        $stm = $this->pdo->prepare("
            INSERT INTO product (
                company_id,
                title,
                price,
                active,
                stock
            ) VALUES (
                :company_id,
                :title,
                :price,
                :active,
                :stock
            )
        ");
        $binds = [
            ':company_id' => $body['company_id'],
            ':title' => $body['title'],
            ':price' => $body['price'],
            ':active' => $body['active'],
            ':stock' => $stock,
        ];
        if (!$stm->execute($binds)) {
            return false;
        }

        $productId = $this->pdo->lastInsertId();

        $stm = $this->pdo->prepare("
            INSERT INTO product_category (
                product_id,
                cat_id
            ) VALUES (
                :product_id,
                :cat_id
            )
        ");
        $binds = [
            ':product_id' => $productId,
            ':cat_id' => $body['category_id'],
        ];
        if (!$stm->execute($binds)) {
            return false;
        }

        $stm = $this->pdo->prepare("
            INSERT INTO product_log (
                product_id,
                admin_user_id,
                `action`
            ) VALUES (
                :product_id,
                :admin_user_id,
                'create'
            )
        ");

        return $stm->execute([
            ':product_id' => $productId,
            ':admin_user_id' => $adminUserId,
        ]);
    }

    public function updateOne($id, $body, $adminUserId): bool
    {
        $companyId = $this->getCompanyFromAdminUser($adminUserId);

        $stm = $this->pdo->prepare("
            SELECT id
            FROM product
            WHERE id = :id
            AND company_id = :company_id
        ");
        $stm->execute([':id' => $id, ':company_id' => $companyId]);
        if ($stm->fetch() === false) {
            return false;
        }

        $binds = [
            ':new_company_id' => $body['company_id'],
            ':title' => $body['title'],
            ':price' => $body['price'],
            ':active' => $body['active'],
            ':id' => $id,
            ':company_id' => $companyId,
        ];

        $stockUpdate = "";
        if (isset($body['stock'])) {
            $stockUpdate = ", stock = :stock";
            $binds[':stock'] = (int)$body['stock'];
        }

        $stm = $this->pdo->prepare("
            UPDATE product
            SET company_id = :new_company_id,
                title = :title,
                price = :price,
                active = :active{$stockUpdate}
            WHERE id = :id
            AND company_id = :company_id
        ");
        if (!$stm->execute($binds)) {
            return false;
        }

        $stm = $this->pdo->prepare("
            UPDATE product_category
            SET cat_id = :cat_id
            WHERE product_id = :product_id
        ");
        if (!$stm->execute([':cat_id' => $body['category_id'], ':product_id' => $id])) {
            return false;
        }

        $stm = $this->pdo->prepare("
            INSERT INTO product_log (
                product_id,
                admin_user_id,
                `action`
            ) VALUES (
                :product_id,
                :admin_user_id,
                'update'
            )
        ");

        return $stm->execute([
            ':product_id' => $id,
            ':admin_user_id' => $adminUserId,
        ]);
    }


    public function deleteOne($id, $adminUserId): bool
    {
        $companyId = $this->getCompanyFromAdminUser($adminUserId);

        $stm = $this->pdo->prepare("
            SELECT id
            FROM product
            WHERE id = :id
            AND company_id = :company_id
        ");
        $stm->execute([':id' => $id, ':company_id' => $companyId]);
        if ($stm->fetch() === false) {
            return false;
        }

        $stm = $this->pdo->prepare("
            DELETE FROM product_category WHERE product_id = :id
        ");
        if (!$stm->execute([':id' => $id])) {
            return false;
        }

        $stm = $this->pdo->prepare("DELETE FROM product WHERE id = :id");
        if (!$stm->execute([':id' => $id])) {
            return false;
        }

        $stm = $this->pdo->prepare("
            INSERT INTO product_log (
                product_id,
                admin_user_id,
                `action`
            ) VALUES (
                :id,
                :admin_user_id,
                'delete'
            )
        ");

        return $stm->execute([
            ':id' => $id,
            ':admin_user_id' => $adminUserId,
        ]);
    }

    public function getLog($id): \PDOStatement
    {
        $stm = $this->pdo->prepare("
            SELECT pl.*, au.name as admin_user_name
            FROM product_log pl
            LEFT JOIN admin_user au ON au.id = pl.admin_user_id
            WHERE pl.product_id = :id
        ");
        $stm->execute([':id' => $id]);

        return $stm;
    }

    private function getCompanyFromAdminUser($adminUserId)
    {
        $stm = $this->pdo->prepare("
            SELECT company_id
            FROM admin_user
            WHERE id = :id
        ");
        $stm->execute([':id' => $adminUserId]);

        return $stm->fetch()->company_id;
    }
}
