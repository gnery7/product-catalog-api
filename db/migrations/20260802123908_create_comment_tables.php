<?php

use Phinx\Migration\AbstractMigration;

class CreateCommentTables extends AbstractMigration
{
  public function up()
  {
    $comment = $this->table("comment");
    $comment
          ->addColumn('product_id', 'integer')
          ->addColumn('admin_user_id','integer')
          ->addColumn('parent_id', 'integer', ['null' => true, 'default' => null])
          ->addColumn('content', 'text')
          ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
          ->create();
    $commentLike = $this->table('comment_like');
    $commentLike
      ->addColumn('comment_id', 'integer')
      ->addColumn('admin_user_id', 'integer')
      ->addIndex(['comment_id', 'admin_user_id'], ['unique' => true])
      ->create();
  }

  public function down()
  {
    $this->table('comment_like')->drop()->save();
    $this->table('comment')->drop()->save();
  }
}