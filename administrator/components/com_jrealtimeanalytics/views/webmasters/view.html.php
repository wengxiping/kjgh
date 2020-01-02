<?php
// namespace administrator\components\com_jrealtimeanalytics\views\overview;
/**
 * @package JREALTIME::GOOGLE::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage google
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
 
/**
 * @package JREALTIME::GOOGLE::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage google
 * @since 2.6
 */
class JRealtimeViewWebmasters extends JRealtimeView {
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$user = JFactory::getUser();
		JToolBarHelper::title( JText::_( 'COM_JREALTIME_GOOGLE_WEBMASTERS_TOOLS' ), 'jrealtimeanalytics' );
		
		// Store logged in status in session
		if($this->isLoggedIn) {
			JToolBarHelper::custom('webmasters.displayxls', 'download', 'download', 'COM_JREALTIME_EXPORTXLS', false);
			JToolBarHelper::custom('webmasters.deleteEntity', 'lock', 'lock', 'COM_JREALTIME_GOOGLE_LOGOUT', false);
		}
		
		JToolBarHelper::custom('cpanel.display', 'home', 'home', 'COM_JREALTIME_CPANEL', false);
	}
	
	/**
	 * Default display listEntities
	 *        	
	 * @access public
	 * @param string $tpl
	 * @return void
	 */
	public function display($tpl = null) {
		// Get main records
		$lists = $this->get ( 'Lists' );
		
		// Check the Google stats type and retrieve stats data accordingly, supported types are 'analytics' and 'webmasters'
		$googleData = $this->get ( 'DataWebmasters' );
		if(!$this->getModel()->getState('loggedout')) {
			$tpl = 'webmasters';
		}
		
		$this->loadJQuery($this->document);
		$this->loadJQueryUI($this->document); // Required for calendar feature
		$this->loadBootstrap($this->document);
		$this->document->addScript(JUri::root(true) . '/administrator/components/com_jrealtimeanalytics/js/libraries/tablesorter/jquery.tablesorter.js');
		$this->document->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/google.js' );
		
		// Inject js translations
		$translations = array();
		$this->injectJsTranslations($translations, $this->document);
		
		$dates = array('start'=>$this->getModel()->getState('fromPeriod'), 'to'=>$this->getModel()->getState('toPeriod'));
		$this->dates = $dates;
		$this->globalConfig = JFactory::getConfig();
		$this->timeZoneObject = new DateTimeZone($this->globalConfig->get('offset'));
		$this->document->addScriptDeclaration("var jrealtime_baseURI='" . JUri::root() . "';");
		$this->lists = $lists;
		$this->googleData = $googleData;
		$this->isLoggedIn = $this->getModel()->getToken();
		$this->statsDomain = $this->getModel()->getState('stats_domain', JUri::root());
		$this->errorsDomain = preg_match('/^http/i', $this->statsDomain) ? $this->statsDomain . '/' : JUri::getInstance()->getScheme() . '://' . $this->statsDomain . '/';
		$this->hasOwnCredentials = $this->getModel()->getState('has_own_credentials', false);
		$this->option = $this->getModel ()->getState ( 'option' );
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
		
		parent::display ($tpl);
	}
}