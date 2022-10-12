<?php

use Phinx\Migration\AbstractMigration;

class Migration0069 extends AbstractMigration{
  public function change(){
    $this->table('form')
      ->addColumn('htmlTextTranslation_id', 'integer', [
        'null' => true,
        'after' => 'descriptionTranslation_id',
      ])
			->addForeignKey('htmlTextTranslation_id', 'translation', 'id', [
				'delete' => 'RESTRICT'
			])
      ->save();
  }
}