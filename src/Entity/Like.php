<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Like extends AbstractEntity{
	protected $id = null;

	protected $post = null;
	protected $user = null;
}