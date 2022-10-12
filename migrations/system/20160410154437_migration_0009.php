<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0009 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('config')
			->addColumn('creationDate', 'datetime', [
				'null' => false
			])
			->addColumn('modificationDate', 'datetime', [
				'null' => true
			])
			->addColumn('name', 'string', [
				'null' => false,
				'length' => 100
			])
			->addColumn('value', 'text', [
				'null' => true
			])
			->save();
	}

	/**
	 * @return {void}
	 */
	public function down(){
		$this->dropTable('config');
	}
}