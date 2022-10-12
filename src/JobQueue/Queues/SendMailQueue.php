<?php

namespace Visitares\JobQueue\Queues;

use Visitares\JobQueue\JobStatus;

class SendMailQueue{

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
	 * @return int
	 */
	public function add(array $from, array $to, string $subject, array $template, array $attachments = []){

		$sql = 'INSERT INTO jobs (creationDate, type, status, priority, payload) VALUES (CURRENT_TIMESTAMP, :type, :status, :priority, :payload)';
		$query = $this->pdo->prepare($sql);
		$query->execute([
			':type' => 'send-mail',
			':status' => JobStatus::READY,
			':priority' => 0,
			':payload' => json_encode([
				'from' => $from,
				'to' => $to,
				'subject' => $subject,
				'template' => $template,
				'attachments' => $attachments,
			]),
		]);

		return $this->pdo->lastInsertId();
	}

}
