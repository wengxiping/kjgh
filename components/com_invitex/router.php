<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Invitex
 * @copyright  Copyright (C) 2005 - 2014. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * Shika is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Routing class from com_invitex
 *
 * @subpackage  com_invitex
 *
 * @since       1.0.0
 */
class InvitexRouter extends JComponentRouterBase
{
	private  $views = array(
						'invites','namecard','resend','stats',
						'urlstats');
	/**
	 * Build the route for the com_invitex component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   1.0.0
	 */
	public function build(&$query)
	{
		$segments = array();

		// Get a menu item based on Itemid or currently active
		$app = JFactory::getApplication();
		$menu = $app->getMenu();
		$params = JComponentHelper::getParams('com_invitex');
		$db = JFactory::getDbo();

		// We need a menu item.  Either the one specified in the query, or the current active one if none specified
		if (empty($query['Itemid']))
		{
			$menuItem = $menu->getActive();
			$menuItemGiven = false;
		}
		else
		{
			$menuItem = $menu->getItem($query['Itemid']);
			$menuItemGiven = true;
		}

		// Check again
		if ($menuItemGiven && isset($menuItem) && $menuItem->component != 'com_invitex')
		{
			$menuItemGiven = false;
			unset($query['Itemid']);
		}

		// Check if view is set.
		if (isset($query['view']))
		{
			$segments[] = $query['view'];
			unset($query['view']);
		}
		else
		{
			// We need to have a view in the query or it is an invalid URL
			return $segments;
		}

		// Check if view is set.
		if (isset($query['tmpl']))
		{
			$segments[] = "invitation";
			unset($query['tmpl']);
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   3.3
	 */
	public function parse(&$segments)
	{
		$vars = array();

		switch ($segments[0])
		{
			case 'invites':
					$vars['view'] = 'invites';

					if (isset($segments[1]))
					{
						if ($segments[1] == 'invitation')
						{
							$vars['tmpl'] = 'component';
						}
					}
					break;
			case 'namecard':
					$vars['view'] = 'namecard';
					break;
			case 'resend':
					$vars['view'] = 'resend';
					break;
			case 'stats':
					$vars['view'] = 'stats';
					break;
			case 'urlstats':
					$vars['view'] = 'urlstats';
					break;
		}

		return $vars;
	}
}
