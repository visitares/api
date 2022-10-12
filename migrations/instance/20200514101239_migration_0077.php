<?php

use Phinx\Migration\AbstractMigration;

class Migration0077 extends AbstractMigration{
  public function up(){

    $this
      ->table('usersubmitinstance')
      ->addColumn('instructionByName', 'string', [
        'default' => null,
        'null' => true,
      ])
      ->addColumn('instructionCompany', 'string', [
        'default' => null,
        'null' => true,
      ])
      ->addColumn('instructionLocation', 'string', [
        'default' => null,
        'null' => true,
      ])
      ->save();

  }
}