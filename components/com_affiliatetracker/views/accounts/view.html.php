<?php

/*------------------------------------------------------------------------
# com_invoices - Invoices for Joomla
# ------------------------------------------------------------------------
# author				Germinal Camps
# copyright 			Copyright (C) 2012 JoomlaFinances.com. All Rights Reserved.
# @license				http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: 			http://www.JoomlaFinances.com
# Technical Support:	Forum - http://www.JoomlaFinances.com/forum
-------------------------------------------------------------------------*/

//no direct access
defined('_JEXEC') or die('Restricted access.');

jimport( 'joomla.application.component.view');

class AffiliateViewAccounts extends JViewLegacy
{
	public $_path = array(
		'template' => array(),
		'helper' => array()
	);

	function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		$uri	= JFactory::getURI();

		$pathway	= $mainframe->getPathway();
		$document	= JFactory::getDocument();

		$params =JComponentHelper::getParams( 'com_affiliatetracker' );

		$items		=  $this->get( 'Data');

		$pagination =  $this->get('Pagination');

		$this->assignRef('items',		$items);

		$this->assignRef('pagination', $pagination);

		$accounts =  AffiliateHelper::getAccountList();
		$this->assignRef('accounts', $accounts);

		$types =  AffiliateHelper::getTypeList();
		$this->assignRef('types', $types);

		$this->assignRef('params', $params);

		$filter_order     = $mainframe->getUserStateFromRequest( 'affiliate.accounts.filter_order', 'filter_order', 'acc.id', 'cmd' );
        $filter_order_Dir = $mainframe->getUserStateFromRequest( 'affiliate.accounts.filter_order_Dir', 'filter_order_Dir', 'desc', 'word' );

		$lists['date_in']     = $this->get( 'DateIn');
		$lists['date_out']    = $this->get( 'DateOut');

		$lists['account_id']    = $this->get( 'AccountId');
		$lists['type_id']    = $this->get( 'TypeId');

		$lists['order']     = $filter_order;
		$lists['order_Dir'] = $filter_order_Dir;

		$this->assignRef( 'lists',	$lists );

		$invite_num = AffiliateHelper::getInviteList();


		$logs = $this->get( 'Logs');
		$conversions = $this->get( 'Conversions');
		$comission_value = $this->get( 'Comission');

		$this->assignRef( 'conversions',	$conversions );
		$this->assignRef( 'comission_value',	$comission_value );
		$this->assignRef( 'logs',	$logs );
		$this->assignRef( 'invite_num',	$invite_num );

		$timespan = $this->get( 'Timespan');

		$this->assignRef( 'timespan',	$timespan );

		JHTML::_('behavior.modal');

		$document->addStyleSheet('components/com_affiliatetracker/assets/styles.css');
		$document->addScript('components/com_affiliatetracker/assets/accounts.js');

		parent::display($tpl);
	}


}
?>
