<?php
// namespace administrator\components\com_jrealtimeanalytics\controllers;
/**
 *
 * @package JREALTIMEANALYTICS::REALSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Statistiche realtime admin controller, responsabile della default display che
 * va ad iniettare la JS APP e di fornire in polling i dati in json format
 * alle richieste asincrone da parte della JS APP che renderizzerà i grafici e i dati
 *
 * @package JREALTIMEANALYTICS::REALSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage controllers
 * @since 1.0
 */
class JRealtimeControllerRealstats extends JRealtimeController {
	/**
	 * Default listEntities
	 * 
	 * @access public
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false) {
		// Parent construction and view display
		parent::display();
	}

	/**
	 * Restituisce in view dispatch i dati per la JS APP in formato JSON
	 * per l'elaborazione e la generazione dei dati statistici
	 * 
	 * @access public
	 * @return void
	 */
	public function showEntity() {
		$initRequest = $this->app->input->get('init', false);
		$pieRequest = $this->app->input->get('pie', false);
		
		$defaultModel = $this->getModel();
		// Calculate data from model
		$data = $defaultModel->getData($pieRequest, $initRequest);
		
		// Respond in JSON to JS APP
		$view = $this->getView(); 
		$view->setModel($defaultModel);
		
		$view->showEntity($data);
	}
}
?>