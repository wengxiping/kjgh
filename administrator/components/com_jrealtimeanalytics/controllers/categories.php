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
class JRealtimeControllerCategories extends JRealtimeController {
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
	
		parent::setModelState($scope);
		
		$filter_order = $this->getUserStateFromRequest ( "$option.$scope.filter_order", 'filter_order', 's.lft', 'cmd' );
		$filter_state = $this->getUserStateFromRequest ( "$option.$scope.filterstate", 'filter_state', '*' );
	
		// Set model state
		$defaultModel->setState ( 'order', $filter_order );
		$defaultModel->setState('state', $filter_state); 
	
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
		$defaultModel = $this->setModelState('categories');
		 
		// Parent construction and view display
		parent::display($cachable);
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
	}
}