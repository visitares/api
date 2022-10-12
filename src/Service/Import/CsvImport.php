<?php

namespace Visitares\Service\Import;

class CsvImport{

  /**
   * @param string $csv
   * @param string $rowDelimiter
   * @param string $columnDelimiter
   * @param bool $skipFirstRow
   * @param bool $parseQuotedValues
   * @param int $numOfPreviewRows
   * @return void
   */
  public function getPreview(
    string $csv,
    string $rowDelimiter = PHP_EOL,
    string $columnDelimiter = ';',
    bool $skipFirstRow = false,
    bool $parseQuotedValues = false,
    int $numOfPreviewRows = 5
  ){
    $rows = $this->parseCsv($csv, $rowDelimiter, $columnDelimiter, $skipFirstRow, $parseQuotedValues);
    return array_slice($rows, 0, $numOfPreviewRows);
  }

  /**
   * @param array $rows
   * @param array $map
   * @return \stdClass[]
   */
  public function mapToKeys(array $rows, array $keys){
    return array_map(function($row) use($keys){
      $result = [];
      foreach($keys as $targetIndex => $sourceIndex){
        $result[$targetIndex] = $row[$sourceIndex];
      }
      return $result;
    }, $rows);
  }

  /**
   * @param string $csv
   * @param string $rowDelimiter
   * @param string $columnDelimiter
   * @param bool $skipFirstRow
   * @param bool $parseQuotedValues
   * @return array
   */
  public function parseCsv(
    string $csv,
    string $rowDelimiter = PHP_EOL,
    string $columnDelimiter = ';',
    bool $skipFirstRow = false,
    bool $parseQuotedValues = false,
    bool $skipEmptyLines = true
  ){
    $csv = trim($csv);
    if(!$csv){
      return [];
    }

    if(!mb_check_encoding($csv, 'UTF-8')){
      $csv = utf8_encode($csv);
    }

    $rows = explode($rowDelimiter, $csv);
    if(!$rows){
      return [];
    }

    if($skipFirstRow){
      array_shift($rows);
    }

    $rows = array_map(function($row) use($columnDelimiter, $parseQuotedValues){
      $row = explode($columnDelimiter, $row);
      $row = array_map('trim', $row);
      if($parseQuotedValues){
        $row = array_map([$this, 'parseQuotedValue'], $row);
      }
      return $row;
    }, $rows);

    if($skipEmptyLines){
      $rows = array_filter($rows, function($row){
        $sum = array_reduce($row, function($sum, $value){
          return $sum . $value;
        }, '');
        return !!$sum;
      });
      $rows = array_values($rows);
    }

    return $rows;
  }

  /**
   * @param string $value
   * @return void
   */
  private function parseQuotedValue(string $value = ''){
    if(!$value){
      return $value;
    }
    if(!preg_match('/^"(.*)"$/', $value, $match)){
      return $value;
    }
    return $match[1] ?? $value;
  }

}
