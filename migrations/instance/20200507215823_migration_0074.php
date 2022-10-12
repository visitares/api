<?php

use Phinx\Migration\AbstractMigration;

class Migration0074 extends AbstractMigration{
  public function up(){
    $this
      ->table('attachment')
			->addColumn('sort', 'integer', [
				'null' => true
			])
      ->save();
  }
}