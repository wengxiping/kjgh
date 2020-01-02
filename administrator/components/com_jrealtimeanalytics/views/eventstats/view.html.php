<?php
// namespace administrator\components\com_jrealtimeanalytics\views\eventstats;
/**
 * @package JREALTIMEANALYTICS::EVENTSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage eventstats
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
 
/**
 * @package JREALTIMEANALYTICS::EVENTSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage eventstats
 * @since 1.0
 */
class JRealtimeViewEventstats extends JRealtimeView {
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addEditEntityToolbar() {
		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$isNew		= ($this->record->id == 0);
		$checkedOut	= !($this->record->checked_out == 0 || $this->record->checked_out == $userId);
		$toolbarHelperTitle = $isNew ? 'COM_JREALTIME_EVENT_NEW' : 'COM_JREALTIME_EVENT_EDIT';
	
		$doc = JFactory::getDocument();
		JToolBarHelper::title( JText::_( $toolbarHelperTitle ), 'jrealtimeanalytics' );
	
		if ($isNew)  {
			// For new records, check the create permission.
			if ($isNew && ($user->authorise('core.create', 'com_jrealtimeanalytics'))) {
				JToolBarHelper::apply( 'eventstats.applyEntity', 'JAPPLY');
				JToolBarHelper::save( 'eventstats.saveEntity', 'JSAVE');
				JToolBarHelper::save2new( 'eventstats.saveEntity2New');
			}
		} else {
			// Can't save the record if it's checked out.
			if (!$checkedOut) {
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($user->authorise('core.edit', 'com_jrealtimeanalytics')) {
					JToolBarHelper::apply( 'eventstats.applyEntity', 'JAPPLY');
					JToolBarHelper::save( 'eventstats.saveEntity', 'JSAVE');
					JToolBarHelper::save2new( 'eventstats.saveEntity2New');
					JToolBarHelper::custom('eventstats.showEntitycsv', 'download', 'download', 'COM_JREALTIME_EXPORTCSV', false);
					JToolBarHelper::custom('eventstats.showEntityxls', 'download', 'download', 'COM_JREALTIME_EXPORTXLS', false);
					JToolBarHelper::custom('eventstats.showEntitypdf', 'download', 'download', 'COM_JREALTIME_EXPORTPDF', false);
				}
			}
		}
			
		JToolBarHelper::custom('eventstats.cancelEntity', 'cancel', 'cancel', 'JCANCEL', false);
	}
	
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$user = JFactory::getUser();
		JToolBarHelper::title( JText::_( 'COM_JREALTIME_EVENTS_LIST' ), 'jrealtimeanalytics' );
		// Access check.
		if ($user->authorise('core.create', 'com_jrealtimeanalytics')) {
			JToolBarHelper::addNew('eventstats.editEntity', 'COM_JREALTIME_ADD_EVENT');
		}
	
		if ($user->authorise('core.edit', 'com_jrealtimeanalytics')) {
			JToolBarHelper::editList('eventstats.editEntity', 'COM_JREALTIME_EDIT_EVENT');
			JToolBarHelper::custom( 'eventstats.copyEntity', 'copy.png', 'copy_f2.png', 'COM_JREALTIME_DUPLICATE_EVENT' );
		}
	
		if ($user->authorise('core.delete', 'com_jrealtimeanalytics') && $user->authorise('core.edit', 'com_jrealtimeanalytics')) {
			JToolBarHelper::deleteList(JText::_('COM_JREALTIME_DELETE_EVENT'), 'eventstats.deleteEntity');
		}
			
		JToolBarHelper::custom('eventstats.showCategories', 'folder', 'folder', 'COM_JREALTIME_CATEGORIES', false);
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
		$lists = $this->get ( 'Filters' );
		$total = $this->get ( 'Total' );
		
		$doc = JFactory::getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$doc->addStylesheet ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/css/eventstats.css' );
		
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
		$this->lists = $lists;
		$this->orders = $orders;
		$this->items = $rows;
		$this->option = $this->getModel ()->getState ( 'option' );
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
		
		parent::display ( 'list' );
	}
	
	/**
	 * Edit entity view
	 *
	 * @access public
	 * @param Object& $row the item to edit
	 * @return void
	 */
	public function editEntity($row) {
		// Sanitize HTML Object2Form
		JFilterOutput::objectHTMLSafe( $row );
		
		// Load JS Client App dependencies
		$doc = JFactory::getDocument();
		$base = JUri::root();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$this->loadValidation($doc);
		$doc->addStylesheet ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/css/eventstats.css' );
		
		// Inject js translations
		$translations = array();
		$this->injectJsTranslations($translations, $doc);
		
		// Load specific JS App
		$doc->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/eventstats.js' );
		$doc->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/libraries/tablesorter/jquery.tablesorter.js');
		$doc->addScriptDeclaration("
					jQuery(function($){
						$('table.table-striped').tablesorter({ 
							cssHeader: ''
						});
					});
					
					Joomla.submitbutton = function(pressbutton) {
						if(!jQuery.fn.validation) {
							jQuery.extend(jQuery.fn, jrealtimejQueryBackup.fn);
						}
						jQuery('#adminForm').validation();
				
						if (pressbutton == 'eventstats.cancelEntity') {
							jQuery('#adminForm').off();
							Joomla.submitform( pressbutton );
							return true;
						}
				
						if(jQuery('#adminForm').validate()) {
							Joomla.submitform( pressbutton );
							return true;
						}
						return false;
					}
				");
		
		$lists = $this->getModel()->getLists($row);
		$this->eventDetails = $this->getModel()->getEventDetails($row->id);
		$this->record = $row;
		$this->lists = $lists;
		$this->cparams = $this->getModel()->getComponentParams();
		$this->livesite = JUri::root();
		
		// Aggiunta toolbar
		$this->addEditEntityToolbar();
		
		// Set timezone if required
		if($this->cparams->get('offset_type', 'joomla') == 'joomla') {
			$jConfig = JFactory::getConfig();
			date_default_timezone_set($jConfig->get('offset', 'UTC'));
		}
		
		parent::display ( 'edit' );
	}
	
	/**
	 * Edit entity view
	 *
	 * @access public
	 * @param Object& $row the item to edit
	 * @return void
	 */
	public function showEntity($row, $tpl) {
		$this->eventDetails = $this->getModel()->getEventDetails($row->id);
		$this->record = $row;
		$this->cparams = $this->getModel()->getComponentParams();
	
		parent::display ( $tpl );
	}
}