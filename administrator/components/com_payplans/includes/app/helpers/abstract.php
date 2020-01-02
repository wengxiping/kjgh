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

// This is just a simple helper implementation for standard apps

class PPAppHelperAbstract
{
	protected $params = null;
	protected $error = null;
	protected $app = null;
	protected $coreParams = null;

	public function __construct($params = null, $app = null)
	{
		$this->params = $params;
		$this->app = $app;
		$this->coreParams = $app->getCoreParams();

		$this->input = JFactory::getApplication()->input;
	}

	/**
	 * This determines if the app is applicable to all plans
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isApplicableToAllPlans()
	{
		$applyAll = $this->coreParams->get('applyAll', false);

		return $applyAll;
	}

	/**
	 * Adds a new record into the resource
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function addResource($subscriptionId, $userId, $groupId, $resource, $count = 0)
	{
		$lib = PP::resource();

		return $lib->add($subscriptionId, $userId, $groupId, $resource, $count);
	}

	/**
	 * Get record from resource
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getResource($userId, $groupId, $title)
	{
		$resource = PP::resource();

		return $resource->get($userId, $groupId, $title);
	}

	/**
	 * Allows child to retrieve error message
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getError()
	{
		if (!$this->error) {
			return false;
		}

		return JText::_($this->error);
	}

	/**
	 * Retrieves an app's helper
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAvailableApps($name)
	{
		$apps = PPHelperApp::getAvailableApps($name);

		return $apps;
	}

	/**
	 * This determines if the app is applicable to all plans
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApplicablePlans()
	{
		return $this->app->getPlans();
	}

	/**
	 * Generates standard redirect link to the plans page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRedirectPlanLink($xhtml = false)
	{
		$link = PPR::_('index.php?option=com_payplans&view=plan', $xhtml);

		return $link;
	}

	/**
	 * Allows child to set error message
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setError($message)
	{
		$this->error = $message;
	}

	/**
	 * Removes a resource
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function removeResource($subscriptionId, $userId, $groupId, $resource, $count = 0)
	{
		$lib = PP::resource();
		return $lib->remove($subscriptionId, $userId, $groupId, $resource, $count);
	}
}