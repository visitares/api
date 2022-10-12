<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0019 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('attachment')
			->changeColumn('message_id', 'integer', [
				'null' => true
			])
			->addColumn('form_id', 'integer', [
				'null' => true,
				'after' => 'message_id'
			])
			->addForeignKey('form_id', 'form', 'id', [
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