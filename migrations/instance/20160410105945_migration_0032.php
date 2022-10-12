<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0032 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('categoryprocess')
			->addColumn('creationDate', 'datetime', [
				'null' => false
			])
			->addColumn('modificationDate', 'datetime', [
				'null' => true
			])
			->addColumn('category_id', 'integer', [
				'null' => false
			])
			->addColumn('isArchived', 'boolean', [
				'null' => false,
				'default' => false
			])
			->addColumn('token', 'string', [
				'length' => 8
			])
			->addColumn('name', 'string', [
				'null' => false,
				'length' => 200
			])
			->addColumn('description', 'text', [
				'null' => true
			])
			->addForeignKey('category_id', 'category', 'id', [
				'delete' => 'RESTRICT'
			])
			->save();

		$this->table('submit')
			->addColumn('categoryprocess_id', 'integer', [
				'null' => true
			])
			->addForeignKey('categoryprocess_id', 'categoryprocess', 'id', [
				'delete' => 'RESTRICT'
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