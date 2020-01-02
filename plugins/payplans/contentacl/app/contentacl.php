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

class PPAppContentacl extends PPApp
{
	public function isApplicable($refObject = null, $eventName = '')
	{
		$app = JFactory::getApplication();
		$option = $app->input->get('option', '', 'default');
		$view = $app->input->get('view', '', 'default');

		if ($eventName === 'onContentPrepare' && $option == 'com_content' && $view == 'article') {
			return true;
		}

		return false;
	}

	/**
	 * Joomla trigger when viewing an article
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onContentPrepare($context, &$row, &$params, $page = 0)
	{
		$type = $this->getAppParam('block_j17', 'none');

		if ($type == 'none' || !$type) {
			return true;
		}

		if ($this->helper->isUserAllowed() === true) {
			return true;
		}

		// IMPORTANT:
		// since this app will be trigger multiple times
		// we need a controller to controller how the 'non-applyAll' behaviour.
		// each block will responsible to only applyAll. only article block will check for both applyAll and non-applyAll.
		// thats mean to say, article is the controller.
		if ($type == 'joomla_category') {
			$this->helper->processCategory($context, $row, $params, $page);
		}

		if ($type == 'joomla_article') {
			$this->helper->processArticle($context, $row, $params, $page);
		}

		return true;
	}
}
