<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class SubmitGroup extends AbstractEntity{
	/**
	 * @var integer
	 */
	protected $id = null;

	/**
	 * @var Submit
	 */
	protected $submit = null;

	/**
	 * @var Group
	 */
	protected $group = null;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		// ..
	}
}