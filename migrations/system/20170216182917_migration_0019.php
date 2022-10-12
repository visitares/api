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
		$this->table('instance')
			->addColumn('defaultRegistrationRole', 'integer', [
				'null' => false,
				'default' => 3 // app-admin
			])
			->save();

		$this->table('usercache_metagroup', [
			'id' => false,
			'primary_key' => [
				'user_id',
				'metaGroup_id'
			]
		])
			->addColumn('user_id', 'integer', ['null' => false])
			->addColumn('metaGroup_id', 'integer', ['null' => false])
			->addForeignKey('user_id', 'usercache', 'id', [
				'delete' => 'CASCADE'
			])
			->addForeignKey('metaGroup_id', 'metagroup', 'id', [
				'delete' => 'CASCADE'
			])
			->save();

		$this->table('post_metagroup', [
			'id' => false,
			'primary_key' => [
				'post_id',
				'metaGroup_id'
			]
		])
			->addColumn('post_id', 'integer', [ 'null' => false ])
			->addColumn('metaGroup_id', 'integer', [ 'null' => false ])
			->addForeignKey('post_id', 'post', 'id', [ 'delete' => 'CASCADE' ])
			->addForeignKey('metaGroup_id', 'metagroup', 'id', [ 'delete' => 'CASCADE' ])
			->save();

		$this->table('post')
			->dropForeignKey('metagroup_id')
			->save();
			
		$this->table('post')
			->removeColumn('metagroup_id')
			->save();
	}

	/**
	 * @return {void}
	 */
	public function down(){
		// ..
	}
}