<?php

namespace Visitares\JobQueue;

use Auryn\Injector;
use Visitares\JobQueue\JobStatus;

class WorkerFactory{

	/**
	 * @var \PDO
	 */
	private $pdo;

	/**
	 * @var Injector
	 */
	private $provider;

	/**
	 * @param \PDO $pdo
	 * @param Injector $provider
	 */
	public function __construct(
		\PDO $pdo,
		Injector $provider
	){
		$this->pdo = $pdo;
		$this->provider = $provider;
	}

	/**
	 * @param int $jobId
	 * @return void
	 */
	public function spawn(int $jobId){
		if(!$job = $this->selectJob($jobId)){
			return false;
		}

		$workerByType = require(APP_DIR_ROOT . '/config/workers.php');
		if(!isset($workerByType[$job->type])){
			return false;
		}

		if(!$worker = $this->provider->make($workerByType[$job->type]->worker)){
			return false;
		}

		$this->setJobStatus($job->id, JobStatus::RUNNING);
		try{
			set_time_limit($workerByType[$job->type]->limit);
			$status = $worker->run($job);
		} catch(\Throwable $e){
			$status = JobStatus::FAILED;
		}
		$this->setJobStatus($job->id, $status);
		return true;
	}

	/**
	 * @param integer $id
	 * @return \stdClass
	 */
	private function selectJob(int $id){
		$query = $this->pdo->prepare('SELECT id, type, payload FROM jobs WHERE id = :id');
		$query->execute([
			':id' => $id,
		]);

		$row = $query->fetch(\PDO::FETCH_OBJ);
		$row->id = (int)$row->id;
		$row->payload = $row->payload ? json_decode($row->payload) : null;

		return $row;
	}

	/**
	 * @param integer $id
	 * @param integer $status
	 * @return void
	 */
	private function setJobStatus(int $id, int $status){
		$lockJobsSql = file_get_contents(__DIR__ . '/sql/jobs-change-status.sql');
		$lockJobsSql = sprintf($lockJobsSql, ':id');
		$query = $this->pdo->prepare($lockJobsSql);
		$query->execute([
			':id' => $id,
			':status' => $status,
		]);
	}

}
