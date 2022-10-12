<?php

use Phinx\Migration\AbstractMigration;

class Migration0066 extends AbstractMigration{

  /**
   * Creates a table object with default columns.
   *
   * @param  boolean $creationDate
   * @param  boolean $modificationDate
   * @return Table
   */
  protected function createBaseTable($name, $options = [], $creationDate = true, $modificationDate = true){
    $table = $this->table($name, $options);
    if($creationDate){
      $table->addColumn('creationDate', 'datetime', [
        'null' => true
      ]);
    }
    if($modificationDate){
      $table->addColumn('modificationDate', 'datetime', [
        'null' => true
      ]);
    }
    return $table;
  }

  /**
   * @return void
   */
  public function change(){

    $this->createBaseTable('catalog')
      ->addColumn('nameTranslation_id', 'integer')
        ->addForeignKey('nameTranslation_id', 'translation', 'id', [
          'delete' => 'RESTRICT'
        ])
      ->save();

    $this->createBaseTable('catalog_attribute')
      ->addColumn('catalog_id', 'integer')
        ->addForeignKey('catalog_id', 'catalog', 'id', [
          'delete' => 'CASCADE'
        ])
      ->addColumn('position', 'integer', ['null' => false, 'default' => 0])
      ->addColumn('type', 'integer', ['null' => false, 'default' => 0])
      ->addColumn('nameTranslation_id', 'integer')
        ->addForeignKey('nameTranslation_id', 'translation', 'id', [
          'delete' => 'RESTRICT'
        ])
      ->save();

    $this->createBaseTable('catalog_entry')
      ->addColumn('catalog_id', 'integer')
        ->addForeignKey('catalog_id', 'catalog', 'id', [
          'delete' => 'CASCADE'
        ])
      ->addColumn('nameTranslation_id', 'integer')
        ->addForeignKey('nameTranslation_id', 'translation', 'id', [
          'delete' => 'RESTRICT'
        ])
      ->save();

    $this->createBaseTable('catalog_entry_attribute')
      ->addColumn('entry_id', 'integer')
        ->addForeignKey('entry_id', 'catalog_entry', 'id', [
          'delete' => 'CASCADE'
        ])
      ->addColumn('attribute_id', 'integer')
        ->addForeignKey('attribute_id', 'catalog_attribute', 'id', [
          'delete' => 'CASCADE'
        ])
      ->addColumn('isActive', 'boolean', ['null' => false, 'default' => true])
      ->addColumn('valueTranslation_id', 'integer')
        ->addForeignKey('valueTranslation_id', 'translation', 'id', [
          'delete' => 'RESTRICT'
        ])
      ->save();
    
    $this->table('form')
      ->addColumn('catalog_id', 'integer', ['null' => true, 'after' => 'category_id'])
        ->addForeignKey('catalog_id', 'catalog', 'id', [
          'delete' => 'SET_NULL',
        ])
      ->save();

  }

}
