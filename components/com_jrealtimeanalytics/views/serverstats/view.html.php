<?php
// namespace components\com_jrealtimeanalytics\views\serverstats;
/**
 * @package JREALTIMEANALYTICS::SERVERSTATS::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage serverstats
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
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

jimport('joomla.toolbar.toolbar');

/**
 * Main view class
 *
 * @package JREALTIMEANALYTICS::SERVERSTATS::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage serverstats
 * @since 2.1
 */
class JRealtimeViewServerstats extends JRealtimeView {
	/**
	 * Prepares the document
	 */
	protected function _prepareDocument() {
		$app = $this->app;
		$document = JFactory::getDocument();
		$menus = $app->getMenu();
		$title = null;
	
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if(is_null($menu)) {
			return;
		}
	
		$this->params = new JRegistry;
		$this->params->loadString($menu->params);
	
		$title = $this->params->get('page_title', JText::_('COM_JREALTIME_GLOBAL_STATS_REPORT'));
		$document->setTitle($title);
	
		if ($this->params->get('menu-meta_description')) {
			$document->setDescription($this->params->get('menu-meta_description'));
		}
	
		if ($this->params->get('menu-meta_keywords')) {
			$document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}
	
		if ($this->params->get('robots')) {
			$document->setMetadata('robots', $this->params->get('robots'));
		}
	}

	/**
	 * Display the serverstats
	 * @access public
	 * @return void
	 */
	public function display($tpl = null) {
		$this->cparams = $this->getModel ()->getComponentParams ();
		$menu = $this->app->getMenu ();
		$activeMenu = $menu->getActive ();
		if (isset ( $activeMenu )) {
			$this->menuTitle = $activeMenu->title;
		}
		
		// jQuery conditional loading
		if($this->cparams->get('includejquery', 1)) {
			$this->loadJQuery($this->document, false);
		}

		$this->loadJQueryUI($this->document); // Required for calendar feature
		$this->loadBootstrap($this->document, null);
		$this->loadJQVMap($this->document);
		$this->loadJQFancybox($this->document);
		
		$this->document->addScript(JUri::root(true) . '/administrator/components/com_jrealtimeanalytics/js/libraries/tablesorter/jquery.tablesorter.js');
		$this->document->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/serverstats.js' );
		$this->document->addScriptDeclaration("
						var jrealtimeIpAddressServerStatsEndpoint = '" . JUri::root() . 'administrator/index.php?option=com_jrealtimeanalytics&task=serverstats.fetchIpinfo&format=raw' . "';
						var jrealtimeBackendHostInfo = " . $this->cparams->get('backend_host_info', 0) . ";
						jQuery.submitbutton = function(pressbutton) {
							jQuery.submitform( pressbutton );
							if (pressbutton == 'serverstats.displaypdf' ||
								pressbutton == 'serverstats.displayxls' ||
								pressbutton == 'serverstats.displaycsv') {
								setTimeout(function(){
									jQuery('#adminForm input[name=task]').val('serverstats.display');
								}, 200);
							}
							return true;
						}
					");
		
		// Get stats data
		$statsData = $this->get('Data');
		// Some exceptions have been triggered
		if(empty($statsData)) {
			return false;
		}
		
		$lists = $this->get('Lists');
		$geoTranslations = $this->get('GeoTranslations');
		// Inject js translations
		$translations = array('COM_JREALTIME_STATS_DETAILS',
							  'COM_JREALTIME_VISUALMAP',
							  'COM_JREALTIME_NUMRESULTS'
		);
		$this->injectJsTranslations($translations, $this->document);
		$this->document->addScriptDeclaration ( 'var jrealtimeGeoMapData = ' . json_encode ( $statsData[NUMUSERSGEOGROUPED]['clientside'] ) . ';' );
		$this->document->addScriptDeclaration ( 'var jrealtimeGeolocationService = "' . $this->getModel()->getComponentParams()->get('backend_geolocation_service', 'geoiplookup') . '";' );
		
		// Enqueue user message se nel periodo selezionato non ci sono pagine visitate AKA statistiche da mostrare
		if(!$statsData[TOTALVISITEDPAGES]) {
			$this->app->enqueueMessage(JText::_('COM_JREALTIME_NO_STATS_IN_PERIOD'));
		}
		
		// Set reference in template
		$dates = array('start'=>$this->getModel()->getState('fromPeriod'), 'to'=>$this->getModel()->getState('toPeriod'));
		$this->data = $statsData;
		$this->dates = $dates;
		$this->geotrans = $geoTranslations;
		$this->userid = $this->user->id ? $this->user->id : session_id();
		$this->nocache = '?time=' . time();
		$this->lists = $lists;
		$this->canExport = (bool)$this->getModel()->getState('hasExportPermission', true);
		$this->livesite = JUri::root();
		
		// View operations
		$this->_prepareDocument();
		
		// Mixin, add include path for admin side to avoid DRY on view templates
		$this->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR . '/views/serverstats/tmpl');
		
		parent::display ( $tpl );
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
		$this->loadBootstrap($doc, null);
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
		$this->canExport = (bool)$this->getModel()->getState('hasExportPermission', true);
		$this->livesite = JUri::root();
		
		parent::display($tpl);
	}
}