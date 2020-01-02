<?php
/**
 * @version     SVN: <svn_id>
 * @package     Invitex
 * @subpackage  mod_inviters
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

/**
 *  dashboard
 *
 * @package     Invitex
 * @subpackage  mod_inviter
 * @since       3.1.4
 */
class InvitexViewdashboard extends JViewLegacy
{
	/**
	 * Function to display dashboard view
	 *
	 * @param   STRING  $tpl  template
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		// Get download id
		$params = JComponentHelper::getParams('com_invitex');
		$this->downloadid = $params->get('downloadid');

		// Get installed version from xml file
		$xml     = JFactory::getXML(JPATH_COMPONENT . '/invitex.xml');
		$version = (string) $xml->version;
		$this->version = $version;

		// Refresh update site
		$model = $this->getModel('dashboard');

		$model->refreshUpdateSite();
		$this->latestVersion = $model->getLatestVersion();

		global $option,$mainframe;

		$linechart = $this->get('LineChartValues');
		$this->linechart_data = $linechart;

		$xml = JFactory::getXML(JPATH_SITE . '/administrator/components/com_invitex/invitex.xml');
		$this->version = $xml->version;
		$statsforpie = $this->get('statsforpie');
		$this->statsforpie = $statsforpie;
		$i = 0;
		$statsforpiemethod = $this->get('statsforpiemethod');

		if ($statsforpiemethod[0])
		{
			foreach ($statsforpiemethod[0] AS $key => $value)
			{
				$this->statsforpiemethodSent[$i] = new stdClass;

				if ($key == 'SEND_MANUAL')
				{
					$this->statsforpiemethodSent[$i]->label = JText::_($key);
				}
				else
				{
					$this->statsforpiemethodSent[$i]->label = ucwords(str_replace("plug_techjoomlaAPI_", "", $key));
				}

				$this->statsforpiemethodSent[$i]->value = $value;
				$i++;
			}
		}

		$i = 0;

		if ($statsforpiemethod[1])
		{
			foreach ($statsforpiemethod[1] AS $key => $value)
			{
				$this->statsforpiemethodAccepted[$i] = new stdClass;

				if ($key == 'SEND_MANUAL')
				{
					$this->statsforpiemethodAccepted[$i]->label = JText::_($key);
				}
				else
				{
					$this->statsforpiemethodAccepted[$i]->label = ucwords(str_replace("plug_techjoomlaAPI_", "", $key));
				}

				$this->statsforpiemethodAccepted[$i]->value = $value;
				$i++;
			}
		}

		$this->statsforpiemethod = $statsforpiemethod;
		$this->all_time_invites_count	=	$this->get('all_time_invites_count');
		$this->accpeted_invites_count	=	$this->get('All_time_invites_accepted_count');
		$this->inviters_count	=	$this->get('inviters_count');

		if (JFactory::getApplication()->input->get('todate'))
		{
			$to_date = JFactory::getApplication()->input->get('todate');
		}
		else
		{
			$to_date = date('Y-m-d');
		}

		if (JFactory::getApplication()->input->get('fromdate'))
		{
			$from_date = JFactory::getApplication()->input->get('fromdate');
		}
		else
		{
			$from_date = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
		}

		$this->to_date = $to_date;
		$this->from_date = $from_date;

		InvitexHelper::addSubmenu('dashboard');

		if (JVERSION >= 3.0)
		{
			$this->sidebar = JHtmlSidebar::render();
		}

		$this->_setToolBar();
		parent::display($tpl);
	}

	/**
	 * Function to set toolbar
	 *
	 * @return  void
	 */
	public function _setToolBar()
	{
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		if (JVERSION >= '3.0')
		{
			JToolbarHelper::title(JText::_('DASHBOARD'), 'dashboard');
		}
		else
		{
			JToolbarHelper::title(JText::_('DASHBOARD'), 'dashboard.png');
		}

		$canDo = InvitexHelper::getActions();

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_invitex');
		}
	}
}
