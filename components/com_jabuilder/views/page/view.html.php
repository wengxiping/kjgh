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
use Joomla\Registry\Registry;

class JabuilderViewPage extends JViewLegacy
{
	public function display($tpl = null) 
	{
		$item = $this->get('Item');

		if( !empty($item)){
			if (is_string($item->params)) $item->params = new Registry($item->params);

			$app = JFactory::getApplication();
			$active       = $app->getMenu()->getActive();

			// Check to see which parameters should take priority
			if ($active)
			{
				$item->params->merge($active->params);
			}

			$this->_set_meta_tag($item);
		} else {
			return;
		}

		$this->item = $item;
 
		parent::display($tpl);
	}
	
	protected function _set_meta_tag($item)
	{

		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();

		$title = $item->params->get('page_title', '');

		// Check for empty title and add site name if param is set
		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		if (empty($title))
		{
			$title = $item->title;
		}

		$this->document->setTitle($title);

		// meta data
		if ($item->params->get('meta_description'))
		{
			$doc->setDescription($item->params->get('meta_description'));
		}
		elseif ($item->params->get('menu-meta_description'))
		{
			$doc->setDescription($item->params->get('menu-meta_description'));
		}

		if ($item->params->get('meta_keywords'))
		{
			$doc->setMetadata('keywords', $item->params->get('meta_keywords'));
		}
		elseif ($item->params->get('menu-meta_keywords'))
		{
			$doc->setMetadata('keywords', $item->params->get('menu-meta_keywords'));
		}

		if ($item->params->get('robots'))
		{
			$doc->setMetadata('robots', $item->params->get('robots'));
		}

	}
}