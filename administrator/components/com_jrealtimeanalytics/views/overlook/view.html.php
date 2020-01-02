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
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$user = JFactory::getUser();
		JToolBarHelper::title( JText::_( 'COM_JREALTIME_OVERVIEW_GRAPH' ), 'jrealtimeanalytics' );
		JToolBarHelper::custom('overlook.displaypdf', 'download', 'download', 'COM_JREALTIME_EXPORTPDF', false);
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
													  array('COM_JREALTIME_TOTAL_VISITED_PAGES', 'COM_JREALTIME_TOTAL_VISITORS'),
													  array('x'=>JText::_('COM_JREALTIME_XTITLE_DAYS')));

		$doc = JFactory::getDocument();
		$this->loadJQuery($doc);
		$this->loadJQueryUI($doc); // Required for calendar feature
		$this->loadBootstrap($doc);
		$this->loadJQFancybox($doc);
		
		$doc->addScriptDeclaration("
						Joomla.submitbutton = function(pressbutton) {
							Joomla.submitform( pressbutton );
							if (pressbutton == 'overlook.displaypdf') {
								jQuery('#adminForm input[name=task]').val('overlook.display');
							}
							return true;
						}
					");
		
		$dates = array('start'=>$this->getModel()->getState('fromPeriod'), 'to'=>$this->getModel()->getState('toPeriod'));
		$this->user = JFactory::getUser ();
		$this->userid = $this->user->id;
		$this->nocache = '?time=' . time();
		$this->lists = $lists;
		$this->monthString = $monthString;
		$this->option = $this->getModel ()->getState ( 'option' );
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
		
		parent::display ( 'list' );
	}
}