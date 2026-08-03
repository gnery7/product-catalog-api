<?php

use Phinx\Migration\AbstractMigration;

class CreateCategoryTranslationTable extends AbstractMigration
{
  public function up()
  {
    $table = $this->table('category_translation');
    $table
      ->addColumn('category_id', 'integer')
      ->addColumn('lang_code', 'string')
      ->addColumn('label', 'text')
      ->addIndex(['category_id', 'lang_code'], ['unique' => true])
      ->create();
  }

  public function down()
  {
    $this->table('category_translation')->drop()->save();
  }
}
