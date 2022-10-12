<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0029 extends AbstractMigration{

  /**
  * @return void
  */
  public function up(){
    $this->table('instance')
      ->addColumn('showFormSearch', 'boolean', [
        'null' => false,
        'default' => true
      ])
      ->addColumn('showFormSearchShortDescription', 'boolean', [
        'null' => false,
        'default' => true
      ])
      ->addColumn('showFormSearchDescription', 'boolean', [
        'null' => false,
        'default' => true
      ])
      ->save();
  }

}
