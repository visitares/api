<?php

use Phinx\Migration\AbstractMigration;

class Migration0075 extends AbstractMigration{
  public function up(){

    $this
      ->table('usersubmitinstance')
      ->addColumn('isInstructed', 'boolean', [
        'default' => false,
        'null' => false,
      ])
      ->addColumn('webinstructor_id', 'integer', [
        'default' => null,
        'null' => true,
      ])
      ->addForeignKey('webinstructor_id', 'user', 'id', [
        'delete'=> 'SET_NULL',
        'update'=> 'RESTRICT'
      ])
      ->save();

    $this
      ->table('catalog')
      ->addColumn('allowInstructions', 'boolean', [
        'default' => false,
        'null' => false,
      ])
      ->save();

  }
}