<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0015 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		/**
		 * comment table
		 */
		$this->table('likes')
			->addColumn('post_id', 'integer', [ 'signed' => true, 'null' => false ])
			->addColumn('user_id', 'integer', [ 'signed' => true, 'null' => false ])
			->addForeignKey('post_id', 'post', 'id', [ 'delete' => 'CASCADE' ])
			->addForeignKey('user_id', 'usercache', 'id', [ 'delete' => 'CASCADE' ])
			->save();
	}

	/**
	 * @return {void}
	 */
	public function down(){
		$this->dropTable('likes');
	}
}