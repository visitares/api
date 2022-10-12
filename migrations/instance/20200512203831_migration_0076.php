<?php

use Phinx\Migration\AbstractMigration;

class Migration0076 extends AbstractMigration{
  public function up(){

    $this
      ->table('usersubmitinstance')
      ->addColumn('instructedForm_id', 'integer', [
        'default' => null,
        'null' => true,
      ])
      ->addForeignKey('instructedForm_id', 'form', 'id', [
        'delete'=> 'SET_NULL',
        'update'=> 'RESTRICT'
      ])
      ->save();

  }
}