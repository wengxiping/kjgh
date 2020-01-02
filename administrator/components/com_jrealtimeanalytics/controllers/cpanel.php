<?php
// namespace administrator\components\com_jrealtimeanalytics\controllers;
/**
 *
 * @package JREALTIMEANALYTICS::CPANEL::administrator::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * CPanel controller
 *
 * @package JREALTIMEANALYTICS::CPANEL::administrator::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @since 1.0
 */
class JRealtimeControllerCpanel extends JRealtimeController {
	/**
	 * Setta il model state a partire dallo userstate di sessione
	 * @access protected
	 * @return void
	 */
	protected function setModelState($scope = 'default') {
		$option = $this->option;
	
		// Filter by current month - week - day
		$cParams = JComponentHelper::getParams($option);
		if($cParams->get('cpanelstats_period_interval', 'week') == 'day') {
			$startPeriod = date ( "Y-m-d" );
			$endPeriod = date ( "Y-m-d" );
		} elseif($cParams->get('cpanelstats_period_interval', 'week') == 'week') {
			$dt = time();
			$startPeriod = date('Y-m-d', strtotime('-1 week', $dt));
			$endPeriod = date('Y-m-d', $dt);
		} elseif ($cParams->get('cpanelstats_period_interval', 'week') == 'month') {
			$dt = time();
			$startPeriod = date('Y-m-d', strtotime('-1 month', $dt));
			$endPeriod = date('Y-m-d', $dt);
		}
	
		// Set model state
		// Get default model
		$defaultModel = $this->getModel ();
		$defaultModel->setState('fromPeriod', $startPeriod);
		$defaultModel->setState('toPeriod', $endPeriod);
		$defaultModel->setState('task', $this->task);
		$defaultModel->setState('cparams', $cParams);
	
		return $defaultModel;
	}
	
	/**
	 * Show Control Panel
	 * 
	 * @access public
	 * @return void
	 */
	function display($cachable = false, $urlparams = false) {
		// Set model state
		$this->setModelState('cpanel');
		
		$view = $this->getView();
		
		// Dependency injection setter on view/model
		$HTTPClient = new JRealtimeHttp();
		$view->set('httpclient', $HTTPClient);
		
		// No operations
		parent::display ($cachable, $urlparams); 
	}
}