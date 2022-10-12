<?php

namespace Visitares\Entity\Factory;

use DateTime;
use Visitares\Entity\Instance;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class InstanceFactory{
	/**
	 * @param  array $values
	 * @return Instance
	 */
	public function fromArray(array $values){

		$instance = new Instance;
		$instance->setCreationDate(new DateTime);
		$instance->setIsActive($values['isActive']);
		$instance->setIsTemplate($values['isTemplate']);
		$instance->setCustomerNumber($values['customerNumber']);
		$instance->setDomain($values['domain']);
		$instance->setName($values['name']);
		$instance->setShortDescription($values['shortDescription']);
		$instance->setDescription($values['description']);
		$instance->setStatsDayRange($values['statsDayRange']);
		$instance->setStatsMinUserCount($values['statsMinUserCount']);
		$instance->setUsersCountByContract($values['usersCountByContract']);
		$instance->setMessageAdministration(false);
		$instance->setLogoffTimer($values['logoffTimer']);
		$instance->setCountry($values['country']);
		$instance->setPostalCode($values['postalCode']);
		$instance->setCity($values['city']);
		$instance->setStreet($values['street']);
		$instance->setSector($values['sector']);
		$instance->setLogo($values['logo']);
		$instance->setMessageModule(false);
		$instance->setDefaultRegistrationRole($values['defaultRegistrationRole']);
		$instance->setNotifyEmail($values['notifyEmail']);
		$instance->setAppSendDeeplinks($values['appSendDeeplinks'] ?? true);

		$instance->setAppDefaultUserMode($values['appDefaultUserMode'] ?? 'anonymous');
		$instance->setShowMyProcesses($values['showMyProcesses'] ?? true);
		$instance->setShowAppAnonymousButton($values['showAppAnonymousButton'] ?? true);
		$instance->setShowAppUserSettings($values['showAppUserSettings'] ?? true);
		$instance->setShowAppLogout($values['showAppLogout'] ?? true);

		$instance->setShowFormSearch($values['showFormSearch'] ?? true);
		$instance->setShowFormSearchShortDescription($values['showFormSearchShortDescription'] ?? true);
		$instance->setShowFormSearchDescription($values['showFormSearchDescription'] ?? true);

		$instance->setAllowInstructions($values['allowInstructions'] ?? false);

		if($values['background']){
			$instance->setBackgroundId($values['background']['id']);
		}

		return $instance;
	}
}
