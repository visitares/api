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
		/**
		 * Update `form` table
		 */
		$this->table('attachment')
			// Data
			->addColumn('message_id', 'integer', ['signed' => true])
			->addColumn('creationDate', 'datetime', [
				'null' => true
			])
			->addColumn('modificationDate', 'datetime', [
				'null' => true
			])
			->addColumn('name', 'string', [
				'null' => false,
				'limit' => 250
			])
			->addColumn('mimetype', 'string', [
				'null' => false,
				'limit' => 100
			])
			->addColumn('size', 'integer', [
				'null' => false
			])
			->addColumn('data', 'binary', [
				'null' => false,
				'limit' => 16777215 // mediumblob
			])

			// Foreign Keys
			->addForeignKey('message_id', 'message', 'id', [
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