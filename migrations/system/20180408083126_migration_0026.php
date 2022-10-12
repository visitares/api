<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0026 extends AbstractMigration{

	/**
	* @return void
	*/
	public function up(){
		$this->table('instance')
			->addColumn('appSendDeeplinks', 'boolean', ['null' => false, 'default' => true])
			->save();
	}

}
