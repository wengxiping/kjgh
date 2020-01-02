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

class PayPlansViewAnalytics extends PayPlansAdminView
{
	public function __construct()
	{
		parent::__construct();

		$this->checkAccess('statistics');
	}

	public function display($tpl=null)
	{
		$layout = $this->input->get('layout', 'sales', 'default');

		$this->heading('analytics_' . $layout);

		JToolbarHelper::custom('updateStat', '', '', 'COM_PAYPLANS_REFRESH_TOOLBAR', false);
		JToolbarHelper::custom('rebuildStat', '', '', 'COM_PAYPLANS_PPRECREATE_TOOLBAR', false);

		$duration = $this->input->get('duration', PP_STATS_DURATION_LAST_30_DAYS, 'default');
		$customStartDate = $this->input->get('customStartDate', '', 'default');
		$customEndDate = $this->input->get('customEndDate', '', 'default');
		$dateRange = $this->input->get('daterange', array(), 'array');
		$dummyData = $this->input->get('dummyData', 0, 'int');

		$start = PP::normalize($dateRange, 'start', '');
		$end = PP::normalize($dateRange, 'end', '');

		if (!$start && !$end) {
			$dateRange = PP::statistics()->getFirstAndLastDate($duration, array($customStartDate, $customEndDate));

			$start = PP::normalize($dateRange, 'start', '');
			$end = PP::normalize($dateRange, 'end', '');
		}

		if ($start && $end) {
			$dateRange = array();
			$dateRange['start'] = PP::date($start)->toSql();
			$dateRange['end'] = PP::date($end)->toSql();

			// Tell the library to use custom to support various date range
			$duration = PP_STATS_DURATION_CUSTOM;
			$customStartDate = $dateRange['start'];
			$customEndDate = $dateRange['end'];
		}

		$type = $layout == 'sales' ? 'all' : $layout;

		$this->set('type', $type);
		$this->set('duration', $duration);
		$this->set('customStartDate', $customStartDate);
		$this->set('customEndDate', $customEndDate);
		$this->set('dateRange', $dateRange);
		$this->set('dummyData', $dummyData);

		parent::display('analytics/default');
	}
}
