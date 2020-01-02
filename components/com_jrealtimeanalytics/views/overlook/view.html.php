<?php
// namespace administrator\components\com_jrealtimeanalytics\views\overlook;
/**
 * @package JREALTIMEANALYTICS::OVERVIEW::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage overlook
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
 
/**
 * @package JREALTIMEANALYTICS::OVERVIEW::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage overlook
 * @since 2.4
 */
class JRealtimeViewOverlook extends JRealtimeView {
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
		$this->cparams = $this->getModel ()->getComponentParams ();
		// Get main records
		$monthString = null;
		$monthSelected = $this->getModel()->getState('statsMonth', null);
		if($monthSelected) {
			$graphData = $this->get ('DataByMonth');
			$monthString = '- ' . date('F', mktime(0, 0, 0, $monthSelected));
		} else {
			$graphData = $this->get ( 'Data' );
		}
		$lists = $this->get ( 'Lists' );
		
		$graphTypeMethod = 'buildGeneric' . ucfirst($this->getModel()->getState('graphType', 'Bars'));
		$graphGenerator = $this->getModel()->getState('graphRenderer');
		$graphGenerator->$graphTypeMethod($graphData, '_serverstats_overview.png', 
													  JText::sprintf('COM_JREALTIME_OVERVIEW_STATS', $monthString),
													  array('COM_JREALTIME_TOTAL_VISITED_PAGES', 'COM_JREALTIME_TOTAL_VISITORS'));

		// Load jQuery lib
		if($this->cparams->get('includejquery', 1)) {
			$this->loadJQuery($this->document, false);
		}
		$doc = JFactory::getDocument();
		$this->loadBootstrap($doc, null); // Required for calendar feature
		$this->loadJQFancybox($doc);
		
		$this->document->addScriptDeclaration("
						jQuery.submitbutton = function(pressbutton) {
							jQuery.submitform( pressbutton );
							if (pressbutton == 'overlook.displaypdf') {
								setTimeout(function(){
									jQuery('#adminForm input[name=task]').val('overlook.display');
								}, 200);
							}
							return true;
						}
						jQuery(function($){
							$('a[data-role=overview]').fancybox({
								transitionOut : 'none'
							});
						});
					");
		
		$dates = array('start'=>$this->getModel()->getState('fromPeriod'), 'to'=>$this->getModel()->getState('toPeriod'));
		$this->user = JFactory::getUser ();
		$this->userid = $this->user->id ? $this->user->id : session_id();
		$this->nocache = '?time=' . time();
		$this->lists = $lists;
		$this->monthString = $monthString;
		$this->canExport = (bool)$this->getModel()->getState('hasExportPermission', true);
		$this->option = $this->getModel ()->getState ( 'option' );
		
		// View operations
		$this->_prepareDocument();
		
		// Mixin, add include path for admin side to avoid DRY on view templates
		$this->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR . '/views/overlook/tmpl');
		
		parent::display ();
	}
}