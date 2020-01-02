<?php
// namespace administrator\components\com_jrealtimeanalytics\controllers;
/**
 * @package JREALTIMEANALYTICS::OVERVIEW::administrator::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Main controller
 * @package JREALTIMEANALYTICS::OVERVIEW::administrator::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @since 2.4
 */
class JRealtimeControllerOverlook extends JRealtimeController {
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
		
		$graphTheme = $this->getUserStateFromRequest( "$option.$scope.graphtheme", 'graphtheme', 'Universal');
		$graphType = $this->getUserStateFromRequest( "$option.$scope.graphtype", 'graphtype', 'Bars');
		$statsYear = $this->getUserStateFromRequest( "$option.$scope.stats_year", 'stats_year', date('Y'));
		$statsMonth = $this->getUserStateFromRequest( "$option.$scope.stats_month", 'stats_month', '');
		
		// Set model state
		$defaultModel->setState ( 'option', $option );
		$defaultModel->setState ( 'graphTheme', $graphTheme );
		$defaultModel->setState ( 'graphType', $graphType );
		$defaultModel->setState ( 'statsYear', $statsYear );
		$defaultModel->setState ( 'statsMonth', $statsMonth );
		$defaultModel->setState ( 'toPeriod', date('Y-m-d') );
	
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
		$model = $this->setModelState('overlook');
			
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
				$pdfRenderer->renderContent ( $bufferContent, $model, 'overview_stats_report_' );
				break;
			default:
				echo $bufferContent;
		}
		
	}
}