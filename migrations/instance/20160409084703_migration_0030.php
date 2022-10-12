<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0030 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('unit')
			->addColumn('creationDate', 'datetime', [
				'null' => false
			])
			->addColumn('modificationDate', 'datetime', [
				'null' => true
			])
			->addColumn('label', 'string', [
				'null' => false,
				'length' => 100
			])
			->save();

		$this->table('input')
			->addColumn('unit_id', 'integer', [
				'null' => true
			])
			->addForeignKey('unit_id', 'unit', 'id', [
				'delete' => 'RESTRICT'
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