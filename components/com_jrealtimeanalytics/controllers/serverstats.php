<?php
// namespace components\com_jrealtimeanalytics\controllers;
/**
 *
 * @package JREALTIMEANALYTICS::SERVERSTATS::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Main controller class
 *
 * @package JREALTIMEANALYTICS::SERVERSTATS::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @since 2.1
 */
class JRealtimeControllerServerstats extends JRealtimeController {
	/**
	 * Setta il model state a partire dallo userstate di sessione
	 *
	 * @access protected
	 * @param string $scope        	
	 * @param Object $model        	
	 * @param Object $cParams        	
	 * @return void
	 */
	protected function setModelState($scope = 'default', $model = null, $cParams = null) {
		$option = $this->option;
		
		// Filtro Data DA... Data A... - Valori di default
		// Filter by current month - week - day
		if ($cParams->get ( 'default_period_interval', 'week' ) == 'day') {
			$startPeriod = date ( "Y-m-d" );
			$endPeriod = date ( "Y-m-d" );
		} elseif ($cParams->get ( 'default_period_interval', 'week' ) == 'week') {
			$dt = time ();
			$startPeriod = date ( 'N', $dt ) == 1 ? date ( 'Y-m-d', $dt ) : date ( 'Y-m-d', strtotime ( 'last monday', $dt ) );
			$endPeriod = date ( 'N', $dt ) == 7 ? date ( 'Y-m-d', $dt ) : date ( 'Y-m-d', strtotime ( 'next sunday', $dt ) );
		} elseif ($cParams->get ( 'default_period_interval', 'week' ) == 'month') {
			$startPeriod = date ( "Y-m-01", strtotime ( date ( "Y-m-d" ) ) );
			$endPeriod = date ( "Y-m-d", strtotime ( "-1 day", strtotime ( "+1 month", strtotime ( date ( "Y-m-01" ) ) ) ) );
		}
		
		$fromPeriod = $this->getUserStateFromRequest ( "$option.$scope.fromperiod", 'fromperiod', strval ( $startPeriod ) );
		$toPeriod = $this->getUserStateFromRequest ( "$option.$scope.toperiod", 'toperiod', strval ( $endPeriod ) );
		$graphTheme = $this->getUserStateFromRequest ( "$option.$scope.graphtheme", 'graphtheme', 'Universal' );
		
		// Set model state
		$model->setState ( 'fromPeriod', $fromPeriod );
		$model->setState ( 'toPeriod', $toPeriod );
		$model->setState ( 'graphTheme', $graphTheme );
		$model->setState ( 'hasExportPermission', $this->hasGroupsPermissions('exporter_groups', $cParams));
	}
	
	/**
	 * Display the Sitemap
	 *
	 * @access public
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false) {
		// Get sitemap model and view core
		$document = JFactory::getDocument ();
		
		$viewType = $document->getType ();
		$coreName = $this->getNames ();
		$viewLayout = $this->app->input->get ( 'layout', 'graph' );
		
		$view = $this->getView ( $coreName, $viewType, '', array (
				'base_path' => $this->basePath 
		) );
		
		// Mixin, add include path for admin side to avoid DRY on model
		$this->addModelPath ( JPATH_COMPONENT_ADMINISTRATOR . '/models', 'JRealtimeModel', 'JRealtimeModel' );
		
		// Get/Create the model
		if ($model = $this->getModel ( $coreName, 'JRealtimeModel' )) {
			// Push the model into the view (as default)
			$view->setModel ( $model, true );
		}
		
		// Set model state
		$this->setModelState ( 'serverstats', $model, $model->getComponentParams () );
		
		// Graph Generators interface as Setter Dependency Injection
		$graphGenerator = new JRealtimeGraphGeneratorsCharts ( $model->getState ( 'graphTheme' ) );
		$model->setGraphRenderer ( $graphGenerator );
		
		if (!in_array($this->task, array('displaypdf','displaycsv','displayxls','emailpdf','emailcsv','emailxls'))) {
			// Set the layout
			$view->setLayout ( $viewLayout );
			$view->display ();
		} else {
			// Permissions check
			if (! $model->getState('hasExportPermission')) {
				$this->setRedirect ( JRoute::_("index.php?option=" . $this->option . "&task=" . $this->corename . ".display"), JText::_ ( 'COM_JREALTIME_ERROR_ALERT_NOACCESS' ), 'notice' );
				return false;
			}
			
			// Call main template
			$prefixPath = null;
			if ($this->task === 'displaypdf' || $this->task === 'emailpdf') {
				$prefixPath = 'pdf_';
			}
			if($this->task === 'displaycsv' || $this->task === 'emailcsv') {
				$prefixPath = 'csv_';
			}
			if($this->task == 'displayxls' || $this->task === 'emailxls') {
				$prefixPath = 'xls_';
			}
			$view->setLayout ( $prefixPath . $viewLayout );
			
			// Creazione buffer output
			ob_start ();
			// Parent construction and view display
			$view->display ( 'main' );
			$bufferContent = ob_get_contents ();
			ob_end_clean ();
			
			// Check if report by email is required
			$mailer = null;
			$cParams = $model->getComponentParams ();
			if($cParams->get('report_byemail', 0) && strpos($this->task, 'email') !== false) {
				// Root controller -> dependency injection
				$mailer = JRealtimeHelpersMailer::getInstance('Joomla');
			}
			
			// Choose if plain HTML or PDF conversion is required based on tasks instead of document format
			switch ($this->task) {
				case 'displaypdf' :
				case 'emailpdf' :
					// Do conversion to PDF format using adapter
					$pdfRenderer = new JRealtimeRenderersAdapterTcpdf ($cParams, $mailer);
					$pdfRenderer->renderContent ( $bufferContent, $model, 'global_stats_report_' );
					break;
				case 'displaycsv':
				case 'emailcsv':
					$csvRenderer = new JRealtimeRenderersAdapterCsv($cParams, $mailer);
					$csvRenderer->renderContent( $bufferContent, $model, 'global_stats_report_' );
					break;
				case 'displayxls':
				case 'emailxls':
					$xlsRenderer = new JRealtimeRenderersAdapterXls($cParams, $mailer);
					$xlsRenderer->renderContent($bufferContent, $model, 'global_stats_report_' );
					break;
				default :
					echo $bufferContent;
			}
		}
	}
	
	/**
	 * Details show entity
	 *
	 * @access public
	 * @return void
	 */
	public function showEntity() {
		// Get sitemap model and view core
		$document = JFactory::getDocument ();
		// Mixin, add include path for admin side to avoid DRY on model
		$this->addModelPath ( JPATH_COMPONENT_ADMINISTRATOR . '/models', 'JRealtimeModel' );
		
		$viewType = $document->getType ();
		$coreName = $this->getNames ();
		$viewLayout = $this->app->input->get ( 'layout', 'graph' );
		
		$view = $this->getView ( $coreName, $viewType, '', array (
				'base_path' => $this->basePath 
		) );
		
		$identifier = $this->app->input->get ( 'identifier', null, 'string' );
		$detailType = $this->app->input->get ( 'details' );
		
		// Get/Create the model
		if ($model = $this->getModel ( $coreName, 'JRealtimeModel' )) {
			// Push the model into the view (as default)
			$view->setModel ( $model, true );
		}
		
		// Set model state
		$this->setModelState ( 'serverstats', $model, $model->getComponentParams () );
		
		$detailData = $model->loadStatsEntity ( $identifier, $detailType );
		// Try to load record from model
		if ($detailData === false) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelExceptions = $model->getErrors ();
			foreach ( $modelExceptions as $exception ) {
				$this->app->enqueueMessage ( $exception->getMessage (), $exception->getErrorLevel () );
			}
			return false;
		}
		
		// Call main template
		$prefixPath = null;
		if ($this->task === 'showEntitypdf') {
			$prefixPath = 'pdf_';
			// Mixin, add include path for admin side to avoid DRY on view templates
			$view->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR . '/views/serverstats/tmpl');
		}
		if($this->task === 'showEntitycsv') {
			$prefixPath = 'csv_';
			// Mixin, add include path for admin side to avoid DRY on view templates
			$view->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR . '/views/serverstats/tmpl');
		}
		if($this->task == 'showEntityxls') {
			$prefixPath = 'xls_';
			// Mixin, add include path for admin side to avoid DRY on view templates
			$view->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR . '/views/serverstats/tmpl');
		}
		$view->setLayout ( $prefixPath . 'details' );
		
		// Creazione buffer output
		ob_start ();
		// Parent construction and view display
		$view->showEntity ( $detailData, $detailType );
		$bufferContent = ob_get_contents ();
		ob_end_clean ();
		
		// Choose if plain HTML or PDF conversion is required based on tasks instead of document format
		switch ($this->task) {
			case 'showEntitypdf' :
				// Do conversion to PDF format using adapter
				$pdfRenderer = new JRealtimeRenderersAdapterTcpdf ();
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
			default :
				echo $bufferContent;
		}
	}
	
	/**
	 * Class Constructor
	 *
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array()) {
		parent::__construct ( $config );

		// Routes controller
		$this->registerTask ( 'view', 'display' );
		$this->registerTask ( 'displaypdf', 'display' );
		$this->registerTask ( 'displaycsv', 'display');
		$this->registerTask ( 'displayxls', 'display');
		$this->registerTask ( 'showEntitypdf', 'showEntity' );
		$this->registerTask ( 'showEntitycsv', 'showEntity');
		$this->registerTask ( 'showEntityxls', 'showEntity');
		
		// Mailer tasks
		$this->registerTask ( 'emailpdf', 'display' );
		$this->registerTask ( 'emailcsv', 'display');
		$this->registerTask ( 'emailxls', 'display');
	}
}