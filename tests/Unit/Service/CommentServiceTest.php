<?php

namespace ContatoSeguro\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Contatoseguro\TesteBackend\Service\CommentService;

class CommentServiceTest extends TestCase
{
    private \PDO $pdo;
    private CommentService $service;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:', null, null, [
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
        ]);
        $this->pdo->exec("
            CREATE TABLE product (
                id INTEGER PRIMARY KEY AUTOINCREMENT
            )
        ");
        $this->pdo->exec("INSERT INTO product (id) VALUES (1)");
        $this->pdo->exec("INSERT INTO product (id) VALUES (7)");
        $this->pdo->exec("
            CREATE TABLE comment (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_id INTEGER,
                admin_user_id INTEGER,
                parent_id INTEGER,
                content TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $this->pdo->exec("
            CREATE TABLE comment_like (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                comment_id INTEGER,
                admin_user_id INTEGER
            )
        ");
        $this->pdo->exec("
            CREATE UNIQUE INDEX comment_like_comment_id_admin_user_id_index
            ON comment_like (comment_id, admin_user_id)
        ");

        $this->service = new CommentService($this->pdo);
    }


    public function testInsertReplyInheritsProductFromParent(): void
    {
        $this->service->insertOne(7, 1, 'comentario raiz');
        $parentId = $this->pdo->lastInsertId();

        $result = $this->service->insertReply($parentId, 2, 'resposta');

        $this->assertTrue($result);

        $reply = $this->pdo->query("SELECT * FROM comment WHERE parent_id = {$parentId}")->fetch();
        $this->assertEquals(7, $reply->product_id);
        $this->assertEquals($parentId, $reply->parent_id);
    }

    public function testInsertReplyReturnsFalseWhenParentDoesNotExist(): void
    {
        $result = $this->service->insertReply(999, 1, 'resposta pro nada');

        $this->assertFalse($result);
    }

    public function testInsertOneReturnsFalseWhenProductDoesNotExist(): void
    {
        $result = $this->service->insertOne(999, 1, 'comentario pro nada');

        $this->assertFalse($result);
    }

    public function testInsertLikeReturnsDuplicadoOnSecondLikeFromSameUser(): void
    {
        $this->service->insertOne(1, 1, 'comentario');
        $commentId = $this->pdo->lastInsertId();

        $firstLike = $this->service->insertLike($commentId, 2);
        $secondLike = $this->service->insertLike($commentId, 2);

        $this->assertTrue($firstLike);
        $this->assertEquals('duplicado', $secondLike);
    }

    public function testDeleteOneReturnsProibidoWhenUserIsNotTheAuthor(): void
    {
        $this->service->insertOne(1, 1, 'comentario do rivers');
        $commentId = $this->pdo->lastInsertId();

        $result = $this->service->deleteOne($commentId, 2);

        $this->assertEquals('proibido', $result);

        $count = $this->pdo->query("SELECT COUNT(*) FROM comment")->fetchColumn();
        $this->assertEquals(1, $count);
    }

    public function testDeleteOneCascadesToRepliesAndLikes(): void
    {
        $this->service->insertOne(1, 1, 'comentario raiz');
        $rootId = $this->pdo->lastInsertId();

        $this->service->insertReply($rootId, 2, 'resposta');
        $replyId = $this->pdo->lastInsertId();

        $this->service->insertReply($replyId, 1, 'resposta da resposta');

        $this->service->insertLike($rootId, 2);

        $result = $this->service->deleteOne($rootId, 1);

        $this->assertTrue($result);

        $commentCount = $this->pdo->query("SELECT COUNT(*) FROM comment")->fetchColumn();
        $likeCount = $this->pdo->query("SELECT COUNT(*) FROM comment_like")->fetchColumn();

        $this->assertEquals(0, $commentCount);
        $this->assertEquals(0, $likeCount);
    }
    public function testGetByProductReturnsFalseWhenProductDoesNotExist(): void
    {
        $result = $this->service->getByProduct(999);

        $this->assertFalse($result);
    }
}