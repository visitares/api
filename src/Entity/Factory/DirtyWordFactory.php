<?php

namespace Visitares\Entity\Factory;

use DateTime;
use Visitares\Entity\DirtyWord;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class DirtyWordFactory{
	/**
	 * @param  array $values
	 * @return Language
	 */
	public function fromArray(array $values){
		$dirtyWord = new DirtyWord;
		foreach($values['word'] as $langCode => $value){
			$dirtyWord->setWord($langCode, $value);
		}
		return $dirtyWord;
	}
}