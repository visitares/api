<?php

namespace Visitares\JobQueue\Queues;

use Visitares\JobQueue\JobStatus;

class NotifyPostSubsQueue{

	/**
	 * @var \PDO
	 */
	private $pdo;

	/**
	 * @param \PDO $pdo
	 */
	public function __construct(
		\PDO $pdo
	){
		$this->pdo = $pdo;
	}

	/**
	 * @param integer $postId
	 * @param string $event
	 * @return int
	 */
	public function add(int $postId, string $event = 'published'){
		$sql = 'INSERT INTO jobs (creationDate, type, status, priority, payload) VALUES (CURRENT_TIMESTAMP, :type, :status, :priority, :payload)';
		$query = $this->pdo->prepare($sql);
		$query->execute([
			':type' => 'notify-post-subs',
			':status' => JobStatus::READY,
			':priority' => 0,
			':payload' => json_encode([
				'post' => [
					'id' => $postId
				],
				'event' => $event,
			]),
		]);
		return $this->pdo->lastInsertId();
	}

}
