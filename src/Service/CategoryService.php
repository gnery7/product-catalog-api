<?php

namespace Contatoseguro\TesteBackend\Service;

use Contatoseguro\TesteBackend\Config\DB;
use Contatoseguro\TesteBackend\Service\Concerns\BuildsCategoryTranslationClause;

class CategoryService
{
    use BuildsCategoryTranslationClause;

    private \PDO $pdo;
    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? DB::connect();
    }

    public function getAll($adminUserId, $lang = null): \PDOStatement
    {
        $clause = $this->buildCategoryTranslationClause($lang);
        $binds = $clause['binds'];
        $binds[':company_id'] = $this->getCompanyFromAdminUser($adminUserId);

        $query = "
            SELECT c.id, c.company_id, {$clause['titleExpr']} as title, c.active
            FROM category c{$clause['join']}
            WHERE (c.company_id = :company_id OR c.company_id IS NULL)
        ";

        $stm = $this->pdo->prepare($query);
        $stm->execute($binds);

        return $stm;
    }


    public function getOne($adminUserId, $categoryId, $lang = null): \PDOStatement
    {
        $clause = $this->buildCategoryTranslationClause($lang);
        $binds = $clause['binds'];
        $binds[':company_id'] = $this->getCompanyFromAdminUser($adminUserId);
        $binds[':category_id'] = $categoryId;

        $query = "
            SELECT c.id, c.company_id, {$clause['titleExpr']} as title, c.active
            FROM category c{$clause['join']}
            WHERE c.active = 1
            AND (c.company_id = :company_id OR c.company_id IS NULL)
            AND c.id = :category_id
        ";

        $stm = $this->pdo->prepare($query);
        $stm->execute($binds);

        return $stm;
    }


    public function getProductCategory($adminUserId, $productId, $lang = null): \PDOStatement
    {
        $clause = $this->buildCategoryTranslationClause($lang);
        $binds = $clause['binds'];
        $binds[':company_id'] = $this->getCompanyFromAdminUser($adminUserId);
        $binds[':product_id'] = $productId;

        $query = "
            SELECT c.id, {$clause['titleExpr']} as title
            FROM category c
            INNER JOIN product_category pc ON pc.cat_id = c.id{$clause['join']}
            WHERE pc.product_id = :product_id
            AND c.active = 1
            AND (c.company_id = :company_id OR c.company_id IS NULL)
        ";

        $stm = $this->pdo->prepare($query);
        $stm->execute($binds);

        return $stm;
    }

    public function insertOne($body, $adminUserId): bool
    {
        $stm = $this->pdo->prepare("
            INSERT INTO category (
                company_id,
                title,
                active
            ) VALUES (
                :company_id,
                :title,
                :active
            )
        ");

        return $stm->execute([
            ':company_id' => $this->getCompanyFromAdminUser($adminUserId),
            ':title' => $body['title'],
            ':active' => $body['active'],
        ]);
    }
    public function insertTranslations($categoryId, $translations): bool
    {
        $this->pdo->beginTransaction();
        $stm = $this->pdo->prepare("
            INSERT INTO category_translation (
                category_id,
                lang_code,
                label
            ) VALUES (
                :category_id,
                :lang_code,
                :label
            )
        ");
        try {
            foreach ($translations as $translation) {
                $stm->execute([
                    ':category_id' => $categoryId,
                    ':lang_code' => $translation['lang_code'],
                    ':label' => $translation['label'],
                ]);
            }
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            return false;
        }
        $this->pdo->commit();
        return true;
    }

    public function updateOne($id, $body, $adminUserId): bool
    {
        $companyId = $this->getCompanyFromAdminUser($adminUserId);

        $stm = $this->pdo->prepare("
            SELECT id
            FROM category
            WHERE id = :id
            AND company_id = :company_id
        ");
        $stm->execute([':id' => $id, ':company_id' => $companyId]);
        if ($stm->fetch() === false) {
            return false;
        }

        $active = (int)$body['active'];

        $stm = $this->pdo->prepare("
            UPDATE category
            SET title = :title,
                active = :active
            WHERE id = :id
            AND company_id = :company_id
        ");

        return $stm->execute([
            ':title' => $body['title'],
            ':active' => $active,
            ':id' => $id,
            ':company_id' => $companyId,
        ]);
    }

    public function deleteOne($id, $adminUserId): bool
    {
        $companyId = $this->getCompanyFromAdminUser($adminUserId);

        $stm = $this->pdo->prepare("
            SELECT id
            FROM category
            WHERE id = :id
            AND company_id = :company_id
        ");
        $stm->execute([':id' => $id, ':company_id' => $companyId]);
        if ($stm->fetch() === false) {
            return false;
        }

        $stm = $this->pdo->prepare("
            DELETE
            FROM category
            WHERE id = :id
            AND company_id = :company_id
        ");

        return $stm->execute([':id' => $id, ':company_id' => $companyId]);
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
