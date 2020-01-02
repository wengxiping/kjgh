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

if (JVERSION < 3.8) {
	include_once(__DIR__ . '/base30.php');
}


if (JVERSION >= 3.8) {
	include_once(__DIR__ . '/base38.php');
}

class JMenuSite extends JMenuSiteBase
{
	public function load()
	{
		try {
			parent::load();

			$app = JFactory::getApplication();
			if($app->isSite()) {
				//trigger event for controlling menus
				$menus = $this->_items;
				$args = array(&$menus);
			
				PPEvent::trigger('onPayplansMenusLoad', $args);
				$this->_items = $menus;
			}

		} catch (RuntimeException $e) {
			JError::raiseWarning(500, JText::sprintf('JERROR_LOADING_MENUS', $e->getMessage()));
			return false;
		}

		foreach ($this->_items as &$item) {
			// Get parent information.
			$parent_tree = array();
			if (isset($this->_items[$item->parent_id]))
			{
				$parent_tree  = $this->_items[$item->parent_id]->tree;
			}

			// Create tree.
			$parent_tree[] = $item->id;
			$item->tree = $parent_tree;

			// Create the query array.
			$url = str_replace('index.php?', '', $item->link);
			$url = str_replace('&amp;', '&', $url);

			parse_str($url, $item->query);
		}
	}
}