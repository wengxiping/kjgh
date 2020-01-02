<?php
// namespace administrator\components\com_jrealtimeanalytics\controllers;
/**
 * @package JREALTIMEANALYTICS::HEATMAP::administrator::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Main controller
 * @package JREALTIMEANALYTICS::HEATMAP::administrator::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @since 2.4
 */
class JRealtimeControllerHeatmap extends JRealtimeController {
	/**
	 * Set model state from session userstate
	 * @access protected
	 * @param string $scope
	 * @return void
	 */
	protected function setModelState($scope = 'default') {
		$option = $this->option;
	
		// Get default model
		$defaultModel = $this->getModel();
		
		// Filtro Data DA... Data A... - Valori di default
		// Filter by current month - week - day
		$cParams = JComponentHelper::getParams($option);
		if($cParams->get('default_period_interval', 'week') == 'day') {
			$startPeriod = date ( "Y-m-d" );
			$endPeriod = date ( "Y-m-d" );
		} elseif($cParams->get('default_period_interval', 'week') == 'week') {
			$dt = time();
			$startPeriod = date('N', $dt)==1 ? date('Y-m-d', $dt) : date('Y-m-d', strtotime('last monday', $dt));
			$endPeriod = date('N', $dt)==7 ? date('Y-m-d', $dt) : date('Y-m-d', strtotime('next sunday', $dt));
		} elseif ($cParams->get('default_period_interval', 'week') == 'month') {
			$startPeriod = date ( "Y-m-01", strtotime ( date ( "Y-m-d" ) ) );
			$endPeriod = date ( "Y-m-d", strtotime ( "-1 day", strtotime ( "+1 month", strtotime ( date ( "Y-m-01" ) ) ) ) );
		}
		
		$fromPeriod = $this->getUserStateFromRequest( "$option.$scope.fromperiod", 'fromperiod', strval($startPeriod));
		$toPeriod = $this->getUserStateFromRequest( "$option.$scope.toperiod", 'toperiod', strval($endPeriod));
		$graphTheme = $this->getUserStateFromRequest( "$option.$scope.graphtheme", 'graphtheme', 'Universal');
		$filter_state = $this->getUserStateFromRequest ( "$option.$scope.filterstate", 'filter_state', null );
		$filter_catid = $this->getUserStateFromRequest ( "$option.$scope.filtercatid", 'filter_catid', null );
		parent::setModelState($scope);
	
		$filter_order = $this->getUserStateFromRequest( "$option.$scope.filter_order", 'filter_order', 'numclicks', 'cmd' );
		$filter_order_Dir = $this->getUserStateFromRequest("$option.$scope.filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		
		// Set model state
		$defaultModel->setState('order', $filter_order);
		$defaultModel->setState('order_dir', $filter_order_Dir);
		
		$defaultModel->setState('fromPeriod', $fromPeriod);
		$defaultModel->setState('toPeriod', $toPeriod);
		$defaultModel->setState('graphTheme', $graphTheme);
	
		return $defaultModel;
	}
	
	/**
	 * Default listEntities
	 * 
	 * @access public
	 * @param $cachable string
	 *       	 the view output will be cached
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false) {
		// Set model state
		$model = $this->setModelState('heatmap');
			
		// Graph Generators interface as Setter Dependency Injection
		$graphGenerator = new JRealtimeGraphGeneratorsCharts($model->getState('graphTheme'));
		$model->setState('graphRenderer', $graphGenerator);
			
		// Get view always HTML format
		$view =  $this->getView ();
		// Push the model into the view (as default)
		$view->setModel ( $model, true );
		
		// Call main template
		$prefixPath = null;
		if($this->task === 'displaypdf') {
			$prefixPath = 'pdf_';
		}
		$view->setLayout($prefixPath . 'default');
		
		//Creazione buffer output
		ob_start ();
		// Parent construction and view display
		$view->display();
		$bufferContent = ob_get_contents ();
		ob_end_clean ();
			
		// Choose if plain HTML or PDF conversion is required based on tasks instead of document format
		switch ($this->task) {
			case 'displaypdf':
				// Do conversion to PDF format using adapter
				$pdfRenderer = new JRealtimeRenderersAdapterTcpdf();
				$pdfRenderer->renderContent ( $bufferContent, $model, 'heatmap_stats_report_' );
				break;
			default:
				echo $bufferContent;
		}
		
	}
}