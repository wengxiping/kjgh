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

class SocialFieldsGroupPermalinkHelper
{

	/**
	 * Ensures that the user doesn't try to use a permalink from a menu alias
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public static function allowed($permalink)
	{
		// check if value conflicts with system view alias or not.
		$sysViews = FRoute::getSystemViews();
		if ($permalink && in_array($permalink, $sysViews)) {
			return false;
		}

		return true;
	}


	public static function valid($permalink, $params)
	{
		if (empty($permalink) || preg_match("#[<>\"'%;()\!&_@\. ]#i", $permalink)) {
			return false;
		}

		$forbidden = $params->get('forbidden');

		if (!empty($forbidden)) {
			$words = explode(',', $forbidden);

			foreach ($words as $word) {
				$word = trim($word);

				if( JString::stristr($permalink, $word) !== false) {
					return false;
				}
			}
		}

		return true;
	}

	public static function exists($permalink)
	{
		$db = FD::db();

		$sql = $db->sql();

		$sql->select('#__social_clusters');
		$sql->where('alias' , $permalink);
		$sql->where('cluster_type', SOCIAL_TYPE_GROUP);

		$db->setQuery($sql->getTotalSql());

		$total = $db->loadResult();

		if ($total > 0) {
			return true;
		}

		// Do not allow them to use any "views"
		$views = JFolder::folders(JPATH_ROOT . '/components/com_easysocial/views');

		if (in_array($permalink , $views)) {
			return true;
		}

		return false;
	}
}
