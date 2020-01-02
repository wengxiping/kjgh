<?php 
/**
 * ------------------------------------------------------------------------
 * JA Builder Package
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;

/**
 * Routing class from com_jabuilder
 *
 * @since  3.3
 */
if (!class_exists('JABuilderRouter')) {
	class JABuilderRouter extends JComponentRouterBase
	{
		/**
		 * Build the route for the com_jabuilder component
		 *
		 * @param   array  &$query  An array of URL arguments
		 *
		 * @return  array  The URL arguments to use to assemble the subsequent URL.
		 *
		 * @since   3.3
		 */
		public function build(&$query)
		{		
			$segments = array();
			$db = JFactory::getDbo();
			$app = JFactory::getApplication();
			$this->menu = $app->getMenu();
			$menuItemGiven = false;

			// We need a menu item.  Either the one specified in the query, or the current active one if none specified
			if (empty($query['Itemid']))
			{
				$menuItem = $this->menu->getActive();
			}
			else
			{
				$menuItem = $this->menu->getItem($query['Itemid']);
				$menuItemGiven = true;
			}

			// Check again
			if ($menuItemGiven && isset($menuItem) && $menuItem->component != 'com_jabuilder')
			{
				$menuItemGiven = false;
				unset($query['Itemid']);
			}

			if (!isset($query['view']) || !isset($query['id']) || $query['view'] != 'page')
			{
				return $segments;
			}

			// Are we dealing with an article or category that is attached to a menu item?
			if (($menuItem instanceof stdClass)
				&& $menuItem->query['view'] == $query['view']
				&& $menuItem->query['id'] == (int) $query['id'])
			{
				unset($query['view']);
				unset($query['id']);
				return $segments;
			}			

			$id = (int) $query['id'];
			$db = JFactory::getDbo();
			$dbQuery = $db->getQuery(true)
				->select('alias')
				->from('#__jabuilder_pages')
				->where('id=' . $id);

			$db->setQuery($dbQuery);
			$alias = $db->loadResult();

			if ($alias) {
				$segments[] = $alias;
			}
			
			unset($query['id']);
			unset($query['view']);
		
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

			if (count($segments) != 1) return $vars;
			$alias = $segments[0];
			$db = JFactory::getDbo();
			$dbQuery = $db->getQuery(true)
				->select('id')
				->from('#__jabuilder_pages')
				->where('alias=' . $db->quote($alias));
			$db->setQuery($dbQuery);
			$id = $db->loadResult();

			// detect itemid

			$vars['view'] = 'page';
			$vars['id'] = $id;

			return $vars;
		}
	}

	/**
	 * Content router functions
	 *
	 * These functions are proxys for the new router interface
	 * for old SEF extensions.
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @deprecated  4.0  Use Class based routers instead
	 */
	function jabuilderBuildRoute(&$query)
	{
		return array();
		$router = new JABuilderRouter;

		return $router->build($query);
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * This function is a proxy for the new router interface
	 * for old SEF extensions.
	 *
	 * @param   array  $segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   3.3
	 * @deprecated  4.0  Use Class based routers instead
	 */
	function jabuilderParseRoute($segments)
	{
		return array();
		$router = new JABuilderRouter;

		return $router->parse($segments);
	}
}