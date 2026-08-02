<?php

namespace Contatoseguro\TesteBackend\Service;

use Contatoseguro\TesteBackend\Config\DB;

class CategoryService
{
    private \PDO $pdo;
    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? DB::connect();
    }

    public function getAll($adminUserId, $lang = null)
    {
        $categoryTitle = "c.title";
        $translationJoin = "";
        $binds = [];
        if ($lang !== null) {
            $categoryTitle = "COALESCE(ct.label, c.title)";
            $translationJoin = " LEFT JOIN category_translation ct ON ct.category_id = c.id AND ct.lang_code = :lang";
            $binds[':lang'] = $lang;
        }
        $query = "
            SELECT c.id, c.company_id, {$categoryTitle} as title, c.active
            FROM category c{$translationJoin}
            WHERE (c.company_id = {$this->getCompanyFromAdminUser($adminUserId)} OR c.company_id IS NULL)
        ";

        $stm = $this->pdo->prepare($query);
        $stm->execute($binds);

        return $stm;
    }


    public function getOne($adminUserId, $categoryId, $lang = null)
    {
        $categoryTitle = "c.title";
        $translationJoin = "";
        $binds = [];
        if ($lang !== null) {
            $categoryTitle = "COALESCE(ct.label, c.title)";
            $translationJoin = " LEFT JOIN category_translation ct ON ct.category_id = c.id AND ct.lang_code = :lang";
            $binds[':lang'] = $lang;
        }
        $query = "
            SELECT c.id, c.company_id, {$categoryTitle} as title, c.active
            FROM category c{$translationJoin}
            WHERE c.active = 1
            AND (c.company_id = {$this->getCompanyFromAdminUser($adminUserId)} OR c.company_id IS NULL)
            AND c.id = {$categoryId}
        ";

        $stm = $this->pdo->prepare($query);
        $stm->execute($binds);

        return $stm;
    }


    public function getProductCategory($adminUserId, $productId, $lang = null)
    {
        $categoryTitle = "c.title";
        $translationJoin = "";
        $binds = [];
        if ($lang !== null) {
            $categoryTitle = "COALESCE(ct.label, c.title)";
            $translationJoin = " LEFT JOIN category_translation ct ON ct.category_id = c.id AND ct.lang_code = :lang";
            $binds[':lang'] = $lang;
        }
        $query = "
            SELECT c.id, {$categoryTitle} as title
            FROM category c
            INNER JOIN product_category pc ON pc.cat_id = c.id{$translationJoin}
            WHERE pc.product_id = {$productId}
            AND c.active = 1
            AND (c.company_id = {$this->getCompanyFromAdminUser($adminUserId)} OR c.company_id IS NULL)
        ";

        $stm = $this->pdo->prepare($query);
        $stm->execute($binds);

        return $stm;
    }

    public function insertOne($body, $adminUserId)
    {
        $stm = $this->pdo->prepare("
            INSERT INTO category (
                company_id,
                title,
                active
            ) VALUES (
                {$this->getCompanyFromAdminUser($adminUserId)},
                '{$body['title']}',
                {$body['active']}
            )
        ");

        return $stm->execute();
    }
    public function insertTranslations($categoryId, $translations)
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

    public function updateOne($id, $body, $adminUserId)
    {
        $active = (int)$body['active'];

        $stm = $this->pdo->prepare("
            UPDATE category
            SET title = '{$body['title']}',
                active = {$active}
            WHERE id = {$id}
            AND company_id = {$this->getCompanyFromAdminUser($adminUserId)}
        ");

        return $stm->execute();
    }

    public function deleteOne($id, $adminUserId)
    {
        $stm = $this->pdo->prepare("
            DELETE
            FROM category
            WHERE id = {$id}
            AND company_id = {$this->getCompanyFromAdminUser($adminUserId)}
        ");

        return $stm->execute();
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
