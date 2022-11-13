<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0003 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('submit')
			->removeColumn('message')
			->save();

		$this->table('message')
			->addColumn('submit_id', 'integer', [
				'signed' => true,
				'after' => 'user_id',
				'null' => true
			])
			->addForeignKey('submit_id', 'submit', 'id', [
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