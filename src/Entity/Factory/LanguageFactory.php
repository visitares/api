<?php

namespace Visitares\Entity\Factory;

use DateTime;
use Visitares\Entity\Language;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class LanguageFactory{
	/**
	 * @param  array $values
	 * @return Language
	 */
	public function fromArray(array $values){
		$language = new Language;
		$language->setIsDefault($values['isDefault']);
		$language->setCode(strtolower($values['code']));
		$language->setLabel($values['label']);
		return $language;
	}
}