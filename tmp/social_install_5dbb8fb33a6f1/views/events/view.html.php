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

jimport('joomla.filesystem.file');

$lib = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/qrcode/qrlib.php';
$exists = JFile::exists($lib);

if ($exists) {
	require_once($lib);
} else {
	require_once(SOCIAL_APPS . '/user/qrcode/library/qrlib.php');	
}

class QrCodeViewEvents extends SocialAppsView
{
	public function display($clusterId)
	{
		$cluster = ES::cluster(SOCIAL_TYPE_EVENT, $clusterId);
		$permalink = $cluster->getPermalink(false, true);

		echo QRcode::png($permalink);
		exit;
	}
}
