<?php

/*------------------------------------------------------------------------
# com_affiliatetracker - Affiliate Tracker for Joomla
# ------------------------------------------------------------------------
# author				Germinal Camps
# copyright 			Copyright (C) 2014 JoomlaThat.com. All Rights Reserved.
# @license				http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: 			http://www.JoomlaThat.com
# Technical Support:	Forum - http://www.JoomlaThat.com/support
-------------------------------------------------------------------------*/

//no direct access
defined('_JEXEC') or die('Restricted access.');

jimport( 'joomla.application.component.view' );
 
class LogsViewLog extends JViewLegacy
{

	public $_path = array(
		'template' => array()
	);

	function display($tpl = null)
	{
		//cridem el CSS
		$document	= JFactory::getDocument();
		
		//get the invoice
		$log			= $this->get('Data');
		
		
		$params =JComponentHelper::getParams( 'com_affiliatetracker' );
		
		$isNew		= ($log->id < 1);

		$text = $isNew ? JText::_( 'NEW' ) : JText::_( 'EDIT' );
		$title = $isNew ? JText::_( 'Log' ) : $log->name;
		
		JToolBarHelper::title(   $title . ': <small><small>[ ' . $text.' ]</small></small>','logs' );
		
		JToolBarHelper::apply();
		JToolBarHelper::save();
		
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {
			
			JToolBarHelper::cancel( 'cancel', 'Close' );
			
		}
		
		// push data into the template
		$this->assignRef('log',		$log);
		
		// JS
		JHtmlBehavior::framework();
		$document->addScript('components/com_affiliatetracker/assets/items.js');

		parent::display($tpl);
	}
	
	
}