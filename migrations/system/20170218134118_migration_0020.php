<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0020 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){

		$this->table('usercache')
			->addColumn('salutation', 'integer', ['null' => true, 'after' => 'user_id'])
			->save();

		$this->table('usercache')
			->addColumn('title', 'integer', ['null' => true, 'after' => 'salutation'])
			->save();

		$this->table('usercache')
			->addColumn('company', 'string', ['null' => true, 'after' => 'lastname'])
			->save();

		$this->table('usercache')
			->addColumn('department', 'string', ['null' => true, 'after' => 'company'])
			->save();

	}

	/**
	 * @return {void}
	 */
	public function down(){
		// ..
	}
}