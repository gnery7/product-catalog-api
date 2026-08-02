<?php

namespace ContatoSeguro\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Contatoseguro\TesteBackend\Service\ProductService;

class ProductServiceTest extends TestCase
{
    private \PDO $pdo;
    private ProductService $service;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:', null, null, [
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
        ]);

        $this->pdo->exec("
            CREATE TABLE admin_user (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                company_id INTEGER,
                name TEXT
            )
        ");
        $this->pdo->exec("
            CREATE TABLE product (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                company_id INTEGER,
                title TEXT,
                price REAL,
                active INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                stock INTEGER DEFAULT 0
            )
        ");
        $this->pdo->exec("
            CREATE TABLE category (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                company_id INTEGER,
                title TEXT,
                active INTEGER
            )
        ");
        $this->pdo->exec("
            CREATE TABLE product_category (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_id INTEGER,
                cat_id INTEGER
            )
        ");
        $this->pdo->exec("
            CREATE TABLE category_translation (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                category_id INTEGER,
                lang_code TEXT,
                label TEXT
            )
        ");

        $this->pdo->exec("INSERT INTO admin_user (id, company_id, name) VALUES (1, 1, 'rivers')");
        $this->pdo->exec("INSERT INTO category (id, company_id, title, active) VALUES (1, 1, 'clothing', 1)");
        $this->pdo->exec("INSERT INTO category (id, company_id, title, active) VALUES (2, 1, 'house', 1)");

        $this->service = new ProductService($this->pdo);
    }

    private function insertProduct($title, $active, $stock)
    {
        $this->pdo->exec("
            INSERT INTO product (company_id, title, price, active, stock)
            VALUES (1, '{$title}', 10, {$active}, {$stock})
        ");

        return $this->pdo->lastInsertId();
    }

    private function linkProductToCategory($productId, $categoryId)
    {
        $this->pdo->exec("
            INSERT INTO product_category (product_id, cat_id)
            VALUES ({$productId}, {$categoryId})
        ");
    }

    public function testGetAllFiltersByActiveStatus(): void
    {
        $activeId = $this->insertProduct('produto ativo', 1, 5);
        $inactiveId = $this->insertProduct('produto inativo', 0, 5);
        $this->linkProductToCategory($activeId, 1);
        $this->linkProductToCategory($inactiveId, 1);

        $stm = $this->service->getAll(1, ['active' => '0']);
        $rows = $stm->fetchAll();

        $this->assertCount(1, $rows);
        $this->assertEquals('produto inativo', $rows[0]->title);
    }

    public function testGetAllFiltersByMinimumStock(): void
    {
        $lowStockId = $this->insertProduct('estoque baixo', 1, 2);
        $highStockId = $this->insertProduct('estoque alto', 1, 20);
        $this->linkProductToCategory($lowStockId, 1);
        $this->linkProductToCategory($highStockId, 1);

        $stm = $this->service->getAll(1, ['min_stock' => '10']);
        $rows = $stm->fetchAll();

        $this->assertCount(1, $rows);
        $this->assertEquals('estoque alto', $rows[0]->title);
    }

    public function testGetAllCategoryFilterSelectsProductWithoutAmputatingItsOtherCategories(): void
    {
        $multiCategoryId = $this->insertProduct('produto em duas categorias', 1, 5);
        $this->linkProductToCategory($multiCategoryId, 1);
        $this->linkProductToCategory($multiCategoryId, 2);

        $singleCategoryId = $this->insertProduct('produto em uma categoria', 1, 5);
        $this->linkProductToCategory($singleCategoryId, 1);

        $stm = $this->service->getAll(1, ['category' => 2]);
        $rows = $stm->fetchAll();

        $this->assertCount(2, $rows);
        $titles = array_column($rows, 'category_title');
        $this->assertContains('clothing', $titles);
        $this->assertContains('house', $titles);
    }

    public function testGetAllTranslatesCategoryAndFallsBackToOriginalWhenTranslationIsMissing(): void
    {
        $this->pdo->exec("
            INSERT INTO category_translation (category_id, lang_code, label)
            VALUES (1, 'pt', 'roupas')
        ");

        $clothingProductId = $this->insertProduct('produto de roupa', 1, 5);
        $this->linkProductToCategory($clothingProductId, 1);

        $houseProductId = $this->insertProduct('produto de casa', 1, 5);
        $this->linkProductToCategory($houseProductId, 2);

        $stm = $this->service->getAll(1, ['lang' => 'pt']);
        $rows = $stm->fetchAll();

        $byTitle = [];
        foreach ($rows as $row) {
            $byTitle[$row->title] = $row->category_title;
        }

        $this->assertEquals('roupas', $byTitle['produto de roupa']);
        $this->assertEquals('house', $byTitle['produto de casa']);
    }
}
