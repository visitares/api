<?php

use Phinx\Migration\AbstractMigration;

class Migration0072 extends AbstractMigration{
  public function up(){
    $this
      ->table('input')
      ->addColumn('type', 'string', [
        'default' => 'text',
        'null' => false,
      ])
      ->save();
  }
}