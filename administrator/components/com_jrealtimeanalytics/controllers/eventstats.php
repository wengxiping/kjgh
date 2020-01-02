<?php
// namespace administrator\components\com_jrealtimeanalytics\controllers;
/**
 * @package JREALTIMEANALYTICS::EVENTSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Main controller
 * @package JREALTIMEANALYTICS::EVENTSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @since 2.0
 */
class JRealtimeControllerEventstats extends JRealtimeController {
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
	
		$filter_state = $this->getUserStateFromRequest ( "$option.$scope.filterstate", 'filter_state', null );
		$filter_catid = $this->getUserStateFromRequest ( "$option.$scope.filtercatid", 'filter_catid', null );
		parent::setModelState($scope);
	
		// Set model state
		$defaultModel->setState('state', $filter_state); 
		$defaultModel->setState('catid', $filter_catid);
	
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
		$defaultModel = $this->setModelState('eventstats');
		 
		// Parent construction and view display
		parent::display($cachable);
	}
	
	/**
	 * Manage entity apply/save after edit entity
	 *
	 * @access public
	 * @return void
	 */
	public function saveEntity() {
		$parentSave = parent::saveEntity();
		
		if($parentSave && $this->task == 'changeEntity') {
			$this->message = null;
		}
	}
	
	/**
	 * Edit entity
	 *
	 * @access public
	 * @return void
	 */
	public function showEntity() {
		$idEntity = $this->app->input->get ( 'id', null, 'int' );
		$model = $this->getModel ();
		$model->setState ( 'option', $this->option );
	
		// Try to load record from model
		if ( !$record = $model->loadEntity ( $idEntity )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelExceptions = $model->getErrors ();
			foreach ( $modelExceptions as $exception ) {
				$this->app->enqueueMessage ( $exception->getMessage (), $exception->getErrorLevel () );
			}
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->corename . ".display", JText::_('COM_JREALTIME_ERROR_EDITING')  );
			return false;
		}
	
		// Additional model state setting
		$model->setState ( 'option', $this->option );
	
		// Get view and pushing model
		$view = $this->getView ();
		$view->setModel ( $model, true );
	
		if($this->task === 'showEntitypdf') {
			$tpl = 'showpdf';
		}
		if($this->task === 'showEntitycsv') {
			$tpl = 'showcsv';
		}
		if($this->task == 'showEntityxls') {
			$tpl = 'showxls';
		}
		
		//Creazione buffer output
		ob_start ();
		// Call edit view
		$view->showEntity ( $record, $tpl );
		$bufferContent = ob_get_contents ();
		ob_end_clean ();
		
		// Choose if plain HTML or PDF conversion is required based on tasks instead of document format
		switch ($this->task) {
			case 'showEntitypdf':
				// Do conversion to PDF format using adapter
				$pdfRenderer = new JRealtimeRenderersAdapterTcpdf();
				$pdfRenderer->renderContent ( $bufferContent, $model, 'event' . $idEntity . '_report_' );
				break;
			case 'showEntitycsv':
				$csvRenderer = new JRealtimeRenderersAdapterCsv();
				$csvRenderer->renderContent ( $bufferContent, $model, 'event' . $idEntity . '_report_' );
				break;
			case 'showEntityxls':
				$xlsRenderer = new JRealtimeRenderersAdapterXls();
				$xlsRenderer->renderContent ( $bufferContent, $model, 'event' . $idEntity . '_report_' );
				break;
			default:
				echo $bufferContent;
		}
	}
	
	/**
	 * Manage entity apply/save after edit entity
	 *
	 * @access public
	 * @return boolean
	 */
	public function changeEntity() {
		$context = implode ( '.', array (
				$this->option,
				strtolower ( $this->getNames () ),
				'errordataload'
		) );
	
		// Store data for session recover
		$this->app->setUserState ( $context, $this->requestArray[$this->requestName] );
		$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->corename . ".editEntity");

		return true;
	}
	
	/**
	 * Redirects to categories bypassing setModelState
	 *
	 * @access public
	 * @return void
	 */
	public function showCategories() {
		// Execute only redirect
		$this->setRedirect ( "index.php?option=" . $this->option . "&task=categories.display" );
	}
	
	/**
	 * Class Constructor
	 * 
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array()) {
		parent::__construct ( $config );
		// Register Extra tasks
		$this->registerTask ( 'moveorder_up', 'moveOrder' );
		$this->registerTask ( 'moveorder_down', 'moveOrder' );
		$this->registerTask ( 'applyEntity', 'saveEntity' );
		$this->registerTask ( 'saveEntity2New', 'saveEntity' );
		$this->registerTask ( 'unpublish', 'publishEntities' );
		$this->registerTask ( 'publish', 'publishEntities' );
		$this->registerTask ( 'showEntitycsv', 'showEntity' );
		$this->registerTask ( 'showEntityxls', 'showEntity' );
		$this->registerTask ( 'showEntitypdf', 'showEntity' );
	}
}