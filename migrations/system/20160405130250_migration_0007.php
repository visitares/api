<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0007 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('imagegroup')
			->addColumn('creationDate', 'datetime', [
				'null' => true
			])
			->addColumn('modificationDate', 'datetime', [
				'null' => true
			])
			->addColumn('label', 'string', [
				'null' => false
			])
			->addColumn('type', 'integer', [
				'null' => false
			])
			->addColumn('instances', 'text', [
				'null' => true
			])
			->save();

		$this->table('image')
			->addColumn('creationDate', 'datetime', [
				'null' => true
			])
			->addColumn('modificationDate', 'datetime', [
				'null' => true
			])
			->addColumn('group_id', 'integer', [
				'null' => false
			])
			->addColumn('filename', 'string', [
				'null' => false,
				'length' => 128
			])
			->addForeignKey('group_id', 'imagegroup', 'id', [
				'delete' => 'RESTRICT'
			])
			->save();

		$this->table('instance')
			->addColumn('imagegroups', 'text', [
				'null' => true
			])
			->addColumn('background_id', 'integer', [
				'null' => true,
				'after' => 'background'
			])
			->addForeignKey('background_id', 'image', 'id', [
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