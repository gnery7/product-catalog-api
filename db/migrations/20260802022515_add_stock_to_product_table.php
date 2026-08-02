<?php

use Phinx\Migration\AbstractMigration;

class AddStockToProductTable extends AbstractMigration
{
  public function up()
  {
    $this->table('product')
      ->addColumn('stock', 'integer', ['default' => 0, 'null' => false])
      ->update();
  }

  public function down()
  {
    $this->table('product')
    ->removeColumn('stock')
    ->update();
  }
}