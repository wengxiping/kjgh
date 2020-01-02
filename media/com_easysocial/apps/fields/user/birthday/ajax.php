<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

// Include dependencies from our libraries
ES::import('admin:/includes/fields/dependencies');
ES::import('fields:/user/datetime/datetime');

class SocialFieldsUserBirthday extends SocialFieldsUserDateTime
{
	/**
	 * Birthday date validation
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isValid()
	{
		// Render the ajax lib.
		$ajax = ES::ajax();

		$ageLimit = $this->input->get('age_limit', 0, 'int');;

		if ($ageLimit < 1) {
		    return true;
		}

		$value = $this->input->get('value', '', 'string');
		
		$data = $this->getDatetimeValue($value);

		// We don't throw validity error here, leave it up to the parent function to do it
		if (!$data->isValid()) {
		    return $ajax->resolve();
		}

		$now = ES::date()->toUnix();
		$birthDate = $data->toDate()->toUnix();

		$diff = floor(($now - $birthDate) / (60*60*24*365));

		if ($diff < $ageLimit) {
		    return $ajax->reject(JText::sprintf('PLG_FIELDS_BIRTHDAY_VALIDATION_AGE_LIMIT', $ageLimit));
		}

		return $ajax->resolve();
	}

}
