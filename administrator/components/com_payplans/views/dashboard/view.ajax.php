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
	public function rebuildstats($tpl = null)
	{
		//gets array of dates to process
		$days_to_process = PayplansHelperStatistics::getDaysToProcess();

		//sets $days_to_process for further calculation
		$session = PP::session();
		$session->set('rebuild_total', $days_to_process);

		$theme = PP::themes();

		$theme->set('rebuild_total',$days_to_process);
		$output = $theme->output('admin/dashboard/dialog.rebuildstats');

		return $this->ajax->resolve($output);
	}
}
