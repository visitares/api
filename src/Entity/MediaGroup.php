<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class MediaGroup extends AbstractEntity{
	/** @var int */
	protected $id = null;

	/** @var DateTime */
	protected $creationDate = null;

	/** @var DateTime */
	protected $modificationDate = null;

	/** @var Master */
	protected $master = null;

	/** @var string */
	protected $label = null;

	/** @var string */
	protected $description = null;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
	}
}