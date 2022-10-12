<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Media extends AbstractEntity{

	const TYPE_VIDEO = 0;
	const TYPE_YOUTUBE = 1;
	const TYPE_OTHER = 2;
	const TYPE_IMAGE = 3;

	/** @var int */
	protected $id = null;

	/** @var DateTime */
	protected $creationDate = null;

	/** @var DateTime */
	protected $modificationDate = null;

	/** @var integer */
	protected $masterId = null;

	/** @var string */
	protected $instanceToken = null;

	/** @var MediaGroup */
	protected $group = null;

	/** @var Language */
	protected $language = null;

	/** @var string */
	protected $label = null;

	/** @var string */
	protected $description = null;

	/** @var integer */
	protected $type = null;

	/** @var string */
	protected $mime = null;

	/** @var string */
	protected $filename = null;

	/** @var string */
	protected $originalFilename = null;

	/** @var string */
	protected $ext = null;

	/** @var integer */
	protected $filesize = null;

	/** @var string */
	protected $length = null;

	public function __construct(){
		$this->creationDate = new DateTime;
	}

	public function setDescription(array $description){
		$this->description = json_encode($description);
	}

	public function getDescription(){
		$desc = json_decode($this->description, true);
		return $desc ? $desc : null;
	}

	public function getTypeLabel(){
		return [
			0 => 'video',
			1 => 'youtube',
			2 => 'other',
			3 => 'image',
		][$this->type] ?? static::TYPE_OTHER;
	}

}