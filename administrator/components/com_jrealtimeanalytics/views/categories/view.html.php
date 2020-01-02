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
class JRealtimeViewCategories extends JRealtimeView {
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
		$toolbarHelperTitle = $isNew ? 'COM_JREALTIME_CAT_NEW' : 'COM_JREALTIME_CAT_EDIT';
	
		$doc = JFactory::getDocument();
		JToolBarHelper::title( JText::_( $toolbarHelperTitle ), 'jrealtimeanalytics' );
	
		if ($isNew)  {
			// For new records, check the create permission.
			if ($isNew && ($user->authorise('core.create', 'com_jrealtimeanalytics'))) {
				JToolBarHelper::apply( 'categories.applyEntity', 'JAPPLY');
				JToolBarHelper::save( 'categories.saveEntity', 'JSAVE');
				JToolBarHelper::save2new( 'categories.saveEntity2New');
			}
		} else {
			// Can't save the record if it's checked out.
			if (!$checkedOut) {
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($user->authorise('core.edit', 'com_jrealtimeanalytics')) {
					JToolBarHelper::apply( 'categories.applyEntity', 'JAPPLY');
					JToolBarHelper::save( 'categories.saveEntity', 'JSAVE');
					JToolBarHelper::save2new( 'categories.saveEntity2New');
				}
			}
		}
			
		JToolBarHelper::custom('categories.cancelEntity', 'cancel', 'cancel', 'JCANCEL', false);
	}
	
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$user = JFactory::getUser();
		JToolBarHelper::title( JText::_( 'COM_JREALTIME_CATS_LIST' ), 'jrealtimeanalytics' );
		// Access check.
		if ($user->authorise('core.create', 'com_jrealtimeanalytics')) {
			JToolBarHelper::addNew('categories.editEntity', 'COM_JREALTIME_ADD_CAT');
		}
	
		if ($user->authorise('core.edit', 'com_jrealtimeanalytics')) {
			JToolBarHelper::editList('categories.editEntity', 'COM_JREALTIME_EDIT_CAT');
		}
	
		if ($user->authorise('core.delete', 'com_jrealtimeanalytics') && $user->authorise('core.edit', 'com_jrealtimeanalytics')) {
			JToolBarHelper::deleteList(JText::_('COM_JREALTIME_DELETE_CAT'), 'categories.deleteEntity');
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
		$rows = $this->get ( 'Data' );
		$lists = $this->get ( 'Filters' );
		$total = $this->get ( 'Total' );
		
		$doc = JFactory::getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		
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
		
		// Preprocess the list of items to find ordering divisions.
		foreach ($this->items as &$item) {
			$this->ordering[$item->parent_id][] = $item->id;
		}
		
		// Preprocess the list of items to find ordering divisions.
		foreach ($this->items as &$item) {
			$this->ordering[$item->parent_id][] = $item->id;
		}
		
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
	public function editEntity(&$row) {
		// Sanitize HTML Object2Form
		JFilterOutput::objectHTMLSafe( $row );
		
		// Load JS Client App dependencies
		$doc = JFactory::getDocument();
		$base = JUri::root();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$this->loadValidation($doc);
		
		// Inject js translations
		$translations = array();
		$this->injectJsTranslations($translations, $doc);
		
		// Load specific JS App
		$doc->addScriptDeclaration("
					Joomla.submitbutton = function(pressbutton) {
					 	if(!jQuery.fn.validation) {
							jQuery.extend(jQuery.fn, jrealtimejQueryBackup.fn);
						}
				
						jQuery('#adminForm').validation();
				
						if (pressbutton == 'categories.cancelEntity') {
							jQuery('#adminForm').off();
							Joomla.submitform( pressbutton );
							return true;
						}
		
						if(jQuery('#adminForm').validate()) {
							Joomla.submitform( pressbutton );
							return true;
						}
						return false;
					};
					jQuery(function($){
						$('#parent_id').on('change', function(){
							$('input[name=haschanged]').attr('value', 1);
						});
					});
				");
		
		$lists = $this->getModel()->getLists($row);
		$this->record = $row;
		$this->lists = $lists;
		
		// Aggiunta toolbar
		$this->addEditEntityToolbar();
		
		parent::display ( 'edit' );
	}
}