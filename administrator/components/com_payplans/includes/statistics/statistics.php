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

class PPStatistics extends PayPlans
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Retrieve adapter
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getAdapter($type)
	{
		static $adapter = null;

		if (!isset($adapter[$type])) {
			$fileName = strtolower($type);

			$helperFile	= dirname(__FILE__) . '/adapters/' . $fileName . '.php';
			require_once($helperFile);

			$className = 'PayPlansStatistics' . ucfirst($type);

			$adapter[$type] = new $className();
		}

		return $adapter[$type];
	}

	/**
	 * Retrieve statistic that are optimized for dashboard page
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getStatistics($duration = PP_STATS_DURATION_LIFETIME)
	{
		// Update any new stats
		$this->calculateStatistics();

		$stats = new stdClass();
		$stats->totalSales = 0;
		$stats->totalRevenue = 0;
		$stats->totalRenewals = 0;
		$stats->totalUpgrades = 0;
		$stats->currentActiveSubscription = 0;
		$stats->currentExpiredSubscription = 0;

		// Get graph statistics for current month
		$dateRange = $this->getFirstAndLastDate($duration);
		$startDate = $dateRange->start;
		$endDate = $dateRange->end;
		$allActiveExpiredSubscription = $this->getAllActiveExpiredSubscription($startDate, $endDate);

		// Fetch this using simple query instead.
		$results = $this->getPlanDataWithinDates($startDate, $endDate);
		$data = array();

		if ($results) {
			foreach ($results as $record) {
				$stats->totalSales += intval($record->sales);
				$stats->totalRevenue += floatval($record->revenue);
				$stats->totalRenewals += intval($record->renewals);
				$stats->totalUpgrades += intval($record->upgrades);
			}
		}

		if (isset($allActiveExpiredSubscription[PP_SUBSCRIPTION_ACTIVE])) {
			$stats->currentActiveSubscription = $allActiveExpiredSubscription[PP_SUBSCRIPTION_ACTIVE]->count;
		}

		if (isset($allActiveExpiredSubscription[PP_SUBSCRIPTION_EXPIRED])) {
			$stats->currentExpiredSubscription = $allActiveExpiredSubscription[PP_SUBSCRIPTION_EXPIRED]->count;
		}

		return $stats;
	}

	/**
	 * Retrieve data for statistics graph
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getStatisticsGraph($duration = PP_STATS_DURATION_MONTHLY, $dateRange = array(), $type = PP_STATISTICS_TYPE_ALL, $dummyData = null)
	{
		// debug
		if ($dummyData) {
			return $this->generateDummyData();
		}

		// Update any new stats
		$this->calculateStatistics();

		// Get graph statistics for current month
		$dateRange = $this->getFirstAndLastDate($duration, $dateRange);
		$startDate = $dateRange->start;
		$endDate = $dateRange->end;

		if ($type == PP_STATISTICS_TYPE_GROWTH) {
			$results = $this->getSubscriptionDataWithinDates($startDate, $endDate);
		} else {
			$results = $this->getPlanDataWithinDates($startDate, $endDate, $type);
		}

		// Format the graph
		$stats = $this->formatStatisticsGraph($results, $startDate, $endDate, $duration, $type, $dummyData);

		return $stats;
	}

	/**
	 * Format the data for statistics graphs
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function formatStatisticsGraph($results, $startDate, $endDate, $duration, $type = PP_STATISTICS_TYPE_ALL)
	{
		// Specifically used for sales and dashboard chart
		if ($type == PP_STATISTICS_TYPE_ALL) {
			return $this->formatStatisticsGraphAll($results, $startDate, $endDate, $duration);
		}

		if ($type == PP_STATISTICS_TYPE_GROWTH) {
			return $this->formatStatisticsGraphGrowth($results, $startDate, $endDate, $duration);
		}

		// Generic graph format for other chart type
		return $this->formatStatisticsGraphGeneric($results, $startDate, $endDate, $duration, $type);
	}

	/**
	 * Format the statistics graph for All type
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function formatStatisticsGraphAll($results, $startDate, $endDate, $duration)
	{
		$currency = PP::getCurrency($this->config->get('currency'))->symbol;

		$salesFigure = array();
		$plansFigure = array();
		$planColor = array();
		$total = 0;
		$totalRevenue = 0;
		$totalUnits = 0;

		if ($results) {
			foreach ($results as $record) {

				$date = PP::date($record->statistics_date);
				$formattedDate = $date->format('d M');

				// Format sales figures
				$cumulativeSalesRevenue = 0;
				$cumulativeSalesUnits = 0;

				if (isset($salesFigure[$formattedDate])) {
					$cumulativeData = $salesFigure[$formattedDate];
					$cumulativeSalesRevenue = $cumulativeData['total_1'];
					$cumulativeSalesUnits = $cumulativeData['total_2'];
				}

				// Display the data if there is at least one figure to show
				$totalRevenue += $record->revenue;
				$totalUnits += $record->sales;

				// Display the data if there is at least one figure to show
				$revenue = $record->revenue + $cumulativeSalesRevenue;
				$units = $record->sales + $cumulativeSalesUnits;

				$formattedRevenue = PPFormats::amount($revenue, $currency);

				$salesData = array(
							'date' => $date,
							'tooltip_title' => $date->format('l, F d, Y'),
							'tooltip_text' => JText::sprintf('COM_PP_SALES_GRAPH_TOOLTIP', $formattedRevenue, $units),
							'total_1' => $revenue,
							'total_2' => $units
						);

				$salesFigure[$formattedDate] = $salesData;

				if (!$record->sales) {
					continue;
				}

				// Format plans figures
				$cumulativePlansRevenue = 0;
				$cumulativePlansUnits = 0;

				if (isset($plansFigure[$record->plan_id])) {
					$cumulativeData = $plansFigure[$record->plan_id];
					$cumulativePlansRevenue = $cumulativeData['total_1'];
					$cumulativePlansUnits = $cumulativeData['total_2'];
				} else {
					$planColor[$record->plan_id] = $total;
					$total++;
				}

				$revenue = $record->revenue + $cumulativePlansRevenue;
				$units = $record->sales + $cumulativePlansUnits;

				$formattedRevenue = PPFormats::amount($revenue, $currency);

				$originalTitle = JText::_($record->title);
				$shortTitle = $originalTitle;
				if ((JString::strlen(preg_replace('/<.*?>/', '', $shortTitle)) >= 15)) {
					$shortTitle = JString::substr($shortTitle, 0, 15) . JText::_('COM_PP_ELLIPSES');
				}

				$plansData = array(
							'title' => $originalTitle,
							'shortTitle' => $shortTitle,
							'tooltip_text' => JText::sprintf('COM_PP_SALES_GRAPH_TOOLTIP', $formattedRevenue, $units),
							'total_1' => $revenue,
							'total_2' => $units,
							'background_color' => $this->getChartLabelColor($planColor[$record->plan_id])
						);

				$plansFigure[$record->plan_id] = $plansData;
			}
		}

		$startDate = PP::date($startDate);
		$endDate = PP::date($endDate);
		$startDateFormat = $startDate->format('F d, Y');
		$endDateFormat = $endDate->format('F d, Y');

		// For weekly we need to display a minimum of 7 days
		if ($duration == PP_STATS_DURATION_WEEKLY) {
			$newSalesFigure = array();

			for ($i = 0; $i < 7; $i++) { 
				$date = PP::date(strtotime('+' . $i . ' days', $startDate->toUnix()));
				$formattedDate = $date->format('d M');

				if (isset($salesFigure[$formattedDate])) {
					$newSalesFigure[$formattedDate] = $salesFigure[$formattedDate];
					continue;
				}

				$formattedRevenue = PPFormats::amount(0, $currency);

				$salesData = array(
							'date' => $date,
							'tooltip_title' => $date->format('l, F d, Y'),
							'tooltip_text' => JText::sprintf('COM_PP_SALES_GRAPH_TOOLTIP', $formattedRevenue, 0),
							'total_1' => 0,
							'total_2' => 0,
							'currency' => $currency
						);

				$newSalesFigure[$formattedDate] = $salesData;
			}

			$salesFigure = $newSalesFigure;
		}

		$stats = new stdClass();
		$stats->chartTitle = JText::sprintf('COM_PP_CHART_SALES_TITLE', $startDateFormat, $endDateFormat);
		$stats->chartFigure = $totalRevenue && !empty($salesFigure) ? $salesFigure : false;
		$stats->plansFigure = $totalRevenue && !empty($plansFigure) ? $plansFigure : false;

		// Render label for chart
		$theme = PP::themes();
		$theme->set('icon', 'fa-money-bill-alt');
		$theme->set('iconKey', '2');
		$theme->set('labelTitle', JText::_('COM_PP_CHART_COLUMN_TOTAL_REVENUE'));
		$theme->set('labelValue', PP::themes()->html('html.amount', $totalRevenue, PPFormats::currency(PP::getCurrency())));

		$stats->chartFigureLabel = $theme->output('admin/analytics/charts/labels/generic');

		// Render plan label
		$theme = PP::themes();
		$theme->set('icon', 'fa-cart-arrow-down');
		$theme->set('iconKey', '1');
		$theme->set('labelTitle', JText::_('COM_PP_CHART_COLUMN_TOTAL_UNITS'));
		$theme->set('labelValue', $totalUnits);

		$stats->plansFigureLabel = $theme->output('admin/analytics/charts/labels/generic');

		// Render the listing.
		$theme = PP::themes();
		$theme->set('results', array_reverse($salesFigure));

		$stats->listings = $theme->output('admin/analytics/charts/listings/sales');

		return $stats;
	}

	/**
	 * Format the statistics graph for upgrades
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function formatStatisticsGraphGeneric($results, $startDate, $endDate, $duration, $type)
	{
		$chartFigure = array();
		$plansFigure = array();
		$planColor = array();
		$total = 0;
		$totalItems = 0;

		if ($results) {
			foreach ($results as $record) {

				$date = PP::date($record->statistics_date);
				$formattedDate = $date->format('d M');

				// Format sales figures
				$cumulativeItems = 0;

				if (isset($chartFigure[$formattedDate])) {
					$cumulativeData = $chartFigure[$formattedDate];
					$cumulativeItems = $cumulativeData['total_1'];
				}

				$totalItems += $record->$type;
				$items = $record->$type + $cumulativeItems;

				$itemsDateData = array(
							'date' => $date,
							'tooltip_title' => $date->format('l, F d, Y'),
							'tooltip_text' => JText::sprintf('COM_PP_' . strtoupper($type) . '_GRAPH_TOOLTIP', $items),
							'total_1' => $items
						);

				$chartFigure[$formattedDate] = $itemsDateData;

				if (!$record->$type) {
					continue;
				}

				// Format plans figures
				$cumulativePlansItems = 0;

				if (isset($plansFigure[$record->plan_id])) {
					$cumulativeData = $plansFigure[$record->plan_id];
					$cumulativePlansItems = $cumulativeData['total_2'];
				} else {
					$planColor[$record->plan_id] = $total;
					$total++;
				}

				$plansItems = $record->$type + $cumulativePlansItems;

				$originalTitle = JText::_($record->title);
				$shortTitle = $originalTitle;
				
				if ((JString::strlen(preg_replace('/<.*?>/', '', $shortTitle)) >= 15)) {
					$shortTitle = JString::substr($shortTitle, 0, 15) . JText::_('COM_PP_ELLIPSES');
				}

				$plansData = array(
							'title' => $originalTitle,
							'shortTitle' => $shortTitle,
							'tooltip_text' => JText::sprintf('COM_PP_' . strtoupper($type) . '_GRAPH_TOOLTIP', $plansItems),
							'total_2' => $plansItems,
							'background_color' => $this->getChartLabelColor($planColor[$record->plan_id])
						);

				$plansFigure[$record->plan_id] = $plansData;
			}
		}

		$startDate = PP::date($startDate);
		$endDate = PP::date($endDate);
		$startDateFormat = $startDate->format('F d, Y');
		$endDateFormat = $endDate->format('F d, Y');

		$stats = new stdClass();
		$stats->chartTitle = JText::sprintf('COM_PP_CHART_' . strtoupper($type) . '_DATE_TITLE', $startDateFormat, $endDateFormat);
		$stats->chartFigure = $totalItems && !empty($chartFigure) ? $chartFigure : false;
		$stats->plansFigure = $totalItems && !empty($plansFigure) ? $plansFigure : false;

		// Get icons and icon key
		$icons = $this->getLabelIcons($type);

		$theme = PP::themes();
		$theme->set('icon', $icons['icon']);
		$theme->set('iconKey', $icons['key']);
		$theme->set('labelTitle', JText::_('COM_PP_CHART_COLUMN_TOTAL_' . strtoupper($type)));
		$theme->set('labelValue', $totalItems);

		$stats->chartFigureLabel = $theme->output('admin/analytics/charts/labels/generic');
		$stats->plansFigureLabel = false;

		// Render the listing.
		$theme = PP::themes();
		$theme->set('results', array_reverse($chartFigure));
		$theme->set('type', $type);

		$stats->listings = $theme->output('admin/analytics/charts/listings/generic');

		return $stats;
	}

	/**
	 * Format the statistics chart for subscription growth
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function formatStatisticsGraphGrowth($results, $startDate, $endDate, $duration)
	{
		$chartFigure = array();
		$totalActive = 0;
		$totalExpire = 0;

		if ($results) {
			foreach ($results as $record) {
				$date = PP::date($record->statistics_date);
				$formattedDate = $date->format('d M');

				$active = (int) $record->active;
				$expire = (int) $record->expire;

				$totalActive += $active;
				$totalExpire += $expire;

				$chartData = array(
								'date' => $date,
								'tooltip_title' => $date->format('l, F d, Y'),
								'tooltip_text' => JText::sprintf('COM_PP_GROWTH_GRAPH_TOOLTIP', $active, $expire),
								'total_1' => $active,
								'total_2' => $expire
							);

				$chartFigure[$formattedDate] = $chartData;
			}
		}

		$startDate = PP::date($startDate);
		$endDate = PP::date($endDate);
		$startDateFormat = $startDate->format('F d, Y');
		$endDateFormat = $endDate->format('F d, Y');

		$stats = new stdClass();
		$stats->chartTitle = JText::sprintf('COM_PP_CHART_GROWTH_DATE_TITLE', $startDateFormat, $endDateFormat);
		$stats->chartFigure = $chartFigure;

		$theme = PP::themes();
		$theme->set('totalActive', $totalActive);
		$theme->set('totalExpire', $totalExpire);

		$stats->chartFigureLabel = $theme->output('admin/analytics/charts/labels/growth');
		$stats->plansFigureLabel = '';

		// Render the listing.
		$theme = PP::themes();
		$theme->set('results', array_reverse($chartFigure));

		$stats->listings = $theme->output('admin/analytics/charts/listings/growth');

		return $stats;
	}

	/**
	 * Use to generate dummy data for debugging purpose
	 *
	 * @since	4.0.0
	 * @access	private
	 */
	private function generateDummyData()
	{
		$currency = PP::getCurrency($this->config->get('currency'))->symbol;

		$startDate = PP::date('05-08-2018');
		$endDate = PP::date('11-08-2018');
		$startDateFormat = $startDate->format('F d, Y');
		$endDateFormat = $endDate->format('F d, Y');

		$salesFigure = array();
		$plansFigure = array();

		for ($i = 0; $i < 7; $i++) { 
			$date = PP::date(strtotime('+' . $i . ' days', $startDate->toUnix()));
			$formattedDate = $date->format('d M');

			$revenue = rand(0,100);
			$units = rand(0,15);

			$formattedRevenue = PPFormats::amount($revenue, $currency);

			$salesData = array(
						'date' => $date,
						'tooltip_title' => $date->format('l, F d, Y'),
						'tooltip_text' => JText::sprintf('COM_PP_SALES_GRAPH_TOOLTIP', $formattedRevenue, $units),
						'total_1' => $revenue,
						'total_2' => $units
					);

			$plansData = array(
						'title' => 'Plan ' . $i,
						'shortTitle' => 'Plan ' . $i,
						'tooltip_text' => JText::sprintf('COM_PP_SALES_GRAPH_TOOLTIP', $formattedRevenue, $units),
						'total_1' => $revenue,
						'total_2' => $units,
						'background_color' => $this->getChartLabelColor($i)
					);

			$salesFigure[$formattedDate] = $salesData;
			$plansFigure[$formattedDate] = $plansData;
		}

		$stats = new stdClass();
		$stats->chartTitle = JText::sprintf('COM_PP_CHART_SALES_TITLE', $startDateFormat, $endDateFormat);
		$stats->chartFigure = $salesFigure;
		$stats->plansFigure = $plansFigure;

		$theme = PP::themes();
		$theme->set('icon', 'fa-money-bill-alt');
		$theme->set('iconKey', '2');
		$theme->set('labelTitle', JText::_('COM_PP_CHART_COLUMN_TOTAL_REVENUE'));
		$theme->set('labelValue', PP::themes()->html('html.amount', 987, PPFormats::currency(PP::getCurrency())));

		$stats->chartFigureLabel = $theme->output('admin/analytics/charts/labels/generic');

		$theme = PP::themes();
		$theme->set('icon', 'fa-cart-arrow-down');
		$theme->set('iconKey', '1');
		$theme->set('labelTitle', JText::_('COM_PP_CHART_COLUMN_TOTAL_UNITS'));
		$theme->set('labelValue', 100);

		$stats->planFigureLabel = $theme->output('admin/analytics/charts/labels/generic');

		return $stats;
	}

	/**
	 * Get the label icons for specific chart type
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLabelIcons($type)
	{
		$icons = array(
					PP_STATISTICS_TYPE_RENEWALS => array('icon' => 'fa-redo', 'key' => '1'),
					PP_STATISTICS_TYPE_UPGRADES => array('icon' => 'fa-arrow-circle-up', 'key' => '1')
				);

		return $icons[$type];
	}

	/**
	 * Retrieve first and last date
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getFirstAndLastDate($duration = PP_STATS_DURATION_WEEKLY, $customDates = array(), $string = false)
	{
		$year = date("Y");
		$month = date("m");
		$day = date("d");
		$date = mktime(0, 0, 0, $month, $day, $year);
		$current = PP::date();

		$dateRange = new stdClass();
		$dateRange->start = '0000-00-00 00:00:00';
		$dateRange->end = $current->toSql();

		// Current date will be the end date
		if ($duration == PP_STATS_DURATION_LIFETIME) {
			return $dateRange;
		}

		if ($duration == PP_STATS_DURATION_WEEKLY) {
			$dateRange->start = $this->getStartWeekDates($current);
			$dateRange->end = $current;
		}

		if ($duration == PP_STATS_DURATION_DAILY) {
			$dateRange->start = PP::date(mktime(0, 0, 0, $month, $day, $year));
			$dateRange->end = PP::date(mktime(23, 59, 59, $month, $day, $year));
		}

		if ($duration == PP_STATS_DURATION_MONTHLY) {
			$dateRange->start = $this->getStartMonthDates($month, $year);
			$dateRange->end = $this->getEndMonthDates($month, $year);
		}

		if ($duration == PP_STATS_DURATION_YEARLY) {
			$dateRange->start = $this->getStartYearDates($year);
			$dateRange->end = $this->getEndYearDates($year);
		}

		if ($duration == PP_STATS_DURATION_LAST_30_DAYS) {
			$dateRange->start = $this->getLast30Days($current);
			$dateRange->end = $current;
		}

		if ($duration == PP_STATS_DURATION_CUSTOM) {

			list($startDate, $endDate) = $customDates;
			$dateRange->start = PP::date($startDate);
			$dateRange->end = PP::date($endDate);
		}

		if ($dateRange->start instanceof PPDate) {
			$dateRange->start = $dateRange->start->toSql();
		}

		if ($dateRange->end instanceof PPDate) {
			$dateRange->end = $dateRange->end->toSql();
		}

		// Return the range in form of string
		if ($string) {
			$startDate = PP::date($dateRange->start);
			$startString = $startDate->format("Y-m-d");

			$endDate = PP::date($dateRange->end);
			$endString = $endDate->format("Y-m-d");

			return $startString . ' - ' . $endString;
		}

		return $dateRange;
	}

	/**
	 * Get start week dates
	 *
	 * @since	4.0
	 * @access	private
	 */
	private function getStartWeekDates($current)
	{
		// Only minus 6 days from current date as we need to include current date as well
		$startDate = PP::date(strtotime("-6 days", $current->toUnix()));
		return $startDate;
	}

	/**
	 * Get the last 30 days from current
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLast30Days($current)
	{
		$date = PP::date(strtotime("-29 days", $current->toUnix()));
		return $date;
	}

	/**
	 * Get start month dates
	 *
	 * @since	4.0
	 * @access	private
	 */
	private function getStartMonthDates($month, $year)
	{
		$startDate = PP::date(mktime(0,0,0,$month, 1, $year));
		return $startDate;
	}

	/**
	 * Get start year dates
	 *
	 * @since	4.0
	 * @access	private
	 */
	private function getStartYearDates($year)
	{
		$startDate = PP::date(mktime(0, 0, 0, 1, 1, $year));
		return $startDate;
	}

	/**
	 * Get previous month dates
	 *
	 * @since	4.0
	 * @access	private
	 */
	private function getEndMonthDates($month, $year)
	{
		$endDate = PP::date(mktime(23, 59, 59, $month + 1, 0, $year));
		return $endDate;
	}

	/**
	 * Get previous year dates
	 *
	 * @since	4.0
	 * @access	private
	 */
	private function getEndYearDates($year)
	{
		$endDate = PP::date(mktime(23, 59, 59, 1, 0, $year));
		return $endDate;
	}

	/**
	 * Retrieve data for ActiveExpired Subscriptions
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getAllActiveExpiredSubscription($firstDate, $lastDate)
	{
		$model = PP::model('Statistics');
		$results = $model->getAllActiveExpiredSubscription($firstDate, $lastDate);

		return $results;
	}

	/**
	 * Get sum of sales and revenue in between two dates
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getPlanDataWithinDates($firstDate, $lastDate, $type = PP_STATISTICS_TYPE_ALL)
	{
		$model = PP::model('Statistics');
		$results = $model->getPlanDataWithinDates($firstDate, $lastDate, $type);

		return $results;
	}

	/**
	 * Retrieve data for active expired subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSubscriptionDataWithinDates($firstDate, $endDate)
	{
		$model = PP::model('Statistics');
		$results = $model->getSubscriptionDataWithinDates($firstDate, $endDate);

		return $results;
	}

	/**
	 * Get statistics data per plans
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getDataPerPlan($duration = PP_STATS_DURATION_MONTHLY, $currentFirstDate, $currentLastDate)
	{
		$model = PP::model('Statistics');
		$results = $model->getSumOfRecords();

		// Format the data
		$data['plans'] = array();
		$totalSales = 0; // count_1
		$totalRevenue = 0; // count_2
		$totalRenewals = 0; // count_3
		$totalUpgrades = 0; // count_4

		if ($results) {
			foreach ($results as $record) {
				$date = strtotime($record->statistics_date);
				$planId = $record->plan_id;

				$data['plans'][$planId] = $record->title;
				$data['upgrade'][$planId] = intval($record->count_4);

				// Plan Specific
				$data[$date]['sales'][$planId] = intval($record->count_1);
				$data[$date]['revenue'][$planId] = floatval($record->count_2);
				$data[$date]['renewals'][$planId] = intval($record->count_3);

				if (isset($data[$date]['sales_day'])) {
					$data[$date]['sales_day'] += intval($record->count_1);
				} else {
					$data[$date]['sales_day'] = intval($record->count_1);
				}

				$totalSales += intval($record->count_1);
				$totalRevenue += floatval($record->count_2);
				$totalRenewals += intval($record->count_3);
				$totalUpgrades += intval($record->count_4);
			}
		}

		$data['sales_all'] = $totalSales;
		$data['revenue_all'] = $totalRevenue;
		$data['renewal_all'] = $totalRenewals;
		$data['upgrades_all'] = $totalUpgrades;

		ksort($data);
		return json_encode($data);
	}

	/**
	 * Method to recalculate statistics
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function calculateStatistics()
	{
		$adapters = array('plan', 'subscription');

		foreach ($adapters as $adapter) {
			$adapterLib = $this->getAdapter($adapter);
			$datesToProcess = $adapterLib->getDates($adapter);

			if (!empty($datesToProcess)) {
				$adapterLib->setDetails(array(), $datesToProcess);
			}
		}

		return true;
	}

	/**
	 * Determine the limit for the rebuild process
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getRebuildLimit()
	{
		// @TODO: add settings for this
		// $limit = JRequest::getVar('limit', 10);
		$limit = 10;
		return $limit;
	}

	/**
	 * Return the total number of days to process in the statistics
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getDaysToProcess()
	{
		$subscriptionLib = $this->getAdapter('subscription');
		$first_date = PP::date($subscriptionLib->getOldestDate());
		$today_date = PP::date('now');

		//Calculation for number of days
		$days = abs((($today_date->toUnix()) - ($first_date->toUnix())) / 86400); // 86400 seconds in one day
		return intval($days);
	}

	/**
	 * Method to truncate all the statistics data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function truncateStatistics()
	{
		$model = PP::model('Statistics');
		$state = $model->truncateStatistics();

		return $state;
	}

	/**
	 * Retrieve a set of colors for chart label
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getChartLabelColor($key = 0)
	{
		$color = array(
					'#4246A1',
					'#f0b236',
					'#439dca',
					'#d85084',
					'#1db6ad',
					'#5AAC4F',
					'#f4511e',
					'#445079',
					'#f0a598',
					'#b5946e',
					'#d6494d',
					'#1A5781',
					'#d597d0',
					'#6e5847',
					'#8143ee',
					'#f19250',
					'#9bc1d2',
					'#4caf50',
					'#5b82db',
					'#ad4247',
					'#36A2EB',
					'#FF6384',
					'#EF5350',
					'#EC407A',
					'#7E57C2',
					'#5C6BC0',
					'#26C6DA',
					'#EF5350',
					'#FF9800',
					'#FFD54F',
					'#43A047',
					'#2196F3',
					'#78909C',
					'#7E57C2'
				);

		return $color[$key];
	}
}
