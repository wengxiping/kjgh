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

class plgPayplansTos extends PPPlugins
{
	public function onPayplansViewBeforeExecute($view, $task)
	{
		if ($this->app->isAdmin()) {
			return true;
		}

		if (!($view instanceof PayPlansViewCheckout)) {
			return true;
		}

		$apps = $this->getAvailableApps();

		if ($apps) {

			$contents = '';
			$appOutput = '';

			foreach ($apps as $app) {
				
				// Ensure that this app is applicable
				$applicable = $app->isApplicable($view);

				if (!$applicable) {
					continue;
				}

				$title = $app->getAppParam('subject');

				$this->set('appId', $app->getId());
				$this->set('title', $title);
				
				$appOutput .= $this->output('row');
			}

			// if app output is blank then do nothing
			if (!$appOutput) {
				return false;
			}

			$this->set('appOutput', $appOutput);
			$contents = $this->output('form');

			return array('pp-before-actions' => $contents);
		}
	
		return false;
	}
}
