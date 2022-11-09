<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0044 extends AbstractMigration{
	/**
	 * @return void
	 */
	public function up(){
		$this->table('media')
			->addColumn('language_id', 'integer', [
				'signed' => false,
				'null' => true,
				'after' => 'group_id'
			])
			->addForeignKey('language_id', 'language', 'id', [
				'delete' => 'SET_NULL'
			])
			->save();

		$this->table('form')
			->addColumn('singleSubmitOnly', 'boolean', [
				'default' => false,
				'after' => 'maxScore'
			])
			->save();

		$this->table('usersubmitinstance')
			->addColumn('creationDate', 'datetime', [
				'null' => true
			])
			->addColumn('modificationDate', 'datetime', [
				'null' => true
			])
			->addColumn('category_id', 'integer', [
				'signed' => false,
				'null' => false
			])
			->addColumn('user_id', 'integer', [
				'signed' => false,
				'null' => false
			])

			->addColumn('isDone', 'boolean', [
				'null' => false,
				'default' => false
			])

			->addColumn('score', 'integer', [
				'null' => false,
				'default' => 0
			])

			->addColumn('name', 'string', [
				'null' => false,
				'length' => 250
			])
			->addColumn('description', 'text', [
				'null' => true
			])

			->addForeignKey('category_id', 'category', 'id', [
				'delete' => 'RESTRICT'
			])
			->addForeignKey('user_id', 'user', 'id', [
				'delete' => 'RESTRICT'
			])
			->save();
	}
}