<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0011 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){

		/**
		 * usercache table
		 */
		$this->createBaseTable('usercache')
			->addColumn('instance_id', 'integer', [ 'signed' => true, 'null' => false ])
			->addForeignKey('instance_id', 'instance', 'id', [ 'delete' => 'CASCADE' ])
			->addColumn('user_id', 'integer', [ 'signed' => true, 'null' => false ])
			->addIndex(['user_id'], ['unique' => true])
			->addColumn('username', 'string', [ 'limit' => 100 ])
			->addColumn('firstname', 'string', [ 'limit' => 100 ])
			->addColumn('lastname', 'string', [ 'limit' => 100 ])
			->addColumn('email', 'string', [ 'limit' => 100 ])
			->save();

		/**
		 * usercache table
		 */
		$this->createBaseTable('groupcache')
			->addColumn('instance_id', 'integer', [ 'signed' => true, 'null' => false ])
			->addForeignKey('instance_id', 'instance', 'id', [ 'delete' => 'CASCADE' ])
			->addColumn('group_id', 'integer', [ 'signed' => true, 'null' => false ])
			->addIndex(['group_id'], ['unique' => true])
			->addColumn('name', 'text', [ 'null' => true ])
			->addColumn('description', 'text', [ 'null' => true ])
			->save();

		/**
		 * master table
		 */
		$this->createBaseTable('master')
			->addColumn('name', 'string', [ 'length' => 250, 'null' => false ])
			->addColumn('isActive', 'boolean', [ 'default' => false, 'null' => false ])
			->addColumn('shortDescription', 'text', [ 'null' => true ])
			->addColumn('description', 'text', [ 'null' => true ])
			->save();

		/**
		 * timeline table
		 */
		$this->createBaseTable('timeline')
			->addColumn('name', 'string', [ 'length' => 250, 'null' => false ])
			->addColumn('isActive', 'boolean', [ 'default' => false, 'null' => false ])
			->addColumn('shortDescription', 'text', [ 'null' => true ])
			->save();


		/**
		 * post table
		 */
		$this->createBaseTable('post')
			->addColumn('timeline_id', 'integer', [ 'signed' => true, 'null' => false ])
			->addColumn('user_id', 'integer', [ 'signed' => true, 'null' => false ])

			->addColumn('published', 'integer', [ 'null' => false ])

			->addColumn('title', 'string', [ 'null' => false, 'length' => 500 ])
			->addColumn('content', 'text', [ 'null' => false ])
			->addColumn('likes', 'integer', [ 'null' => false, 'default' => 0 ])
			
			->addForeignKey('timeline_id', 'timeline', 'id', [ 'delete' => 'CASCADE' ])
			
			->save();


		/**
		 * media table
		 */
		$this->table('media')
			->addColumn('creationDate', 'datetime', [ 'null' => false ])
			->addColumn('modificationDate', 'datetime', [ 'null' => true ])
			->addColumn('master_id', 'integer', [ 'signed' => true, 'null' => true ])
			->addForeignKey('master_id', 'master', 'id', [ 'delete' => 'CASCADE' ])
			->addColumn('group_id', 'integer', [ 'signed' => true, 'null' => true ])
			->addForeignKey('group_id', 'groupcache', 'id', [ 'delete' => 'CASCADE' ])
			->addColumn('post_id', 'integer', [ 'signed' => true, 'null' => true ])
			->addForeignKey('post_id', 'post', 'id', [ 'delete' => 'CASCADE' ])
			->addColumn('label', 'string', [ 'null' => true, 'length' => 200 ])
			->addColumn('description', 'text', [ 'null' => true ])
			->addColumn('type', 'integer', [ 'null' => false ])
			->addColumn('mime', 'string', [ 'null' => true, 'length' => 100 ])
			->addColumn('filename', 'string', [ 'length' => 500 ])
			->addColumn('filesize', 'integer', [ 'null' => true ])
			->addColumn('originalFilename', 'string', [ 'null' => true, 'length' => 500 ])
			->addColumn('ext', 'string', [ 'null' => true, 'length' => 20 ])
			->save();


		/**
		 * mediagroup table
		 */
		$this->table('mediagroup')
			->addColumn('creationDate', 'datetime', [ 'null' => false ])
			->addColumn('modificationDate', 'datetime', [ 'null' => true ])
			->addColumn('label', 'string', [ 'length' => 200, 'null' => true ])
			->addColumn('description', 'text', [ 'null' => true ])
			->save();


		/**
		 * post-group join-table
		 */
		$this->table('post_group', [
			'id' => false,
			'primary_key' => [
				'post_id',
				'group_id'
			]
		])
			->addColumn('post_id', 'integer', [ 'signed' => true, 'null' => false ])
			->addColumn('group_id', 'integer', [ 'signed' => true, 'null' => false ])
			->addForeignKey('post_id', 'post', 'id', [ 'delete' => 'CASCADE' ])
			->save();
			

		/**
		 * update instance table
		 */
		$this->table('instance')
			->addColumn('timeline_id', 'integer', [ 'signed' => true, 'null' => true])
			->addForeignKey('timeline_id', 'timeline', 'id', ['delete' => 'SET_NULL'])
			->save();

	}

	/**
	 * @param  boolean $creationDate
	 * @param  boolean $modificationDate
	 * @return Table
	 */
	protected function createBaseTable($name, $options = [], $creationDate = true, $modificationDate = true){
		$table = $this->table($name, $options);
		if($creationDate){
			$table->addColumn('creationDate', 'datetime', [ 'null' => true ]);
		}
		if($modificationDate){
			$table->addColumn('modificationDate', 'datetime', [ 'null' => true ]);
		}
		return $table;
	}

	/**
	 * @return {void}
	 */
	public function down(){
		$this->dropTable('master_media');
		$this->dropTable('post_media');
		$this->dropTable('post_group');
		$this->dropTable('media');
		$this->dropTable('master');
		$this->dropTable('timeline');
		$this->dropTable('post');
	}
}