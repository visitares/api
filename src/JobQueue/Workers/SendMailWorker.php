<?php

namespace Visitares\JobQueue\Workers;

use PHPMailer\PHPMailer\PHPMailer;
use Twig\Environment as Twig;
use Visitares\JobQueue\JobStatus;

class SendMailWorker{

	/**
	 * @var PHPMailer
	 */
	private $mailer;

	/**
	 * @var Twig
	 */
	private $twig;

	/**
	 * @param PHPMailer $mailer
	 * @param Twig $twig
	 */
	public function __construct(
		PHPMailer $mailer,
		Twig $twig
	){
		$this->mailer = $mailer;
		$this->twig = $twig;
	}

	/**
	 * @param \stdClass $job
	 * @return int
	 */
	public function run(\stdClass $job){

		list($fromEmail, $fromName) = $job->payload->from;
		$tos = array_map(function($to){
			list($email, $name) = $to;
			return (object)[
				'email' => $email,
				'name' => $name,
			];
		}, $job->payload->to);

		$this->mailer->From = $fromEmail;
		$this->mailer->FromName = $fromName;

		if(APP_DEV || APP_DEBUG){
			$this->mailer->addAddress('rderheim@derheim-software.de', 'Ricard Derheim');
		} else{
			foreach($tos as $to){
				$this->mailer->addAddress($to->email, $to->name);
			}
		}

		list($templateHtml, $templatePlain, $data) = $job->payload->template;
		$bodyHtml = $this->twig->render($templateHtml, (array)$data);
		$bodyPlain = !$templatePlain ? $bodyHtml : $this->twig->render($templatePlain, (array)$data);

		$this->mailer->Subject = $job->payload->subject;
		$this->mailer->Body = $bodyHtml;
		$this->mailer->AltBody = $bodyPlain;

		if($this->mailer->send()){
			file_put_contents(APP_DIR_LOG . '/workers/send-mail.log', sprintf('[%s] send: %s', date('Y-m-d H:i:s'), $tos[0]->email) . PHP_EOL, FILE_APPEND);
			return JobStatus::DONE;
		} else{
			file_put_contents(APP_DIR_LOG . '/workers/send-mail.log', sprintf('[%s] fail: %s', date('Y-m-d H:i:s'), $tos[0]->email) . PHP_EOL, FILE_APPEND);
			return JobStatus::FAILED;
		}

	}

}
