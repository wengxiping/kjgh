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
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$user = JFactory::getUser();
		JToolBarHelper::title( JText::_( 'COM_JREALTIME_GOOGLE_ANALYTICS' ), 'jrealtimeanalytics' );
		
		// Store logged in status in session
		if($this->isLoggedIn) {
			JToolBarHelper::custom('google.deleteEntity', 'lock', 'lock', 'COM_JREALTIME_GOOGLE_LOGOUT', false);
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
		$googleData = $this->get ( 'Data' );
		
		$this->loadJQuery($this->document);
		$this->loadBootstrap($this->document);
		
		$dates = array('start'=>$this->getModel()->getState('fromPeriod'), 'to'=>$this->getModel()->getState('toPeriod'));
		$this->lists = $lists;
		$this->googleData = $googleData;
		$this->isLoggedIn = $this->getModel()->getToken();
		$this->option = $this->getModel ()->getState ( 'option' );
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
		
		parent::display ();
	}
}