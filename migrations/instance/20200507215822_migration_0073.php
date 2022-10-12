<?php

use Phinx\Migration\AbstractMigration;

class Migration0073 extends AbstractMigration{
  public function up(){
    $this
      ->table('client')
			->addColumn('dividingLine', 'boolean', [
				'null' => false,
				'default' => false
			])
      ->save();
    $this
      ->table('category')
      ->addColumn('dividingLine', 'boolean', [
        'null' => false,
        'default' => false
      ])
      ->save();
  }
}