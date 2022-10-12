<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0024 extends AbstractMigration{

	/**
	* @return void
	*/
	public function up(){

		$this->table('jobs')
			->addColumn('creationDate', 'datetime', [ 'null' => true ])
			->addColumn('modificationDate', 'datetime', [ 'null' => true ])
			->addColumn('sleepUntil', 'datetime', [ 'null' => true ])
			->addColumn('expiresOn', 'datetime', [ 'null' => true ])
			->addColumn('type', 'string', ['null' => false, 'length' => 200])
			->addColumn('status', 'integer', ['null' => false, 'default' => 0])
			->addColumn('priority', 'integer', ['null' => false, 'default' => 0])
			->addColumn('payload', 'string', ['null' => true, 'limit' => MysqlAdapter::TEXT_MEDIUM])
			->save();

		$this->table('workers')
			->addColumn('creationDate', 'datetime', [ 'null' => true ])
			->addColumn('modificationDate', 'datetime', [ 'null' => true ])
			->addColumn('type', 'string', ['null' => false, 'length' => 200])
			->addColumn('isActive', 'integer', ['null' => false, 'default' => 0])
			->addColumn('maxInstances', 'integer', ['null' => false, 'default' => 0])
			->save();

	}

}
