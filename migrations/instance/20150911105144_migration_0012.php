<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0012 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('user')
			->addColumn('anonymous', 'boolean', [
				'null' => false,
				'default' => false
			])
			->addColumn('anonymousToken', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 64
			])
			->save();

		$this->table('category')
			->addColumn('beginDate', 'date', [
				'null' => true,
				'default' => null,
				'after' => 'isActive'
			])
			->addColumn('endDate', 'date', [
				'null' => true,
				'default' => null,
				'after' => 'beginDate'
			])
			->addColumn('inputLockHours', 'integer', [
				'null' => false,
				'default' => 24,
				'after' => 'isCopy'
			])
			->save();
	}

	/**
	 * @return {void}
	 */
	public function down(){
		// ..
	}
}