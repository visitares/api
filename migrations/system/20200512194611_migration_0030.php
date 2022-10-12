<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0030 extends AbstractMigration{

  /**
  * @return void
  */
  public function up(){
    $this->table('instance')
      ->addColumn('allowInstructions', 'boolean', [
        'null' => false,
        'default' => false
      ])
      ->save();
  }

}
