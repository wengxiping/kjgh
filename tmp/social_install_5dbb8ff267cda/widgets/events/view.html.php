<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class QrCodeWidgetsEvents extends SocialAppsWidgets
{
	/**
	 * Renders QRCode on event (mobile)
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function mobileAfterIntro($clusterId, $cluster)
	{
		echo $this->render($cluster);
	}

	/**
	 * Renders the QRCode image
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function render($cluster)
	{
		$permalink = ESR::events(array('id' => $cluster->getAlias(),'appId' => $this->app->getAlias(),'layout' => 'item'));

		$this->set('permalink', $permalink);

		return parent::display('widgets/sidebar');
	}

	/**
	 * Renders QRCode on event (desktop)
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function sidebarTop($clusterId, $cluster)
	{
		echo $this->render($cluster);
	}
}
