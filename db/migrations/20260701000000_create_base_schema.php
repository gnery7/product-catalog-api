<?php

use Phinx\Migration\AbstractMigration;

class CreateBaseSchema extends AbstractMigration
{
  public function up()
  {
    if (!$this->hasTable('company')) {
      $this->table('company')
        ->addColumn('name', 'string', ['null' => false])
        ->addColumn('active', 'boolean', ['null' => false])
        ->addIndex('name', ['unique' => true])
        ->create();
    }

    if (!$this->hasTable('admin_user')) {
      $this->table('admin_user')
        ->addColumn('company_id', 'integer', ['null' => true])
        ->addColumn('email', 'string', ['null' => false])
        ->addColumn('name', 'string', ['null' => false])
        ->addIndex('email', ['unique' => true])
        ->create();
    }

    if (!$this->hasTable('category')) {
      $this->table('category')
        ->addColumn('company_id', 'integer', ['null' => true])
        ->addColumn('title', 'string', ['null' => false])
        ->addColumn('active', 'boolean', ['null' => false])
        ->create();
    }

    if (!$this->hasTable('product')) {
      $this->table('product')
        ->addColumn('company_id', 'integer', ['null' => false])
        ->addColumn('title', 'string', ['null' => false])
        ->addColumn('price', 'float', ['null' => true])
        ->addColumn('active', 'boolean', ['null' => false])
        ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
        ->create();
    }

    if (!$this->hasTable('product_category')) {
      $this->table('product_category')
        ->addColumn('cat_id', 'integer', ['null' => false])
        ->addColumn('product_id', 'integer', ['null' => false])
        ->create();
    }

    if (!$this->hasTable('product_log')) {
      $this->table('product_log')
        ->addColumn('product_id', 'integer', ['null' => false])
        ->addColumn('admin_user_id', 'integer', ['null' => false])
        ->addColumn('action', 'string', ['null' => false])
        ->addColumn('timestamp', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
        ->create();
    }
  }

  public function down()
  {
    if ($this->hasTable('product_log')) {
      $this->table('product_log')->drop()->save();
    }
    if ($this->hasTable('product_category')) {
      $this->table('product_category')->drop()->save();
    }
    if ($this->hasTable('product')) {
      $this->table('product')->drop()->save();
    }
    if ($this->hasTable('category')) {
      $this->table('category')->drop()->save();
    }
    if ($this->hasTable('admin_user')) {
      $this->table('admin_user')->drop()->save();
    }
    if ($this->hasTable('company')) {
      $this->table('company')->drop()->save();
    }
  }
}
