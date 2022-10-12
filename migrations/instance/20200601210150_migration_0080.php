<?php

use Phinx\Migration\AbstractMigration;

class Migration0080 extends AbstractMigration{
  public function up(){
    $this
      ->table('form_media')
      ->addColumn('creationDate', 'timestamp', [
        'null' => false,
        'default' => 'CURRENT_TIMESTAMP',
      ])
      ->save();
    $this->execute('UPDATE form_media SET creationDate = DATE_SUB(NOW(), INTERVAL 14 DAY)');
  }
}