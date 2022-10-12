<?php

namespace Visitares\Entity\Validation;

use Visitares\Entity\User;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class UserValidator{
	/**
	 * @param  User $user
	 * @return boolean
	 */
	public function validate(User $user){
		return true;
	}
}