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

class SocialConverseKit extends EasySocial
{
	public static function factory()
	{
		$obj = new self();

		return $obj;
	}

	/**
	 * Determines if Conversekit is enabled
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function exists($currentView = null)
	{
		$exists = JPluginHelper::isEnabled('system', 'conversekit');

		if ($exists && $this->config->get('conversations.conversekit.links')) {

			if ($currentView && $currentView != 'conversations') {
				return true;
			}
		}

		return false;
	}
}