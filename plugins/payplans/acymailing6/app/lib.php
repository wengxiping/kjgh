<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPAcym
{
	protected $file = JPATH_ROOT . '/administrator/components/com_acym/helpers/helper.php';

	/**
	 * Determines if Acymailing exists on the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exists()
	{
		static $exists = null;

		if (is_null($exists)) {
			$enabled = JComponentHelper::isEnabled('com_acym');
			$fileExists = JFile::exists($this->file);
			$exists = false;

			if ($enabled && $fileExists) {
				$exists = true;
				require_once($this->file);
			}
		}

		return $exists;
	}

	/**
	 * Retrieves a list of Acymailing lists on the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLists()
	{
		$db = PP::db();
		$query = 'SELECT * FROM `#__acym_list` WHERE `active` = 1';
		$db->setQuery($query);
		$lists = $db->loadObjectList();

		return $lists;
	}
}
