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
	 * Default display listEntities
	 *        	
	 * @access public
	 * @param string $tpl
	 * @return void
	 */
	public function display($tpl = null) {
		$menu = $this->app->getMenu ();
		$activeMenu = $menu->getActive ();
		if (isset ( $activeMenu )) {
			$this->menuTitle = $activeMenu->title;
		}
		
		// Get main records
		$lists = $this->get ( 'Lists' );
		
		// Check the Google stats type and retrieve stats data accordingly, supported types are 'analytics' and 'webmasters'
		$googleData = $this->get ( 'DataWebmasters' );
		if(!$this->getModel()->getState('loggedout')) {
			$tpl = 'webmasters';
		}
		
		// jQuery conditional loading
		if($this->getModel()->getComponentParams()->get('includejquery', 1)) {
			$this->loadJQuery($this->document, false);
		}
		$this->loadJQueryUI($this->document); // Required for calendar feature
		$this->loadBootstrap($this->document, null);
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
		$this->canExport = (bool)$this->getModel()->getState('hasExportPermission', true);
		$this->option = $this->getModel ()->getState ( 'option' );
		$this->cparams = $this->getModel ()->getComponentParams ();
		
		// View operations
		$this->_prepareDocument();
		
		parent::display ($tpl);
	}
}