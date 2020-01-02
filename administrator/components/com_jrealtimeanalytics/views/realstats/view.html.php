<?php
// namespace administrator\components\com_jrealtimeanalytics\views\messages;
/** 
 * @package JREALTIMEANALYTICS::REALSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage realstats
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );   
jimport ( 'joomla.application.component.view' ); 

/**
 * Realtime stats view
 *
 * @package JREALTIMEANALYTICS::REALSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage realstats
 * @since 1.0
 */
class JRealtimeViewRealstats extends JRealtimeView { 
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		JToolBarHelper::title( JText::_( 'COM_JREALTIME_REALSTATS_GRAPH_HEADER' ), 'jrealtimeanalytics' );
		JToolBarHelper::custom('cpanel.display', 'home', 'home', 'COM_JREALTIME_CPANEL', false);
	}

	/**
	 * Default inject JS APP
	 * @access public
	 * @return void
	 */
	public function display($tpl = null) {
		$componentConfig = $this->get('Config');
		
		$doc = JFactory::getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$this->loadJQVMap($doc);
		$this->loadJQFancybox($doc);
		$doc->addScriptDeclaration(" 
					var jrealtimeServerStatsEndpoint = '" . JUri::root() . 'administrator/index.php?option=com_jrealtimeanalytics&task=realstats.showEntity&format=raw' . "';  
					var jrealtimeIntervalRealStats = '" . $componentConfig->get('realtimerefresh') . "';
					jQuery(function(){
						new JRealtimeControllerRealtimestats(); 
					});
				");
		// Inject js translations
		$translations = array(	'COM_JREALTIME_PIEGRAPHTITLE', 
								'COM_JREALTIME_TEXTSTATSTITLE', 
								'COM_JREALTIME_BARGRAPHTITLE', 
								'COM_JREALTIME_USERS',
								'COM_JREALTIME_USERSSTATSTITLE',
								'COM_JREALTIME_TITLENAME',
								'COM_JREALTIME_TITLEUSERNAME',
								'COM_JREALTIME_TITLETYPE',
								'COM_JREALTIME_TITLETIME',
								'COM_JREALTIME_TITLENOWPAGE',
								'COM_JREALTIME_PERPAGESTATSTITLE',
								'COM_JREALTIME_TITLENUMUSERS',
								'COM_JREALTIME_TITLELASTVISIT',
								'COM_JREALTIME_DBERROR',
								'COM_JREALTIME_NA');
		$this->injectJsTranslations($translations, $doc);
		
		// Custom CSS lib files
		
		// Kendo lib
		$doc->addScript(JUri::root(true) . '/administrator/components/com_jrealtimeanalytics/js/libraries/kendo/kendo.core.js');
		$doc->addScript(JUri::root(true) . '/administrator/components/com_jrealtimeanalytics/js/libraries/kendo/kendo.data.js');
		$doc->addScript(JUri::root(true) . '/administrator/components/com_jrealtimeanalytics/js/libraries/kendo/kendo.chart.js');
		$doc->addScript(JUri::root(true) . '/administrator/components/com_jrealtimeanalytics/js/libraries/kendo/jquery.stringify.js');
		
		// Core MVC JS
		$doc->addScript(JUri::root(true) . '/administrator/components/com_jrealtimeanalytics/js/views/realtimestats.view.js');
		$doc->addScript(JUri::root(true) . '/administrator/components/com_jrealtimeanalytics/js/models/realtimestats.model.js');
		$doc->addScript(JUri::root(true) . '/administrator/components/com_jrealtimeanalytics/js/controllers/realtimestats.controller.js');
		 
		// Aggiunta toolbar
		$this->addDisplayToolbar();
		
		// Display template
		parent::display();
	}

	/**
	 * Inject dati to JS app response in JSON mime-type
	 * @access public
	 * @param Object[]& $data
	 * @return void
	 */
	public function showEntity(&$data) {
		$doc = JFactory::getDocument();
		$doc->setMimeEncoding('application/json');
		echo json_encode($data);
		
		exit();
	}
}
?>