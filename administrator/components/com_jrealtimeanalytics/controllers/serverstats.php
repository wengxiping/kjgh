<?php
// namespace administrator\components\com_jrealtimeanalytics\controllers;
/** 
 * @package JREALTIMEANALYTICS::SERVERSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Concrete class serverstats controller
 *
 * @package JREALTIMEANALYTICS::SERVERSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @since 1.0
 */
class JRealtimeControllerServerstats extends JRealtimeController { 
	/**
	 * Setta il model state a partire dallo userstate di sessione
	 * @access protected
	 * @return void
	 */
	protected function setModelState($scope = 'default') { 
		$option = $this->option;
		
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
		$statsReport = $this->getUserStateFromRequest( "$option.$scope.statsreport", 'statsreport', 'full');
		
		// Get default model
		$defaultModel = parent::setModelState('serverstats');
		
		// Set model state  
		$defaultModel->setState('fromPeriod', $fromPeriod);
		$defaultModel->setState('toPeriod', $toPeriod); 
		$defaultModel->setState('graphTheme', $graphTheme);
		$defaultModel->setState('statsReport', $statsReport);
		$defaultModel->setState('task', $this->task);
		
		return $defaultModel;
	}
	
	/**
	 * Default show stats
	 * 
	 * @access public
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false) {
		// Set model state 
		$model = $this->setModelState('serverstats');
		 
		// Graph Generators interface as Setter Dependency Injection
		$graphGenerator = new JRealtimeGraphGeneratorsCharts($model->getState('graphTheme'));
		$model->setGraphRenderer($graphGenerator);
		 
		// Get view always HTML format
		$view =  $this->getView ();
		// Push the model into the view (as default)
		$view->setModel ( $model, true );
		
		// Call main template
		$prefixPath = null;
		if($this->task === 'displaypdf') {
			$prefixPath = 'pdf_';
		}
		if($this->task === 'displaycsv') {
			$prefixPath = 'csv_';
		}
		if($this->task == 'displayxls') {
			$prefixPath = 'xls_';
		}
		$view->setLayout($prefixPath . 'graph');
		
		//Creazione buffer output
		ob_start (); 
		// Parent construction and view display
		$view->display('main');
		$bufferContent = ob_get_contents ();
		ob_end_clean ();
		 
		// Choose if plain HTML or PDF conversion is required based on tasks instead of document format
		switch ($this->task) {
			case 'displaypdf':
				// Do conversion to PDF format using adapter
				$pdfRenderer = new JRealtimeRenderersAdapterTcpdf();
				$pdfRenderer->renderContent ( $bufferContent, $model, 'global_stats_report_' );
				break;
			case 'displaycsv':
				$csvRenderer = new JRealtimeRenderersAdapterCsv();
				$csvRenderer->renderContent( $bufferContent, $model);
				break;
			case 'displayxls':
				$xlsRenderer = new JRealtimeRenderersAdapterXls();
				$xlsRenderer->renderContent($bufferContent, $model);
				break;
			default:
				echo $bufferContent; 
		} 
	}
	
	/**
	 * Details show entity
	 *
	 * @access public
	 * @return void
	 */
	public function showEntity() {
		// Set model state
		$this->setModelState('serverstats');
		
		$identifier = $this->app->input->get('identifier', null, 'string');
		$detailType = $this->app->input->get('details');
		
		$model = $this->getModel();
		$detailData = $model->loadStatsEntity($identifier, $detailType);
		// Try to load record from model
		if($detailData === false) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelExceptions = $model->getErrors();
			foreach ($modelExceptions as $exception) {
				$this->app->enqueueMessage($exception->getMessage(), $exception->getErrorLevel());
			}
			return false;
		}
		
		// Get view always HTML format
		$view = $this->getView();
		
		// Call main template
		$prefixPath = null;
		if($this->task === 'showEntitypdf') {
			$prefixPath = 'pdf_';
		}
		if($this->task === 'showEntitycsv') {
			$prefixPath = 'csv_';
		}
		if($this->task == 'showEntityxls') {
			$prefixPath = 'xls_';
		}
		$view->setLayout($prefixPath . 'details');
		
		//Creazione buffer output
		ob_start ();
		// Parent construction and view display
		$view->showEntity($detailData, $detailType);
		$bufferContent = ob_get_contents ();
		ob_end_clean ();
			
		// Choose if plain HTML or PDF conversion is required based on tasks instead of document format
		switch ($this->task) {
			case 'showEntitypdf':
				// Do conversion to PDF format using adapter
				$pdfRenderer = new JRealtimeRenderersAdapterTcpdf();
				$pdfRenderer->renderContent ( $bufferContent, $model, $detailType . '_stats_report_' );
				break;
			case 'showEntitycsv':
				$csvRenderer = new JRealtimeRenderersAdapterCsv();
				$csvRenderer->renderContent( $bufferContent, $model, $detailType . '_stats_report_' );
				break;
			case 'showEntityxls':
				$xlsRenderer = new JRealtimeRenderersAdapterXls();
				$xlsRenderer->renderContent($bufferContent, $model, $detailType . '_stats_report_' );
				break;
			default:
				echo $bufferContent;
		}
	}
	
	/**
	 * Cancellazione dati statistici nella DB cache
	 *
	 * @access public
	 * @return void
	 */
	public function deleteEntity() {  
		// Access check
		if (! $this->allowDelete ( $this->option )) {
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->corename . ".display", JText::_ ( 'COM_JREALTIME_ERROR_ALERT_NOACCESS' ), 'notice' );
			return false;
		}
		
		// Set model state
		$model = $this->setModelState('serverstats');
		
		if(!$model->deleteEntity(null)) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getError(null, false);
			$this->app->enqueueMessage($modelException->getMessage(), $modelException->getErrorLevel());
			$this->setRedirect('index.php?option=' . $this->option . '&task=' . $this->corename . '.display', JText::_('COM_JREALTIME_ERROR_CLEANED_CACHE'));
			return false;
		}
			
		$this->setRedirect('index.php?option=' . $this->option . '&task=' . $this->corename . '.display', JText::_('COM_JREALTIME_SUCCESS_CLEANED_CACHE') );
	}
	
	/**
	 * Fetch host address IP info
	 *
	 * @access public
	 * @return void
	 */
	public function fetchIpinfo() {
		$ipAddress = $this->app->input->getString('ipaddress');
		$ipInformations = gethostbyaddr($ipAddress);
	
		echo $ipInformations;
		jexit();
	}
	
	/**
	 * Overloaded class constructor
	 * 
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array()) {
		parent::__construct($config);
		
		// Routes controller tasks
		$this->registerTask('displaypdf', 'display');
		$this->registerTask('displaycsv', 'display');
		$this->registerTask('displayxls', 'display');
		$this->registerTask('showEntitypdf', 'showEntity');
		$this->registerTask('showEntitycsv', 'showEntity');
		$this->registerTask('showEntityxls', 'showEntity');
		$this->registerTask('deletePeriodEntity', 'deleteEntity');
	}
}