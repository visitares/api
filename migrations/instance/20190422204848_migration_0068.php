<?php

use Phinx\Migration\AbstractMigration;

class Migration0068 extends AbstractMigration{
  public function change(){
    $this->table('form')
      ->addColumn('catalogEntry_id', 'integer', [
        'signed' => true,
        'null' => true,
        'after' => 'category_id',
      ])
      ->addForeignKey('catalogEntry_id', 'catalog_entry', 'id', [
        'delete' => 'CASCADE'
      ])
      ->save();
  }
}