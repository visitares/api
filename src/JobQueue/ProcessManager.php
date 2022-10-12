<?php

namespace Visitares\JobQueue;

use Visitares\JobQueue\JobStatus;

class ProcessManager{

	/**
	 * @var \PDO
	 */
	private $pdo;

	/**
	 * @param \PDO $pdo
	 */
	public function __construct(
		\PDO $pdo,
		WorkerFactory $factory
	){
		$this->pdo = $pdo;
		$this->factory = $factory;
	}

	/**
	 * @param \stdClass $row
	 * @return \stdClass
	 */
	private function workerFromRow(\stdClass $row){
		$row->id = (int)$row->id;
		$row->max = (int)$row->max;
		$row->open = (int)$row->open;
		$row->enqueued = (int)$row->enqueued;
		$row->running = (int)$row->running;
		return $row;
	}

	/**
	 * @param \stdClass $row
	 * @return \stdClass
	 */
	private function jobFromRow(\stdClass $row){
		$row->id = (int)$row->id;
		return $row;
	}

	/**
	 * @return void
	 */
	public function run(){
		$this->reopenDeadWorkers();
		$workersOverviewSql = file_get_contents(__DIR__ . '/sql/workers-overview.sql');
		foreach($this->pdo->query($workersOverviewSql, \PDO::FETCH_OBJ) as $row){
			$worker = $this->workerFromRow($row);
			if(!$worker->open || $worker->max === $worker->running){
				continue;
			}
			$this->spawnWorkersFor($worker);
		}
	}

	/**
	 * @return void
	 */
	private function reopenDeadWorkers(){
		$sql = 'UPDATE jobs SET status = 0 WHERE modificationDate < (NOW() - INTERVAL 5 MINUTE) AND status IN(1,2)';
		$query = $this->pdo->prepare($sql);
		return $query->execute();
	}

	/**
	 * @param \stdClass $worker
	 * @return void
	 */
	private function spawnWorkersFor(\stdClass $worker){
		$jobs = $this->selectOpenJobsFor($worker);
		$ok = $this->enqueueJobs($jobs);
		if(!$ok){
			return;
		}
		foreach($jobs as $job){
			$this->spawnWorker($job);
		}
	}

	/**
	 * @param \stdClass $job
	 * @return void
	 */
	private function spawnWorker(\stdClass $job){
		if(APP_DEV){
			return $this->factory->spawn($job->id);
		} else{
			$script = realpath(APP_DIR_ROOT . '/cronjobs/job-queue-run-worker.php');
			if(PHP_OS === 'WINNT'){
				$cmd = sprintf('php "%s" %d', $script, $job->id);
				exec($cmd);
			} else{
				$cmd = sprintf(PHP_BIN . ' "%s" %d > /dev/null 2>&1 &', $script, $job->id);
				exec($cmd);
			}
		}
	}

	/**
	 * @param \stdClass $worker
	 * @return array
	 */
	private function selectOpenJobsFor(\stdClass $worker){
		$openJobsByTypeSql = file_get_contents(__DIR__ . '/sql/open-jobs-by-type.sql');
		$openJobsByTypeSql = sprintf($openJobsByTypeSql, $worker->max - ($worker->enqueued + $worker->running));
		$query = $this->pdo->prepare($openJobsByTypeSql);
		$query->execute([
			':type' => $worker->type,
		]);
		return array_map([$this, 'jobFromRow'], $query->fetchAll(\PDO::FETCH_OBJ));
	}

	/**
	 * @param array $jobs
	 * @return void
	 */
	private function enqueueJobs(array $jobs){
		$ids = array_map(function($job){
			return $job->id;
		}, $jobs);
		
		if(!$ids){
			return false;
		}

		$params = [];
		foreach($ids as $index => $id){
			$params[':id' . $index] = $id;
		}

		$jobsChangeStatusSql = file_get_contents(__DIR__ . '/sql/jobs-change-status.sql');
		$jobsChangeStatusSql = sprintf($jobsChangeStatusSql, implode(', ', array_keys($params)));
		$params[':status'] = JobStatus::ENQUEUED;

		$query = $this->pdo->prepare($jobsChangeStatusSql);
		$query->execute($params);

		return true;
	}

}
