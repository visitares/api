<?php

namespace Visitares\Entity;

use DateTime;

class Config extends AbstractEntity{
	protected $id = null;
	protected $creationDate = null;
	protected $modificationDate = null;
	protected $name = null;
	protected $value = null;

	public function __construct(){
		$this->creationDate = new DateTime;
	}

	public function getValue(){
		return $this->value ? json_decode($this->value) : null;
	}

	public function setValue($value){
		if($value === null){
			$this->value = null;
		} else{
			$this->value = json_encode($value);
		}
	}

	public function jsonSerialize(): mixed {
		return[
			'id' => $this->id,
			'creationDate' => $this->creationDate->format('Y-m-d H:i:s'),
			'modificationDate' => $this->modificationDate ? $this->modificationDate->format('Y-m-d H:i:s') : null,
			'name' => $this->name,
			'value' => $this->getValue()
		];
	}
}