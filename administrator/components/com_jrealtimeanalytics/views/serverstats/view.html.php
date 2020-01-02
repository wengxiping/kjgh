<?php
// namespace administrator\components\com_jrealtimeanalytics\views\messages;
/** 
 * @package JREALTIMEANALYTICS::SERVERSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage serverstats
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );   
define ( 'VISITSPERPAGE', 0 );
define ( 'TOTALVISITEDPAGES', 1 );
define ( 'TOTALVISITEDPAGESPERUSER', 2 );
define ( 'TOTALVISITORS', 3 );
define ( 'MEDIUMVISITTIME', 4 );
define ( 'MEDIUMVISITEDPAGESPERSINGLEUSER', 5 );
define ( 'NUMUSERSGEOGROUPED', 6 );
define ( 'NUMUSERSBROWSERGROUPED', 7 );
define ( 'NUMUSERSOSGROUPED', 8 );
define ( 'LEAVEOFF_PAGES', 9 );
define ( 'LANDING_PAGES', 10 );
define ( 'REFERRALTRAFFIC', 11 );
define ( 'SEARCHEDPHRASE', 12 );
define ( 'TOTALVISITEDPAGESPERIPADDRESS', 13 );
define ( 'BOUNCERATE', 14 );
define ( 'TOTALUNIQUEVISITORS', 15 );
define ( 'NUMUSERSDEVICEGROUPED', 16 );

jimport ( 'joomla.application.component.view' ); 

/**
 * Server stats view
 *
 * @package JREALTIMEANALYTICS::SERVERSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage realstats
 * @since 1.0
 */
class JRealtimeViewServerstats extends JRealtimeView { 
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$user = JFactory::getUser();
		
		JToolBarHelper::title( JText::_( 'COM_JREALTIME_SERVERSTATS_PANEL_HEADER' ), 'jrealtimeanalytics' );
		JToolBarHelper::custom('serverstats.displaycsv', 'download', 'download', 'COM_JREALTIME_EXPORTCSV', false);
		JToolBarHelper::custom('serverstats.displayxls', 'download', 'download', 'COM_JREALTIME_EXPORTXLS', false);
		JToolBarHelper::custom('serverstats.displaypdf', 'download', 'download', 'COM_JREALTIME_EXPORTPDF', false);
		
		if ($user->authorise('core.delete', 'com_jrealtimeanalytics') && $user->authorise('core.edit', 'com_jrealtimeanalytics')) {
			JToolBarHelper::deleteList(JText::_('COM_JREALTIME_DELETE_SERVERSTATS_PERIOD_CACHE'), 'serverstats.deletePeriodEntity', 'COM_JREALTIME_CLEANPERIOD_CACHE');
			JToolBarHelper::deleteList(JText::_('COM_JREALTIME_DELETE_SERVERSTATS_ALL_CACHE'), 'serverstats.deleteEntity', 'COM_JREALTIME_CLEANALL_CACHE');
		}
		
		JToolBarHelper::custom('cpanel.display', 'home', 'home', 'COM_JREALTIME_CPANEL', false);
	}
	
	/**
	 * Default display delle stats
	 * 
	 * @access public
	 * @return void
	 */
	public function display($tpl = 'main') {
		$doc = JFactory::getDocument();
		$this->loadJQuery($doc);
		$this->loadJQueryUI($doc); // Required for calendar feature
		$this->loadBootstrap($doc);
		$this->loadJQVMap($doc);
		$this->loadJQFancybox($doc);
		$this->cparams = $this->getModel()->getComponentParams();
		
		$doc->addScript(JUri::root(true) . '/administrator/components/com_jrealtimeanalytics/js/libraries/tablesorter/jquery.tablesorter.js');
		$doc->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/serverstats.js' );
		$doc->addScriptDeclaration("
						var jrealtimeIpAddressServerStatsEndpoint = '" . JUri::root() . 'administrator/index.php?option=com_jrealtimeanalytics&task=serverstats.fetchIpinfo&format=raw' . "';
						var jrealtimeBackendHostInfo = " . $this->cparams->get('backend_host_info', 0) . ";
						Joomla.submitbutton = function(pressbutton) {
							Joomla.submitform( pressbutton );
							if (pressbutton == 'serverstats.displaypdf' ||
								pressbutton == 'serverstats.displayxls' ||
								pressbutton == 'serverstats.displaycsv') {
								jQuery('#adminForm input[name=task]').val('serverstats.display');
							}
							return true;
						}
					");
		
		// Get stats data
		$statsData = $this->get('Data');
		$lists = $this->get('Lists');
		$geoTranslations = $this->get('GeoTranslations');
		// Inject js translations
		$translations = array('COM_JREALTIME_STATS_DETAILS',
							  'COM_JREALTIME_VISUALMAP',
							  'COM_JREALTIME_NUMRESULTS'
		);
		$this->injectJsTranslations($translations, $doc);
		$doc->addScriptDeclaration ( 'var jrealtimeGeoMapData = ' . json_encode ( $statsData[NUMUSERSGEOGROUPED]['clientside'] ) . ';' );
		$doc->addScriptDeclaration ( 'var jrealtimeGeolocationService = "' . $this->getModel()->getComponentParams()->get('backend_geolocation_service', 'geoiplookup') . '";' );
		
		// Enqueue user message se nel periodo selezionato non ci sono pagine visitate AKA statistiche da mostrare
		if(!$statsData[TOTALVISITEDPAGES]) {
			$this->app->enqueueMessage(JText::_('COM_JREALTIME_NO_STATS_IN_PERIOD'));
		}
		
		// Set reference in template
		$dates = array('start'=>$this->getModel()->getState('fromPeriod'), 'to'=>$this->getModel()->getState('toPeriod')); 
		$this->data = $statsData;
		$this->dates = $dates;
		$this->geotrans = $geoTranslations;
		$this->userid = $this->user->id;
		$this->nocache = '?time=' . time();
		$this->lists = $lists;
		$this->livesite = JUri::root();
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
		
		// Some exceptions have been triggered
		if(empty($statsData)) {
			return false;
		}
		
		// Set timezone if required
		if($this->cparams->get('offset_type', 'joomla') == 'joomla') {
			$jConfig = JFactory::getConfig();
			date_default_timezone_set($jConfig->get('offset', 'UTC'));
		}
		
		parent::display($tpl);
	}
	
	/**
	 * Show entity details richiesto per visite utente e pagine
	 * 
	 * @access public
	 * @param Object& $detailData
	 * @param string $tpl detailType
	 * @return void 
	 */
	public function showEntity(&$detailData, $tpl) { 
		$doc = JFactory::getDocument();
		$this->loadJQuery($doc);
		$this->loadJQueryUI($doc); // Required for draggable feature
		$this->loadBootstrap($doc);
		$doc->addStyleDeclaration('body.contentpane.component{height:95%;padding-top:10px;}');
		$doc->addScript(JUri::root(true) . '/administrator/components/com_jrealtimeanalytics/js/libraries/tablesorter/jquery.tablesorter.js');
		$doc->addScriptDeclaration("
						jQuery(function($) {
							$('table.table-striped').tablesorter({
								cssHeader : ''
							});
						});
					");
		
		// Add scripting for flow tpl feature
		if($tpl == 'flow') {
			$doc->addScript(JUri::root(true) . '/administrator/components/com_jrealtimeanalytics/js/libraries/gojs/go.js');
			$doc->addScript(JUri::root(true) . '/administrator/components/com_jrealtimeanalytics/js/flow.js');
		}
		
		$this->detailData = $detailData;
		$this->cparams = JComponentHelper::getParams('com_jrealtimeanalytics');
		$this->daemonRefresh = $this->cparams->get('daemonrefresh', 2);
		$this->livesite = JUri::root();
		
		parent::display($tpl);
	}
}
?>