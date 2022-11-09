<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0004 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		/**
		 * Update `user` table
		 */
		$this->table('user')
			// Change fk delete action to `restrict`
			->dropForeignKey('language_id')
			->addForeignKey('language_id', 'language', 'id', [
				'delete' => 'RESTRICT'
			])

			->save();

		/**
		 * Update `unread` table
		 */
		$this->table('unread')
			// Change fk delete action to `cascade`
			->dropForeignKey('user_id')
			->addForeignKey('user_id', 'user', 'id', [
				'delete' => 'CASCADE'
			])

			// Add refrence to `submit` table
			->addColumn('submit_id', 'integer', [
				'signed' => false,
				'after' => 'user_id',
				'null' => false
			])
			->addForeignKey('submit_id', 'submit', 'id', [
				'delete' => 'CASCADE'
			])

			// Add counter for unread messages
			->addColumn('count', 'integer', [
				'default' => 1
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