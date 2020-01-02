<?php
// namespace administrator\components\com_jrealtimeanalytics\controllers;
/**
 *
 * @package JREALTIMEANALYTICS::CONFIG::administrator::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Config controller concrete implementation
 *
 * @package JREALTIMEANALYTICS::CPANEL::administrator::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @since 1.0
 */
class JRealtimeControllerConfig extends JRealtimeController {

	/**
	 * Show configuration
	 * @access public
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false) {
		// Access check.
		if (!$this->allowAdmin($this->option)) {
			$this->setRedirect('index.php?option=' . $this->option . '&task=' . $this->corename . '.display', JTEXT::_('J' . 'ERROR_ALERT_NOACCESS'));
			return false;
		}
		
		parent::display($cachable);
	}
	
	/**
	 * Save config entity
	 * @access public
	 * @return void
	 */
	public function saveEntity() {
		$model = $this->getModel();
		$option = $this->option;
	
		if(!$model->storeEntity()) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getError(null, false);
			$this->app->enqueueMessage($modelException->getMessage(), $modelException->getErrorLevel());
			$this->setRedirect('index.php?option=' . $this->option . '&task=' . $this->corename . '.display', JText::_('COM_JREALTIME_ERROR_SAVING_PARAMS'));
			return false;
		}
		
		$this->setRedirect('index.php?option=' . $this->option . '&task=' . $this->corename . '.display', JText::_('COM_JREALTIME_SAVED_PARAMS'));
	}
}