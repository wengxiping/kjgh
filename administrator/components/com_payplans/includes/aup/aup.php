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

class PPAup
{
	protected $file = JPATH_ROOT . '/components/com_altauserpoints/helper.php';

	/**
	 * Assigns points in AUP
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function assignPoints(PPUser $user, $function, $objectId, $description, $points)
	{
		$id = AltaUserPointsHelper::getAnyUserReferreID($user->getId());

		return AltaUserPointsHelper::newpoints($function, $id, $objectId, $description, $points, '', 1);
	}

	/**
	 * Creates a new rule in AUP
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createRule($name, $description, $plugin, $function)
	{
		$exists = $this->isRuleExists($function);

		if (!$exists) {
			$db = PP::db();
			$query = "INSERT IGNORE INTO `#__alpha_userpoints_rules` (`id`, `rule_name`, `rule_description`, `rule_plugin`, `plugin_function`, `access`, `component`, `calltask`, `taskid`, `points`, `points2`, `percentage`, `rule_expire`, `sections`, `categories`, `content_items`, `exclude_items`, `published`, `system`, `duplicate`, `blockcopy`, `autoapproved`, `fixedpoints`, `category`, `displaymsg`, `msg`, `method`, `notification`, `emailsubject`, `emailbody`, `emailformat`, `bcc2admin`, `type_expire_date`) VALUES
					  ('', '" . $name . "', '" . $description . "', '" . $plugin . "', '" . $function . "', 1, '', '', '', 0.00, 0.00, 0, '0000-00-00 00:00:00', '', '', '', '', 1, 0, 0, 0, 1, 0, 'ot', 0, '', 4, 0, '', '', 0, 0, 0)";
			$db->setQuery($query);
			$db->query();
		}
	}

	/**
	 * Determines if kunena exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exists()
	{
		$enabled = JComponentHelper::isEnabled('com_altauserpoints');
		$exists = JFile::exists($this->file);

		if (!$exists || !$enabled) {
			return false;
		}
		
		require_once($this->file);

		return true;
	}

	/**
	 * Retrieves the total points the user has
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPoints($userId = null, $decimal = false)
	{
		$user = PP::user($userId);
		$helper = new AltaUserPointsHelper();

		$points = $helper->getCurrentTotalPoints('', $user->getId());

		if (!$decimal) {
			$points = round($points);
		}
		
		return $points;
	}

	/**
	 * Determines if there are any rules created on AUP
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isRuleExists($function = 'plgaup_payplans_aupdiscount')
	{
		$db = PP::db();
		$query = 'SELECT COUNT(1) FROM ' . $db->qn('#__alpha_userpoints_rules') . ' WHERE ' . $db->qn('plugin_function') . '=' . $db->Quote($function);
		$db->setQuery($query);
		$exists = $db->loadResult() > 0 ? true : false;
		
		return $exists;
	}
}
