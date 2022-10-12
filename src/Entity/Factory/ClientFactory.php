<?php

namespace Visitares\Entity\Factory;

use DateTime;
use Visitares\Entity\Client;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class ClientFactory{
	/**
	 * @param  array $values
	 * @return Client
	 */
	public function fromArray(array $values){
		$client = new Client;
		$client->setCreationDate(new DateTime);
		foreach((array)($values['name'] ?? []) as $langCode => $value){
			$client->setName($langCode, $value);
		}
		foreach((array)($values['description'] ?? []) as $langCode => $value){
			$client->setDescription($langCode, $value);
		}
		//$client->setName($values['name']);
		//$client->setDescription($values['description']);
		$client->setIsActive($values['isActive']);
		$client->setLineBreak($values['lineBreak']);
		$client->setDividingLine($values['dividingLine'] ?? false);
		$client->setSort($values['sort']);
		if($values['icon']){
			$client->setIconId($values['icon']['id']);
		}
		return $client;
	}
}