<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0018 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('post')
			->addColumn('metagroup_id', 'integer', [
				'null' => true,
				'after' => 'user_id'
			])
			->addForeignKey('metagroup_id', 'metagroup', 'id', [
				'delete' => 'SET_NULL'
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