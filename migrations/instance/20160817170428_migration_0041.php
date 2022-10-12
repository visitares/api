<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0041 extends AbstractMigration{
	/**
	 * @return void
	 */
	public function up(){
		$this->table('media')
			->addColumn('filesize', 'integer', [
				'after' => 'filename',
				'null' => true
			])
			->addColumn('originalFilename', 'string', [
				'after' => 'filename',
				'null' => true
			])
			->save();
	}
}