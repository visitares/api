<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0045 extends AbstractMigration{
	/**
	 * @return void
	 */
	public function up(){
		$this->table('submit')
			->addColumn('submitinstance_id', 'integer', [
				'null' => true,
				'after' => 'language_id'
			])
			->addForeignKey('submitinstance_id', 'usersubmitinstance', 'id', [
				'delete' => 'SET_NULL'
			])
			->save();
	}
}