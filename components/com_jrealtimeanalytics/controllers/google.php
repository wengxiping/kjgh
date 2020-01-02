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
 * @since 2.5
 */
class JRealtimeControllerGoogle extends JRealtimeController {
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
		// Set model state
		$defaultModel->setState ( 'option', $option );
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
		// Composer autoloader
		require_once JPATH_COMPONENT_ADMINISTRATOR. '/framework/composer/autoload_real.php';
		ComposerAutoloaderInitfc5c9af51413a149e4084a610a3ab6djreal::getLoader();
	
		// Mixin, add include path for admin side to avoid DRY on model
		$this->addModelPath ( JPATH_COMPONENT_ADMINISTRATOR . '/models', 'JRealtimeModel', 'JRealtimeModel' );
		
		$this->setModelState('google');
		parent::display($cachable, $urlparams);
	}
	
	/**
	 * Delete a db table entity
	 *
	 * @access public
	 * @return void
	 */
	public function deleteEntity() {
		// Mixin, add include path for admin side to avoid DRY on model
		$this->addModelPath ( JPATH_COMPONENT_ADMINISTRATOR . '/models', 'JRealtimeModel', 'JRealtimeModel' );
		
		// Load della model e checkin before exit
		$model = $this->getModel ();
	
		if (! $model->deleteEntity ( null )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getError ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getErrorLevel () );
			$this->setRedirect ( JRoute::_("index.php?option=" . $this->option . "&view=" . $this->corename), JText::_ ( 'COM_JREALTIME_GOOGLE_ERROR_' . 'LOGOUT' ) );
			return false;
		}
	
		$this->setRedirect ( JRoute::_("index.php?option=" . $this->option . "&view=" . $this->corename), JText::_ ( 'COM_JREALTIME_GOOGLE_SUCCESS_LOGOUT' ) );
	}
}