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

class JabuilderModelPage extends JModelItem
{
	function getItem()
	{
		$app = JFactory::getApplication();
		
		$input = $app->input;

		$id = $input->getCmd('id');
		
		$user = JFactory::getUser();
		
		if( empty($id) )
		{
			return JError::raiseError(404, JText::_('JERROR_PAGE_NOT_FOUND'));
		}
		
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		
		$query->select('*')
				->from($db->quoteName('#__jabuilder_pages'))
				->where($db->quoteName('id').'='.$id)
				->where($db->quoteName('parent').'= 0');
		
		$db->setQuery($query);
		
		$item = $db->loadObject();

		if( empty($item) )
		{
			JError::raiseError(404, 'Page not found');
 
			return false;
		}

		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('onJubLoadItem', array (&$item));

		$access = $user->getAuthorisedViewLevels();
		
		if ( !in_array($item->access, $access) )
		{
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			
			$app->setHeader('status', 403, true);
					
			return;
		}

		$groups_can_view_unpublish = array(
			'6' => '6',
			'7' => '7',
			'8' => '8'
		);
		
		if ( $item->state == 0 && 
				count(array_intersect($user->groups, $groups_can_view_unpublish)) == 0 )
		{
			//return JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			
			$app->setHeader('status', 403, true);
			
			return;
		}

		return $item;
	}
}