<?php

namespace Visitares\API\Statistics\UserSubmitInstance;

use Visitares\API\CsvController;
use Visitares\fns;
use Visitares\Storage\Facade\InstanceStorageFacade;

class Export{

	private $token;
	private $storage;
	private $em;
	private $pdo;
	private $csvController;

	public function __construct(
		$token,
		InstanceStorageFacade $storage,
		CsvController $csvController
	){
		$this->token = $token;
		$this->storage = $storage;
		$this->storage->setToken($token);
		$this->em = $storage->getEntityManager();
		$this->pdo = $this->em->getConnection()->getWrappedConnection();
		$this->csvController = $csvController;
	}

	/**
	 * @param array $filter
	 * @return array
	 */
	public function go(array $filter){

		if(
			!isset($filter['Period']) ||
			!isset($filter['Period']['From']) ||
			!isset($filter['Period']['To'])
		){
			return[
				'error' => true,
				'code' => 'FILTER_INVALID'
			];
		}

		$filter['Period']['From'] = $filter['Period']['From'] ? (date('Y-m-d', strtotime($filter['Period']['From'])) . ' 00:00:00') : null;
		$filter['Period']['To'] = $filter['Period']['To'] ? (date('Y-m-d', strtotime($filter['Period']['To'])) . ' 23:59:59') : null;

		list($params, $whereClause) = $this->createWhere($filter);

		$sql = file_get_contents(__DIR__ . '/export.sql');
		$sql = str_replace('{where}', $whereClause, $sql);

		$query = $this->pdo->prepare($sql);
		$res = $query->execute($params);

		$rows = $res->fetchAll(\PDO::FETCH_OBJ);
		$rows = array_map([$this, 'transformRow'], $rows);

		if(!$rows){
			return[
				'error' => true,
				'code' => 'EMPTY_DATA',
			];
		}

		// index by date
		$indexedRows = fns::groupBy($rows, function(\stdClass $row){
			return (new \DateTime($row->creationDate))->format('Y-m-d');
		});

		// index by user id
		$indexedRows = fns::mapMap($indexedRows, function($rows, $key){
			return fns::groupBy($rows, function(\stdClass $row){
				return $row->user_id;
			});
		});

		// get distinct dates
		$dates = array_keys($indexedRows);

		// get distinct users
		$users = fns::fold($rows, function(array $users, \stdClass $row){
			$user = fns::find($users, function($user) use($row){
				return $user->user_id === $row->user_id;
			}) ?? null;
			return $user ? $users : array_merge($users, [$row]);
		}, []);

		// create pivot data
		$data = $this->createPivotData($indexedRows, $dates, fns::map($users, fns::getter('user_id')));

		// create csv
		$csvRows = [ [''] ];
		$csvRows[0] = array_merge($csvRows[0], fns::fold($dates, function($dates, $date){
			return array_merge($dates, [$date, 'Punkte erreicht', 'Punkte max.']);
		}, []));

		foreach($users as $user){
			$csvRow = [ $user->user_firstname . ' ' . $user->user_lastname ];
			foreach($dates as $date){
				$row = $indexedRows[$date][$user->user_id][0] ?? null;
				if($row){
					$csvRow[] = $row->name;
					$csvRow[] = $row->score;
					$csvRow[] = $row->category_maxScore;
				} elseif(!$row){
					$csvRow[] = '';
					$csvRow[] = '';
					$csvRow[] = '';
				}
			}
			$csvRows[] = $csvRow;
		}

		return $this->csvController->create($this->token, $csvRows, false);
	}

	/**
	 * @param array $indexedData
	 * @param array $x
	 * @param array $y
	 * @return array
	 */
	private function createPivotData(array $indexedData, array $xIndexes, array $yIndexes){
		$data = [];
		foreach($xIndexes as $x => $xIndex){
			$data[$x] = [];
			foreach($yIndexes as $y => $yIndex){
				$data[$x][$y] = $indexedData[$xIndex][$yIndex] ?? null;
			}
		}
		return $data;
	}

	private function transformRow(\stdClass $row){
		$cast = function($value, $fn){
			return $value === null ? $value : $fn($value);
		};
		foreach(['id', 'category_id', 'user_id'] as $prop){
			$row->{$prop} = $cast($row->{$prop}, 'intval');
		}
		foreach(['score', 'category_maxScore'] as $prop){
			$row->{$prop} = $cast($row->{$prop}, 'floatval');
		}
		foreach(['isDone'] as $prop){
			$row->{$prop} = $cast($row->{$prop}, 'boolval');
		}
		return $row;
	}

	/**
	 * @param array $filter
	 * @return array
	 */
	private function createWhere(array $filter){
		list($params, $conditions) = array_reduce(array_keys($filter), function(array $where, $key) use($filter){
			list($params, $conditions) = $where;
			switch($key){
				case 'Period':
					return[
						$params + [
							':DateTimeFrom' => $filter[$key]['From'],
							':DateTimeTo' => $filter[$key]['To'],
						],
						array_merge($conditions, [
							'usi.creationDate BETWEEN :DateTimeFrom AND :DateTimeTo'
						]),
					];

				case 'isDone':
					return[
						$params + [':isDone' => $filter[$key]],
						array_merge($conditions, ['usi.isDone = :isDone']),
					];

				case 'GroupId':
					if(!$filter[$key]){
						return $where;
					}
					$newParams = array_reduce($filter[$key], function($params, $id){
						$key = ':GroupId' . count($params);
						return $params + [ $key => $id ];
					}, []);
					return[
						$params + $newParams,
						array_merge($conditions, ['gu.group_id IN (' . implode(', ', array_keys($newParams)) . ')']),
					];

				default:
					return $where;
			}
		}, [ [], [] ]);
		return[
			$params,
			$conditions ? sprintf(' WHERE %s ', implode(' AND ', $conditions)) : '',
		];
	}

}
