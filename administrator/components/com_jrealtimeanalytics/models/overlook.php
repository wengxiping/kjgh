<?php
// namespace administrator\components\com_jrealtimeanalytics\models;
/**
 *
 * @package JREALTIMEANALYTICS::OVERVIEW::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Sources model concrete implementation <<testable_behavior>>
 *
 * @package JREALTIMEANALYTICS::OVERVIEW::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 2.4
 */
class JRealtimeModelOverlook extends JRealtimeModel {
	/**
	 * Build list entities query
	 *
	 * @access protected
	 * @param string $previousDate
	 * @param string $currentDate
	 * @param boolean $byMonth Grouping type for additional stat
	 * @return string
	 */
	protected function buildListQuery($previousDate, $currentDate, $byMonth = false) {
		// is this a query request by month?
		$numVisits = null;
		if(!$byMonth) {
			$numVisits = ",\n COUNT(DISTINCT(" . $this->_db->quoteName('session_id_person') . ")) AS numvisits" ;
		}
		
		// Fetch data from DB if not injected and filter by MySql period with SUM
		$query = "SELECT COUNT(*) AS numpages" .
				 $numVisits .
				 "\n FROM #__realtimeanalytics_serverstats" .
				 "\n WHERE " . $this->_db->quoteName('visit_timestamp') .
				 "\n BETWEEN " . $this->_db->quote($previousDate) .
				 "\n AND "  . $this->_db->quote($currentDate);
		return $query;
	}
	
	/**
	 * Process rawdata -> processeddata
	 *
	 * @access public
	 * @return array
	 */
	public function getData() {
		// Initialization
		$processedData = array();
		
		// Set interval for DatePeriod
		$interval = 'R11/' . $this->getState ('statsYear') . '-02-01T00:00:00Z/P1M';
		$periods = new DatePeriod($interval);
	
		// Set start date
		$currentInitialDate = new DateTime($this->getState ('statsYear') . '-01-01');
		$currentDate = strtotime($currentInitialDate->format('Y-m-d'));
	
		foreach ($periods as $monthIndex=>$period) {
			// Store for post processing
			$previousDate = $currentDate;
			$currentDate = strtotime($period->format('Y-m-d'));

			// Fetch data from DB if not injected and filter by MySql period with SUM
			$query = $this->buildListQuery($previousDate, $currentDate, true);
			try {
				// Step 1: load stats for the number of pages viewed
				$this->_db->setQuery($query);
				$chunk = $this->_db->loadRow();
				// Initialize unique visitors count
				$chunk[1] = 0;
				if($this->_db->getErrorNum()) {
					throw new JRealtimeException($this->_db->getErrorMsg(), 'error');
				}

				// Step 2: retrieve and sum up the number of unique visitors using the classic 'daily metric'
				$tempMonth = $monthIndex + 1;
				$finalStringMonth = $tempMonth < 10 ? '0' . $tempMonth : $tempMonth;
				// Call the same function with an explicit month
				$processedDataNumVisitsByDay = $this->getDataByMonth($finalStringMonth);
				foreach ($processedDataNumVisitsByDay as $numVisitsByDay) {
					$chunk[1] += $numVisitsByDay[1];
				}
				
				// Assign to processed data array
				$orderMonthIndex = strftime('%B', $previousDate);
				$processedData[$orderMonthIndex] = $chunk;
			} catch ( JRealtimeException $e ) {
				$this->app->enqueueMessage ( $e->getMessage (), $e->getErrorLevel () );
				$result = array ();
				break;
			} catch ( Exception $e ) {
				$jRealtimeException = new JRealtimeException ( $e->getMessage (), 'error' );
				$this->app->enqueueMessage ( $jRealtimeException->getMessage (), $jRealtimeException->getErrorLevel () );
				$result = array ();
				break;
			}
				
		}
	
		// No errors detected during processing
		return $processedData;
	}
	
	/**
	 * Process rawdata -> processeddata
	 *
	 * @access public
	 * @param int $monthProcessed
	 * @return array
	 */
	public function getDataByMonth($monthProcessed = null) {
		// Initialization
		$processedData = array();
		$processedMonth = $monthProcessed ? $monthProcessed : $this->getState ('statsMonth');

		// Get the numebr of days in a month accordingly
		$repetitions = cal_days_in_month(CAL_GREGORIAN, $processedMonth, $this->getState ('statsYear')) - 1;

		// Set interval for DatePeriod
		$interval = 'R' . $repetitions . '/' . $this->getState ('statsYear') . '-' . $processedMonth . '-02T00:00:00Z/P1D';
		$periods = new DatePeriod($interval);

		// Set start date
		$currentInitialDate = new DateTime($this->getState ('statsYear') . '-' . $processedMonth . '-01T00:00:00');
		$currentDate = strtotime($currentInitialDate->format('Y-m-d'));

		foreach ($periods as $day=>$period) {
			// Store for post processing
			$previousDate = $currentDate;
			$currentDate = strtotime($period->format('Y-m-d'));

			// Fetch data from DB if not injected and filter by MySql period with SUM
			$query = $this->buildListQuery($previousDate, $currentDate);
			try {
				$this->_db->setQuery($query);
				$chunk = $this->_db->loadRow();
				if($this->_db->getErrorNum()) {
					throw new JRealtimeException($this->_db->getErrorMsg(), 'error');
				}
					
				// Assign to processed data array
				$processedData[$day+1] = $chunk;
			} catch ( JRealtimeException $e ) {
				$this->app->enqueueMessage ( $e->getMessage (), $e->getErrorLevel () );
				$result = array ();
				break;
			} catch ( Exception $e ) {
				$jRealtimeException = new JRealtimeException ( $e->getMessage (), 'error' );
				$this->app->enqueueMessage ( $jRealtimeException->getMessage (), $jRealtimeException->getErrorLevel () );
				$result = array ();
				break;
			}

		}

		// No errors detected during processing
		return $processedData;
	}
	
	/**
	 * Return select lists used as filter for editEntity
	 *
	 * @access public
	 * @param Object $record
	 * @return array
	 */
	public function getLists($record = null) {
		$lists = array();
	
		// Supported graph theme
		$themes = array();
		$themes[] = JHtml::_('select.option', 'Universal', 'Universal');
		$themes[] = JHtml::_('select.option', 'Aqua', 'Aqua');
		$themes[] = JHtml::_('select.option', 'Orange', 'Orange');
		$themes[] = JHtml::_('select.option', 'Pastel', 'Pastel');
		$themes[] = JHtml::_('select.option', 'Rose', 'Rose');
		$themes[] = JHtml::_('select.option', 'Softy', 'Softy');
		$themes[] = JHtml::_('select.option', 'Vivid', 'Vivid');
	
		$lists ['graphTheme'] = JHtml::_ ( 'select.genericlist', $themes, 'graphtheme', 'onchange="Joomla.submitform();"', 'value', 'text', $this->getState('graphTheme', 'Universal'));
	
		// Supported graph theme
		$types = array();
		$types[] = JHtml::_('select.option', 'Bars', JText::_('COM_JREALTIME_BARS_GRAPH'));
		$types[] = JHtml::_('select.option', 'Lines', JText::_('COM_JREALTIME_LINES_GRAPH'));
		$lists ['graphType'] = JHtml::_ ( 'select.genericlist', $types, 'graphtype', 'onchange="Joomla.submitform();"', 'value', 'text', $this->getState('graphType', 'Bars'));
		
		// Year select list
		$lists ['statsYear'] = JHtml::_('select.integerlist', 2012, 2038, 1, 'stats_year', 'onchange="Joomla.submitform();"', $this->getState('statsYear', date('Y')));
		
		// Month select list
		// Supported graph theme
		$months = array();
		$months[] = JHtml::_('select.option', '', JText::_('COM_JREALTIME_ALLMONTHS'));
		$months[] = JHtml::_('select.option', '01', date('F', mktime(0, 0, 0, 1)));
		$months[] = JHtml::_('select.option', '02', date('F', mktime(0, 0, 0, 2)));
		$months[] = JHtml::_('select.option', '03', date('F', mktime(0, 0, 0, 3)));
		$months[] = JHtml::_('select.option', '04', date('F', mktime(0, 0, 0, 4)));
		$months[] = JHtml::_('select.option', '05', date('F', mktime(0, 0, 0, 5)));
		$months[] = JHtml::_('select.option', '06', date('F', mktime(0, 0, 0, 6)));
		$months[] = JHtml::_('select.option', '07', date('F', mktime(0, 0, 0, 7)));
		$months[] = JHtml::_('select.option', '08', date('F', mktime(0, 0, 0, 8)));
		$months[] = JHtml::_('select.option', '09', date('F', mktime(0, 0, 0, 9)));
		$months[] = JHtml::_('select.option', '10', date('F', mktime(0, 0, 0, 10)));
		$months[] = JHtml::_('select.option', '11', date('F', mktime(0, 0, 0, 11)));
		$months[] = JHtml::_('select.option', '12', date('F', mktime(0, 0, 0, 12)));
		$lists ['statsMonth'] = JHtml::_('select.genericlist', $months, 'stats_month', 'onchange="Joomla.submitform();"', 'value', 'text', $this->getState('statsMonth', ''));
		
		return $lists;
	}
	
	/**
	 * Return select lists used as filter for listEntities
	 *
	 * @access public
	 * @return array
	 */
	public function getFilters() {
		$filters = array();
		
		return $filters;
	}
}