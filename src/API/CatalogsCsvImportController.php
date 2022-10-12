<?php

namespace Visitares\API;

use Visitares\Service\Import\CsvImport;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Entity\{
  Catalog,
  CatalogAttribute,
  CatalogEntry,
  CatalogEntryAttribute
};

class CatalogsCsvImportController{

  private $csvImport = null;
  private $storage = null;
  private $em = null;

  /**
   * @param CsvImport $csvImport
   */
  public function __construct(
    CsvImport $csvImport,
		InstanceStorageFacade $storage,
		string $token = ''
  ){
    $this->csvImport = $csvImport;
    $this->storage = $storage;
    if($token){
      $this->storage->setToken($token);
      $this->em = $this->storage->getEntityManager();
    }
  }

  /**
   * @param string $rowDelimiter
   * @param string $columnDelimiter
   * @param bool $skipFirstRow
   * @param bool $parseQuotedValues
   * @param int $numOfPreviewRows
   * @return void
   */
  public function loadPreview(
    string $rowDelimiter = PHP_EOL,
    string $columnDelimiter = ';',
    bool $skipFirstRow = false,
    bool $parseQuotedValues = false,
    int $numOfPreviewRows = 5
  ){
    $file = $_FILES['csv'] ?? null;
    if(!$file){
      return [ 'error' => true, 'code' => 'NO_FILE' ];
    }
    $rows = $this->csvImport->getPreview(
      file_get_contents($file['tmp_name']),
      $rowDelimiter,
      $columnDelimiter,
      false, // always keep first line (we'll remove it later)
      $parseQuotedValues,
      $numOfPreviewRows
    );

    $header = [];
    if($skipFirstRow){
      $header = array_shift($rows);
    }

    return (object)[
      'header' => $header,
      'rows' => $rows
    ];
  }

  /**
   * Example mapping:
   * 
   *  [
   *    <AttributeId> => <CsvColumnIndex>,
   *    123 => 2,
   *    456 => 0,
   *  ]
   * 
   * @param string $rowDelimiter
   * @param string $columnDelimiter
   * @param bool $skipFirstRow
   * @param bool $parseQuotedValues
   * @param int $catalogId
   * @param array $map
   * @return bool
   */
  public function import(
    string $rowDelimiter = PHP_EOL,
    string $columnDelimiter = ';',
    bool $skipFirstRow = false,
    bool $parseQuotedValues = true,
    int $catalogId = null,
    \stdClass $map = null
  ){
    set_time_limit(0);

    $file = $_FILES['csv'] ?? null;
    if(!$file){
      return [ 'error' => true, 'code' => 'NO_FILE' ];
    }

    // parse csv
    $rows = $this->csvImport->parseCsv(
      file_get_contents($file['tmp_name']),
      $rowDelimiter,
      $columnDelimiter,
      $skipFirstRow,
      $parseQuotedValues
    );
    if(!$rows){
      return [ 'error' => true, 'code' => 'NO_DATA' ];
    }

    $mapped = $this->csvImport->mapToKeys($rows, (array)$map);


    $pdo = $this->em->getConnection()->getWrappedConnection();

    $insertTranslation = $pdo->prepare('INSERT INTO translation (creationDate) VALUES (NOW());');

    $insertTranslated = $pdo->prepare('INSERT INTO translated (creationDate, language_id, translation_id, content) VALUES (NOW(), :language_id, :translation_id, :content);');

    $insertCatalogEntry = $pdo->prepare('
      INSERT INTO catalog_entry (
        creationDate,
        catalog_id,
        nameTranslation_id,
        descriptionTranslation_id
      ) VALUES (
        NOW(),
        :catalog_id,
        :nameTranslation_id,
        :descriptionTranslation_id
      );
    ');

    $insertCatalogEntryAttribute = $pdo->prepare('
      INSERT INTO catalog_entry_attribute (
        creationDate,
        entry_id,
        attribute_id,
        isActive,
        valueTranslation_id
      ) VALUES (
        NOW(),
        :entry_id,
        :attribute_id,
        :isActive,
        :valueTranslation_id
      );
    ');

    foreach($mapped as $row){

      $nameTranslationId = null;
      $descriptionTranslationId = null;

      // create name translation
      foreach($row as $key => $value){
        list($field, $lang) = explode('.', $key);
        if($field !== 'name'){
          continue;
        }
        $insertTranslation->execute();
        $nameTranslationId = $pdo->lastInsertId();
        $insertTranslated->execute([
          ':language_id' => 1,
          ':translation_id' => $nameTranslationId,
          ':content' => $value,
        ]);
        break;
      }

      if(!$nameTranslationId){
        $insertTranslation->execute();
        $nameTranslationId = $pdo->lastInsertId();
        $insertTranslated->execute([
          ':language_id' => 1,
          ':translation_id' => $nameTranslationId,
          ':content' => '',
        ]);
      }

      // create description translation
      foreach($row as $key => $value){
        list($field, $lang) = explode('.', $key);
        if($field !== 'description'){
          continue;
        }
        $insertTranslation->execute();
        $descriptionTranslationId = $pdo->lastInsertId();
        $insertTranslated->execute([
          ':language_id' => 1,
          ':translation_id' => $descriptionTranslationId,
          ':content' => $value,
        ]);
        break;
      }

      if(!$descriptionTranslationId){
        $insertTranslation->execute();
        $descriptionTranslationId = $pdo->lastInsertId();
        $insertTranslated->execute([
          ':language_id' => 1,
          ':translation_id' => $descriptionTranslationId,
          ':content' => '',
        ]);
      }

      // create catalog entry
      $insertCatalogEntry->execute([
        ':catalog_id' => $catalogId,
        ':nameTranslation_id' => $nameTranslationId,
        ':descriptionTranslation_id' => $descriptionTranslationId,
      ]);
      $catalogEntryId = $pdo->lastInsertId();

      // create attributes
      foreach($row as $key => $value){
        list($attributeId, $lang) = explode('.', $key);
        if(!is_numeric($attributeId)){
          continue;
        }
        $insertTranslation->execute();
        $valueTranslationId = $pdo->lastInsertId();
        $insertTranslated->execute([
          ':language_id' => 1,
          ':translation_id' => $valueTranslationId,
          ':content' => $value,
        ]);

        $insertCatalogEntryAttribute->execute([
          ':entry_id' => $catalogEntryId,
          ':attribute_id' => $attributeId,
          ':isActive' => true,
          ':valueTranslation_id' => $valueTranslationId,
        ]);
      }

    }

    return true;
  }

}