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

class PayplansControllerDashboard extends PayPlansController
{
	public function __construct()
	{
		parent::__construct();
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
		$graphStatistics = $statistics->getStatisticsGraph(PP_STATS_DURATION_WEEKLY);

		return $this->ajax->resolve($graphStatistics);
	}
}
