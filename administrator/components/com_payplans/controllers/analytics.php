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

class PayplansControllerAnalytics extends PayPlansController
{
	public function __construct()
	{
		parent::__construct();

		$this->checkAccess('statistics');
	}

	/**
	 * Allow caller to render statistics chart data
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function renderChart()
	{
		$statistics = PP::statistics();

		$duration = $this->input->get('duration', PP_STATS_DURATION_MONTHLY, 'default');
		$type = $this->input->get('type', 'all', 'default');
		$customStartDate = $this->input->get('customStartDate', '', 'default');
		$customEndDate = $this->input->get('customEndDate', '', 'default');
		$dummyData = $this->input->get('dummyData', 0, 'int');

		$graphStatistics = $statistics->getStatisticsGraph($duration, array($customStartDate, $customEndDate), $type, $dummyData);

		return $this->ajax->resolve($graphStatistics);
	}

	/**
	 * Rebuild the statistics data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function rebuildStat()
	{
		$current = $this->input->get('current', 0, 'int');
		$totalDays = $this->input->get('totalDays', 0, 'int');
		$rebuildLimit = $this->input->get('rebuildLimit', 0, 'int');
		$statistics = PP::statistics();

		if (!$current || $current == 0) {
			$statistics->truncateStatistics();
		}

		$statistics->calculateStatistics();

		$completed = $current + $rebuildLimit;

		if ($completed > $totalDays) {
			$completed = $totalDays;
		}

		$message = JText::sprintf('COM_PAYPLANS_DASHBOARD_REBUILD_PROGRESS', $completed, $totalDays);

		return $this->ajax->resolve($message);
	}
}
