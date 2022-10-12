<?php

namespace Visitares\API;

use PHPMailer;
use Visitares\JobQueue\Queues\SendMailQueue;

class TestController {

  /**
   * @var PHPMailer
   */
  private $phpMailer;

  /**
   * @var SendMailQueue
   */
  private $sendMailQueue;

  /**
   * @param PHPMailer $phpMailer
   * @param SendMailQueue $sendMailQueue
   */
  public function __construct(
    PHPMailer $phpMailer,
    SendMailQueue $sendMailQueue
  ){
    $this->phpMailer = $phpMailer;
    $this->sendMailQueue = $sendMailQueue;
  }

  /**
   * Test PHPMailer
   *
   * @return bool
   */
  public function testEmail(){

    $this->phpMailer->From = 'noreply@visitares.com';
    $this->phpMailer->FromName = 'VISITARES';
    $this->phpMailer->addAddress('rderheim@derheim-software.de', 'Ricard Derheim');
    $this->phpMailer->Subject = '[TEST] visitares :: TestController->testEmail()';
    $this->phpMailer->Body = 'This is just a test.';
    $this->phpMailer->AltBody = 'This is just a test.';
    return $this->phpMailer->send();

  }

  /**
   * Test SendEmailQueue
   *
   * @return bool
   */
  public function testEmailQueue(){
    return $this->sendMailQueue->add(
      // from
			[ 'noreply@visitares.com', 'VISITARES' ],
      
      // to
			[
				[ 'rderheim@derheim-software.de', 'Ricard Derheim' ],
			],
      
      // subject
      '[TEST] visitares :: TestController->testMailQueue()',

      // body
      [

        // html
        'html/mails/test.html',

        // plain
        'html/mails/test.html',

        //data
        null
      ],
    
      // attachments
      []
    );
  }

}