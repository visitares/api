<?php

use Phinx\Migration\AbstractMigration;

class Migration0078 extends AbstractMigration{
  public function up(){

    $this->table('user')
      ->renameColumn('password', 'password_old')
      ->addColumn('password', 'string', [
        'null' => false,
        'after' => 'username',
        'default' => '',
      ])
      ->changeColumn('password_old', 'string', [
        'null' => true,
        'default' => '',
      ])
      ->save();

  }
}