<?php

use Phinx\Migration\AbstractMigration;

class Migration0067 extends AbstractMigration{

  /**
   * @return void
   */
  public function change(){

    $this->table('catalog')
      ->addColumn('descriptionTranslation_id', 'integer', ['signed' => true])
        ->addForeignKey('descriptionTranslation_id', 'translation', 'id', [
          'delete' => 'RESTRICT'
        ])
      ->save();

    $this->table('catalog_entry')
      ->addColumn('descriptionTranslation_id', 'integer', ['signed' => true])
        ->addForeignKey('descriptionTranslation_id', 'translation', 'id', [
          'delete' => 'RESTRICT'
        ])
      ->save();

  }

}
