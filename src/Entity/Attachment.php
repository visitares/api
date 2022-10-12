<?php

namespace Visitares\Entity;

use DateTime;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Attachment extends AbstractEntity{
	/**
	 * @var integer
	 */
	protected $id = null;

	/**
	 * @var DateTime
	 */
	protected $creationDate = null;

	/**
	 * @var DateTime
	 */
	protected $modificationDate = null;

	/**
	 * @var Message
	 */
	protected $message = null;

	/**
	 * @var Form
	 */
	protected $form = null;

	/**
	 * @var string
	 */
	protected $name = null;

	/**
	 * @var string
	 */
	protected $mimetype = null;

	/**
	 * @var integer
	 */
	protected $size = null;

	/**
	 * @var string
	 */
	protected $data = null;

	/**
	 * @var integer
	 */
	protected $sort = null;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
	}

	private function initDir(string $token){
		$dir = APP_DIR_ROOT . '/web/shared/user/' . $token . '/attachments';
		if(!file_exists($dir)){
			@mkdir($dir, 0777, true);
		}
	}

	public function getData(string $token){
		$file = APP_DIR_ROOT . '/web/shared/user/' . $token . '/attachments/' . hash('sha256', $this->getId());
		if(!file_exists($file)){
			return null;
		}
		return file_get_contents($file);
	}

	/**
	 * @param string $data
	 * @return void
	 */
	public function setData($data, string $token, bool $raw = false){
		$this->initDir($token);

		if(!file_exists(APP_DIR_ROOT . '/web/shared/user/' . $token . '/attachments/')){
			mkdir(APP_DIR_ROOT . '/web/shared/user/' . $token . '/attachments/', 0777, true);
		}

		$file = APP_DIR_ROOT . '/web/shared/user/' . $token . '/attachments/' . hash('sha256', $this->getId());
		if(!$data){
			if(file_exists($file)){
				unlink($file);
			}
			return;
		}
		file_put_contents($file, $raw ? $data : file_get_contents($data));
	}

	public function copyAttachment(Attachment $attachment, string $token){
		$this->initDir($token);
		if(!file_exists(APP_DIR_ROOT . '/web/shared/user/' . $token . '/attachments/' . hash('sha256', $attachment->getId()))){
			return;
		}
		copy(
			APP_DIR_ROOT . '/web/shared/user/' . $token . '/attachments/' . hash('sha256', $attachment->getId()),
			APP_DIR_ROOT . '/web/shared/user/' . $token . '/attachments/' . hash('sha256', $this->getId())
		);
	}

	public function removeAttachment(string $token){
		$this->initDir($token);
		$file = APP_DIR_ROOT . '/web/shared/user/' . $token . '/attachments/' . hash('sha256', $this->getId());
		if(!file_exists($file)){
			return;
		}
		unlink(APP_DIR_ROOT . '/web/shared/user/' . $token . '/attachments/' . hash('sha256', $this->getId()));
	}

	public function read(string $token){
		$file = APP_DIR_ROOT . '/web/shared/user/' . $token . '/attachments/' . hash('sha256', $this->getId());
		if(file_exists($file)){
			readfile($file);
		}
	}
}
