<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Request extends AbstractEntity{
	/**
	 * @var integer
	 */
	protected $id = null;

	/**
	 * @var DateTime
	 */
	protected $creationDate = null;

	/**
	 * @var string
	 */
	protected $resource = null;

	/**
	 * @var string
	 */
	protected $userAgent = null;

	/**
	 * @var string
	 */
	protected $headers = null;
}