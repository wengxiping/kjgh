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

class JabuilderViewPage extends JViewLegacy
{
	function display($tpl=null)
	{
		// Get the Data
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');

		$input = JFactory::getApplication()->input;
		$opt = $input->get('opt');
		if ($opt) {
			$option = json_decode( base64_decode($opt) );
			$title = isset($option->title) ? $option->title: '';
			$alias = isset($option->alias) ? $option->alias: '';
			$this->form->setValue('title','', $title);
			$this->form->setValue('alias','', $alias);
		}
		
		$menuid = $input->get('menuid');
		if ($menuid) {
			$this->form->setValue('id','', $menuid);
			$this->form->setValue('menuid','', $menuid);
		} else if (!empty($this->item->menuid)) {
			$this->form->setValue('id','', $this->item->menuid);
			$this->form->setFieldAttribute('menuid','readonly',true);
		}
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));
 
			return false;
		}
		
		// Set the toolbar
		$this->addToolBar();
		
		parent::display($tpl);
	}
	
	protected function addToolBar()
	{
		$input = JFactory::getApplication()->input;
 
		// Hide Joomla Administrator Main menu
		$input->set('hidemainmenu', true);
 
		$isNew = ($this->item->id == 0);
		
		JToolBarHelper::apply('page.apply');
		
		JToolBarHelper::save('page.save');
		
		JToolBarHelper::cancel(
			'page.cancel',
			$isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE'
		);
		
		if ($isNew)
		{
			$title = 'New Page';
		}
		else
		{
			$title = 'Edit Page';
			
			$bar = JToolbar::getInstance('toolbar');
		
			$layout = new JLayoutFile('toolbar.live');

			$bar->appendButton('Custom', $layout->render(array()), '');
		}
 
		JToolBarHelper::title($title, 'Ja Builder');
	}
}