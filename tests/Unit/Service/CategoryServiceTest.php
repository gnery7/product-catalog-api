<?php

namespace ContatoSeguro\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Contatoseguro\TesteBackend\Service\CategoryService;

class CategoryServiceTest extends TestCase
{
    private \PDO $pdo;
    private CategoryService $service;

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
            CREATE TABLE category (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                company_id INTEGER,
                title TEXT,
                active INTEGER
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
        $this->pdo->exec("
            CREATE UNIQUE INDEX category_translation_category_id_lang_code_index
            ON category_translation (category_id, lang_code)
        ");

        $this->pdo->exec("INSERT INTO admin_user (id, company_id, name) VALUES (1, 1, 'rivers')");

        $this->service = new CategoryService($this->pdo);
    }

    public function testInsertTranslationsPersistsAllWhenValid(): void
    {
        $result = $this->service->insertTranslations(1, [
            ['lang_code' => 'en', 'label' => 'clothes'],
            ['lang_code' => 'pt', 'label' => 'roupas'],
        ]);

        $this->assertTrue($result);

        $count = $this->pdo->query("SELECT COUNT(*) FROM category_translation")->fetchColumn();
        $this->assertEquals(2, $count);
    }

    public function testInsertTranslationsRollsBackAllWhenOneIsDuplicate(): void
    {
        $result = $this->service->insertTranslations(1, [
            ['lang_code' => 'pt', 'label' => 'telefones'],
            ['lang_code' => 'en', 'label' => 'phones'],
            ['lang_code' => 'pt', 'label' => 'fones'],
        ]);

        $this->assertFalse($result);

        $count = $this->pdo->query("SELECT COUNT(*) FROM category_translation")->fetchColumn();
        $this->assertEquals(0, $count);
    }
    public function testUpdateOneReturnsFalseWhenCategoryDoesNotExist(): void
    {
        $body = [
            'title' => 'categoria qualquer',
            'active' => 1,
        ];

        $result = $this->service->updateOne(999, $body, 1);

        $this->assertFalse($result);
    }

    public function testDeleteOneReturnsFalseWhenCategoryDoesNotExist(): void
    {
        $result = $this->service->deleteOne(999, 1);

        $this->assertFalse($result);
    }
}
