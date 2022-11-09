<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0013 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('submit_group')
			->addColumn('submit_id', 'integer', ['signed' => false])
			->addColumn('group_id', 'integer', ['signed' => false])

			->addForeignKey('submit_id', 'submit', 'id', [
				'delete' => 'CASCADE'
			])

			->addForeignKey('group_id', 'usergroup', 'id', [
				'delete' => 'CASCADE'
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