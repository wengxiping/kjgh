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

class PayPlansViewDashboard extends PayPlansAdminView
{
	public function display($tpl = null)
	{
		$this->heading('Dashboard');

		// Add Joomla button
		if ($this->my->authorise('core.admin', 'com_payplans')) {
			JToolbarHelper::preferences('com_payplans');
		}

		$statistics = PP::statistics();

		// Debug
		// $graphStatistics = $statistics->getStatisticsGraph(PP_STATS_DURATION_WEEKLY);
		// dump($graphStatistics);

		$chart = $statistics->getStatistics(PP_STATS_DURATION_WEEKLY);

		$this->set('statistics', $chart);

		parent::display('dashboard/default');
	}
}
