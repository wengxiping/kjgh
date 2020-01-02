<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/formatter.php');

class PPAppHttpquery extends PPApp
{
	public function onPayplansSubscriptionAfterSave($prev, $new)
	{
		if ($prev != null && $prev->getStatus() == $new->getStatus()) {
			return true;
		}
		
		$query = '';
		$url = '';

		if ($new->isActive()) {
			$query = $this->helper->getQuery('active');
			$url = $this->helper->getUrl('active');
		}

		if ($new->isOnHold()) {
			$query = $this->helper->getQuery('hold');
			$url = $this->helper->getUrl('hold');
		}

		if ($new->isExpired()) {
			$query = $this->helper->getQuery('expire');
			$url = $this->helper->getUrl('expire');
		}

		if (!$query || !$url) {
			return true;
		}

		$this->helper->executeQuery($new, $url, $query);

		return true;
	}
}