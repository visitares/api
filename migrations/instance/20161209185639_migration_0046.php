<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0046 extends AbstractMigration{
	/**
	 * @return void
	 */
	public function up(){
		$this->table('datalist')
			->addColumn('creationDate', 'datetime', [
				'null' => true
			])
			->addColumn('modificationDate', 'datetime', [
				'null' => true
			])
			->addColumn('name', 'string', [
				'length' => 100,
				'null' => false
			])
			->addColumn('value', 'text', [
				'null' => false
			])
			->save();
	}
}