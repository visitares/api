<?php

namespace Visitares\Entity\Factory;

use Visitares\Entity\Attachment;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class AttachmentFactory{
	/**
	 * @param  array $values
	 * @return array
	 */
	public function fromArray(array $values){
		$attachment = new Attachment;
		$attachment->setName($values['name']);
		$attachment->setMimetype($values['type'] ? $values['type'] : 'text/plain');
		$attachment->setSize($values['size']);
		$attachment->setSort($values['sort'] ?? null);
		return $attachment;
	}
}
