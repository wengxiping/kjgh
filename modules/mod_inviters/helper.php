<?php
/**
 * @version     SVN: <svn_id>
 * @package     Invitex
 * @subpackage  mod_inviters
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

/**
 * Helper for mod_inviter
 *
 * @package     Invitex
 * @subpackage  mod_inviter
 * @since       3.1.4
 */
abstract class ModInvitexhelper
{
	/**
	 * Retrieve a list of Inviter
	 *
	 * @param   INT  $params  module params
	 *
	 * @return  Object  $items  List of Inviters
	 *
	 * @since   3.1.4
	 */
	public static function getInviters($params)
	{
		$limit = $params->get('no_of_inviters_to_show', 5);

		$helperPath = JPATH_SITE . '/components/com_invitex/helper.php';

		if (!class_exists('cominvitexHelper'))
		{
			// Require_once $path;
			JLoader::register('cominvitexHelper', $helperPath);
			JLoader::load('cominvitexHelper');
		}

		$invhelperObj = new cominvitexHelper;

		// Create a new query object.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('u.username,u.id,SUM(ii.invites_count) as total_sent');
		$query->from('`#__users` AS u');
		$query->innerjoin('#__invitex_imports AS ii on ii.inviter_id =u.id');
		$query->where('ii.invites_count>0');
		$query->group('u.id');
		$query->order('ii.invites_count');
		$query->setLimit($limit);
		$db->setQuery($query);
		$items = $db->loadObjectlist();

		foreach ($items as $k => $v)
		{
			$query = "";
			$items[$k]->total_sent = $invhelperObj->getInvitesSent($v->id);
			$query = $db->getQuery(true);
			$query->select('COUNT(invitee_id) as count');
			$query->from('#__invitex_imports_emails');
			$query->where('inviter_id=' . $v->id);
			$query->where("invitee_id<>0 ");
			$query->where("friend_count<>0");
			$db->setQuery($query);

			if ($db->loadResult())
			{
				$items[$k]->acc	= $db->loadResult();
			}
			else
			{
				$items[$k]->acc	= '0';
			}

			$query = "SELECT sum(click_count)
			FROM #__invitex_imports_emails
			WHERE inviter_id=$v->id";
			$query = $db->getQuery(true);
			$query->select('sum(click_count)');
			$query->from('#__invitex_imports_emails');
			$query->where('inviter_id=' . $v->id);
			$db->setQuery($query);

			if ($db->loadResult())
			{
				$items[$k]->click	= $db->loadResult();
			}
			else
			{
				$items[$k]->click	= '0';
			}
		}

		$sort_by = $params->get('sort_by', 'acc');
		$direction = $params->get('ordering', '-1');
		$items = JArrayHelper::sortObjects($items, $sort_by, $direction);

		return $items;
	}
}
