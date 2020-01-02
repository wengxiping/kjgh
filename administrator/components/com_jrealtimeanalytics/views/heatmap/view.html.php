<?php
// namespace administrator\components\com_jrealtimeanalytics\views\eventstats;
/**
 * @package JREALTIMEANALYTICS::HEATMAP::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage heatmap
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
 
/**
 * @package JREALTIMEANALYTICS::HEATMAP::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage eventstats
 * @since 2.4
 */
class JRealtimeViewHeatmap extends JRealtimeView {
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$user = JFactory::getUser();
		JToolBarHelper::title( JText::_( 'COM_JREALTIME_HEATMAP_LIST' ), 'jrealtimeanalytics' );
	
		if ($user->authorise('core.delete', 'com_jrealtimeanalytics') && $user->authorise('core.edit', 'com_jrealtimeanalytics')) {
			JToolBarHelper::deleteList(JText::_('COM_JREALTIME_DELETE_ENTITY'), 'heatmap.deleteEntity');
		}
		JToolBarHelper::custom('heatmap.displaypdf', 'download', 'download', 'COM_JREALTIME_EXPORTPDF', false);
		
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
		$rows = $this->get ( 'Data' );
		$lists = $this->get ( 'Lists' );
		$total = $this->get ( 'Total' );
		
		// Normalize the graph data and generate the graph
		$graphData = array();
		foreach ($rows as $index=>$row) {
			$graphData['#' . ($index + 1)] = $row->numclicks;
		}
		$graphGenerator = $this->getModel()->getState('graphRenderer');
		$graphGenerator->buildGenericBars($graphData, 
										  '_serverstats_heatmap.png', 
										  'COM_JREALTIME_HEATMAP_GRAPH', 
										  array('COM_JREALTIME_NUMCLICKS'));

		$doc = JFactory::getDocument();
		$this->loadJQuery($doc);
		$this->loadJQueryUI($doc); // Required for calendar feature
		$this->loadBootstrap($doc);
		$this->loadJQFancybox($doc);
		
		$doc->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/heatmap.js' );
		$doc->addScriptDeclaration("
						Joomla.submitbutton = function(pressbutton) {
							Joomla.submitform( pressbutton );
							if (pressbutton == 'heatmap.displaypdf') {
								jQuery('#adminForm input[name=task]').val('heatmap.display');
							}
							return true;
						}
					");
		
		$orders = array ();
		$orders ['order'] = $this->getModel ()->getState ( 'order' );
		$orders ['order_Dir'] = $this->getModel ()->getState ( 'order_dir' );
		// Pagination view object model state populated
		$pagination = new JPagination ( $total, $this->getModel ()->getState ( 'limitstart' ), $this->getModel ()->getState ( 'limit' ) );
		
		$dates = array('start'=>$this->getModel()->getState('fromPeriod'), 'to'=>$this->getModel()->getState('toPeriod'));
		$this->dates = $dates;
		$this->user = JFactory::getUser ();
		$this->pagination = $pagination;
		$this->searchword = $this->getModel ()->getState ( 'searchword' );
		$this->userid = $this->user->id;
		$this->nocache = '?time=' . time();
		$this->lists = $lists;
		$this->orders = $orders;
		$this->items = $rows;
		$this->option = $this->getModel ()->getState ( 'option' );
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
		
		parent::display ( 'list' );
	}
}