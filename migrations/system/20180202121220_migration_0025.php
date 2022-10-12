<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0025 extends AbstractMigration{

	/**
	* @return void
	*/
	public function up(){

		$this->table('usercache_metagroup')
			->addColumn('notify', 'boolean', ['null' => false, 'default' => true])
			->save();

	}

}
