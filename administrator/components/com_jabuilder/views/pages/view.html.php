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

class JabuilderViewPages extends JViewLegacy
{
	function display($tpl=null)
	{
		if( $this->getLayout() !== 'modal' ) {
			JabuilderHeper::addSubmenu('pages');
		}
		$this->addToolbar();
		$this->items = $this->get('Items');
		//$this->sidebar = JHtmlSidebar::render();
		$this->pagination = $this->get('Pagination');
		parent::display($tpl);
	}
	
	function addToolbar()
	{
		JToolBarHelper::title( 'JA Builder Pages' );
		JToolBarHelper::addNew('page.add');		
		JToolBarHelper::deleteList('Be careful!', 'pages.delete');
		//JToolbarHelper::preferences('com_jabuilder');
		JToolbarHelper::divider();
		/*
		$canDo = JHelperContent::getActions('com_jabuilder');
		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
		}
		*/
	}
}