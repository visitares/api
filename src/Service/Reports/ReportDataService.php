<?php

namespace Visitares\Service\Reports;

use DateTime;
use Visitares\Storage\Facade\InstanceStorageFacade;

class ReportDataService{
	private $storage = null;

	public function __construct(
		InstanceStorageFacade $storage
	){
		$this->storage = $storage;
	}

	public function fetch(array $filter){
		$queryString = 'SELECT s FROM Visitares\Entity\Submit s ';

		if(isset($filter['input'])){
			$queryString .= 'JOIN s.values v ';
		}

		if(isset($filter['user']) || isset($filter['group'])){
			$queryString .= 'JOIN s.user u JOIN u.groups g ';
		}

		$conditions = [];
		$params = [];

		if(isset($filter['form'])){
			$conditions[] = 's.form = :form';
			$params['form'] = $filter['form'];
		}

		if(isset($filter['input'])){
			$conditions[] = 'v.input = :input';
			$params['input'] = $filter['input'];
		}

		if(isset($filter['from'])){
			$conditions[] = 's.creationDate >= :from';
			$params['from'] = new DateTime($filter['from']);
		}

		if(isset($filter['to'])){
			$conditions[] = 's.creationDate <= :to';
			$params['to'] = new DateTime($filter['to']);
		}

		if(isset($filter['group'])){
			$conditions[] = 'g.id = :group';
			$params['group'] = $filter['group'];
		}

		if(isset($filter['user'])){
			$conditions[] = 'u.id = :user';
			$params['user'] = $filter['user'];
		}

		if($conditions){
			$queryString .= 'WHERE ' . implode(' AND ', $conditions) . ' ';
		}

		$queryString .= 'ORDER BY s.creationDate';

		$em = $this->storage->getEntityManager();
		$query = $em->createQuery($queryString);
		foreach($params as $name => $value){
			$query->setParameter($name, $value);
		}

		$submits = $query->getResult();

		return $submits;
	}
}