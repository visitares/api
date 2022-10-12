<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0040 extends AbstractMigration{
	/**
	 * @return void
	 */
	public function up(){

		$this->table('media')
			->addColumn('creationDate', 'datetime', [
				'null' => false
			])
			->addColumn('modificationDate', 'datetime', [
				'null' => true
			])
			->addColumn('label', 'string', [
				'length' => 200,
				'null' => true
			])
			->addColumn('description', 'text', [
				'null' => true
			])
			->addColumn('type', 'integer', [
				'null' => false
			])
			->addColumn('mime', 'string', [
				'length' => 100,
				'null' => true
			])
			->addColumn('filename', 'string', [
				'length' => 500
			])
			->save();

		$this->table('form_media', [
			'id' => false,
			'primary_key' => ['form_id', 'media_id']
		])
			->addColumn('form_id', 'integer', [
				'null' => false
			])
			->addColumn('media_id', 'integer', [
				'null' => false
			])
			->addColumn('sort', 'integer', [
				'null' => true
			])

			->addForeignKey('form_id', 'form', 'id', [
				'delete' => 'CASCADE'
			])
			->addForeignKey('media_id', 'media', 'id', [
				'delete' => 'CASCADE'
			])
			->save();
	}
}