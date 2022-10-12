<?php

namespace Visitares\Service\Notification;

use PHPMailer;

class Email{

	/**
	 * @var PHPMailer
	 */
	private $mailer;

	/**
	 * @param PHPMailer $mailer
	 */
	public function __construct(PHPMailer $mailer){
		$this->mailer = $mailer;
	}

	/**
	 * @param string $message
	 * @param array $from [:email, :name]
	 * @param array $receivers
	 * @return void
	 */
	public function send(string $subject, string $message, array $from, array $receivers){
		$mail = clone $this->mailer;
		list($fromEmail, $fromName) = $from;

		$mail->Subject = $subject;
		$mail->Body = $mail->BodyAlt = $message;

		$mail->From = $fromEmail;
		$mail->FromName = $fromName;
		foreach($receivers as $receiver){
			if(is_string($receiver)){
				$receiver = [$receiver];
			}
			list($email, $name) = array_merge($receiver, ['']);
			$mail->addAddress($email, $name);
		}

		return $mail->send() ? true : false;

	}

}
