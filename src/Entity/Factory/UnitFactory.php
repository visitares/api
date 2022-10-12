<?php

namespace Visitares\Entity\Factory;

use DateTime;
use Visitares\Entity\Unit;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class UnitFactory{
	/**
	 * @param  array $values
	 * @return Language
	 */
	public function fromArray(array $values){
		$unit = new Unit;
		$unit->setLabel($values['label']);
		return $unit;
	}
}