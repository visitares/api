<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0027 extends AbstractMigration{

  /**
  * @return void
  */
  public function up(){
    $this->table('instance')
      ->addColumn('showAppAnonymousButton', 'boolean', [
        'null' => false,
        'default' => true
      ])
      ->addColumn('showAppUserSettings', 'boolean', [
        'null' => false,
        'default' => true
      ])
      ->addColumn('showAppLogout', 'boolean', [
        'null' => false,
        'default' => true
      ])
      ->save();
  }

}
