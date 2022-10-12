<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0028 extends AbstractMigration{

  /**
  * @return void
  */
  public function up(){
    $this->table('instance')
      ->addColumn('appDefaultUserMode', 'string', [
        'null' => false,
        'default' => 'anonymous'
      ])
      ->save();
  }

}
