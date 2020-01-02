<?php
// namespace administrator\components\com_jrealtimeanalytics\views\overview;
/**
 * @package JREALTIMEANALYTICS::GOOGLE::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage google
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
 
/**
 * @package JREALTIMEANALYTICS::GOOGLE::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage google
 * @since 2.5
 */
class JRealtimeViewGoogle extends JRealtimeView {
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
		$googleData = $this->get ( 'Data' );
		
		$this->loadJQuery($this->document);
		$this->loadBootstrap($this->document, null);
		
		$this->lists = $lists;
		$this->googleData = $googleData;
		$this->option = $this->getModel ()->getState ( 'option' );
		$this->cparams = $this->getModel ()->getComponentParams ();
		$this->isLoggedIn = $this->getModel()->getToken();
		
		// View operations
		$this->_prepareDocument();
		
		parent::display ();
	}
}