<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class FormMedia extends AbstractEntity{
	/** @var Form */
	protected $form = null;

	/** @var Media */
	protected $media = null;

	/** @var integer */
	protected $sort = null;

	/** @var DateTime */
	protected $creationDate = null;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
	}

}