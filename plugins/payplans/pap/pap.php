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

jimport('joomla.filesystem.file');

$file = JPATH_ADMINISTRATOR . '/components/com_payplans/includes/payplans.php';

if (!JFile::exists($file)) {
	return;
}

require_once($file);

class plgPayplansPap extends PPPlugins
{
	/**
	 * System trigger, triggered by Joomla
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onAfterRender()
	{
		$apps = $this->getAvailableApps();

		if (!$apps) {
			return;
		}

		foreach ($apps as $app) {
			$helper = $app->getHelper();
			$scripts = $helper->getScripts();

			if ($scripts) {
				$body = JResponse::getBody();
				$body = str_ireplace('</body>', $scripts . '</body>', $body);

				JResponse::setBody($body);
				break;
			}
		}
	}
	
	/**
	 * System trigger, triggered by Joomla
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onAfterRoute()
	{
		$apps = $this->getAvailableApps();

		if (!$apps) {
			return;
		}

		foreach ($apps as $app) {
			$helper = $app->getHelper();
			$name = $helper->getClickTrackingParamName();

			$affiliateId = $this->input->get($name, false);

			if ($affiliateId) {
				break;
			}
		}
		
		if ($affiliateId) {
			$session = PP::session();
			$session->clear('PAPAffiliateId');
			$session->set('PAPAffiliateId', $affiliate_id);
		}
	}
}
