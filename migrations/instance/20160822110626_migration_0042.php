<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0042 extends AbstractMigration{
	/**
	 * @return void
	 */
	public function up(){

		$this->table('mediagroup')
			->addColumn('creationDate', 'datetime', [
				'null' => false
			])
			->addColumn('modificationDate', 'datetime', [
				'null' => true
			])
			->addColumn('label', 'string', [
				'length' => 200,
				'null' => true
			])
			->addColumn('description', 'text', [
				'null' => true
			])
			->save();

		$this->table('media')
			->addColumn('group_id', 'integer', [
				'signed' => true,
				'null' => true,
				'after' => 'modificationDate'
			])
			->addForeignKey('group_id', 'mediagroup', 'id', [
				'delete' => 'SET_NULL'
			])
			->save();
	}
}