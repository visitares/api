<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0016 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		/**
		 * comment table
		 */
		$this->table('groupcache')
			->removeIndex('group_id')
			->save();
		$this->table('usercache')
			->removeIndex('user_id')
			->save();
	}

	/**
	 * @return {void}
	 */
	public function down(){
		$this->dropTable('likes');
	}
}