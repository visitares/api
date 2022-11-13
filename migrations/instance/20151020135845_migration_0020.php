<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0020 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('formadmin')
			// Meta
			->addColumn('creationDate', 'datetime', [
				'null' => true
			])
			->addColumn('modificationDate', 'datetime', [
				'null' => true
			])

			// Data
			->addColumn('user_id', 'integer', ['signed' => true])
			->addColumn('form_id', 'integer', ['signed' => true])
			->addColumn('role', 'integer')

			// Foreign keys
			->addForeignKey('user_id', 'user', 'id', [
				'delete' => 'CASCADE'
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