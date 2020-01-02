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

class KunenaHelper
{
	/**
	 * Determines if Kunena is installed on the site.
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public static function exists()
	{
		$file = JPATH_ADMINISTRATOR . '/components/com_kunena/api.php';

		if (!JFile::exists($file)) {
			return false;
		}

		// Load Kunena's api file
		require_once($file);

		// Load Kunena's language
		KunenaFactory::loadLanguage('com_kunena.libraries', 'admin');

		return true;
	}
}