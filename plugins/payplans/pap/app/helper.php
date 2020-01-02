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

class PPHelperPap extends PPHelperStandardApp
{
	/**
	 * Retrieves the param name for click tracking
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAccountId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('accountId', 'default1');
		}

		return $id;
	}

	/**
	 * Retrieves the param name for click tracking
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAccountUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = $this->params->get('papUrl', '');
		}

		return $url;
	}

	/**
	 * Retrieves the param name for click tracking
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getClickTrackingParamName()
	{
		static $name = null;

		if (is_null($name)) {
			$name = $this->params->get('urlParameter', 'a_aid');
		}

		return $name;
	}

	/**
	 * Retrieves the scripts use for post affiliate pro
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getScripts()
	{
		static $script = null;

		if (is_null($script)) {
			$script = $this->params->get('papScript', '');
		}

		return $script;
	}

	/**
	 * Retrieves the lib for PAP
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSalesTracker()
	{
		require_once(__DIR__ . '/lib/api.php');

		$url = $this->getAccountUrl() . '/scripts/sale.php';
		$tracker = new Pap_Api_SaleTracker($url, $this->isDebug());

		return $tracker;
	}

	/**
	 * Determines if we are on debug mode
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isDebug()
	{
		static $debug = null;

		if (is_null($debug)) {
			$debug = $this->params->get('debug', false);
		}

		return $debug;
	}
}
