<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Value extends AbstractEntity{
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
	 * @var Submit
	 */
	protected $submit = null;

	/**
	 * @var Input
	 */
	protected $input = null;

	/**
	 * @var Option
	 */
	protected $option = null;

	/**
	 * @var boolean
	 */
	protected $checked = null;

	/**
	 * @var string
	 */
	protected $text = null;

	/**
	 * @var float
	 */
	protected $coefficient = null;

	/**
	 * Intializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
	}

	/**
	 * @param string $coefficient
	 */
	public function setCoefficient($coefficient){
		if($coefficient){
			if(is_string($coefficient)){
				$coefficient = str_replace(',', '.', $coefficient);
			}
			$this->coefficient = floatval($coefficient);
		} else{
			$this->coefficient = null;
		}
	}
}