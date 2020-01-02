<?php
// namespace administrator\components\com_jrealtimeanalytics\tables;
/**
 *
 * @package JREALTIMEANALYTICS::EVENTSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage tables
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * ORM Table for event entities
 *
 * @package JREALTIMEANALYTICS::EVENTSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage tables
 * @since 1.0
 */
class TableEventstats extends JTable {
	/**
	 *
	 * @var int
	 */
	public $id = null;
	
	/**
	 *
	 * @var int
	 */
	public $catid = null;
	
	/**
	 *
	 * @var string
	 */
	public $type = null;
	
	/**
	 *
	 * @var string
	 */
	public $name = null;
	
	/**
	 *
	 * @var string
	 */
	public $description = null;
	
	/**
	 *
	 * @var int
	 */
	public $hasgoal = 0;
	
	/**
	 *
	 * @var int
	 */
	public $goal_expectation = null;
	
	/**
	 *
	 * @var int
	 */
	public $goal_notification = 0;
	
	/**
	 *
	 * @var string
	 */
	public $goal_notification_emails = null;
	
	/**
	 *
	 * @var int
	 */
	public $checked_out = 0;
	
	/**
	 *
	 * @var datetime
	 */
	public $checked_out_time = '0000-00-00 00:00:00';
	
	/**
	 *
	 * @var int
	 */
	public $published = 1;
	
	/**
	 *
	 * @var int
	 */
	public $ordering = null;
	
	/**
	 *
	 * @var string
	 */
	public $params = null;
	
	/**
	 * Bind Table override
	 * @override
	 *
	 * @see JTable::bind()
	 */
	public function bind($fromArray, $saveTask = false, $sessionTask = false) {
		parent::bind ( $fromArray );
		
		if ($saveTask) {
			$registry = new JRegistry ();
			$registry->loadArray ( $this->params );
			// Swap for validation
			$this->validationParams = $registry;
			
			$this->params = $registry->toString ();
			
			// Reset nullable fields
			if(!$this->description) {
				$this->description = null;
			}
			
			if(!$this->goal_expectation) {
				$this->goal_expectation = null;
			}
		}
		
		// Reset dyna params for event type if type changed
		if(JFactory::getApplication()->input->get('task') == 'eventstats.changeEntity') {
			$this->params = null;
		}
		
		// Manage complex attributes during session recovering bind/load
		if ($sessionTask) {
			$registry = new JRegistry ( $this->params );
			$this->params = $registry;
		}
		
		return true;
	}
	
	/**
	 * Load Table override
	 * @override
	 *
	 * @see JTable::load()
	 */
	public function load($idEntity = null, $reset = true) {
		// If not $idEntity set return empty object
		if ($idEntity) {
			if (! parent::load ( $idEntity )) {
				return false;
			}
		}
		
		$registry = new JRegistry ();
		$registry->loadString ( $this->params );
		$this->params = $registry;
		
		return true;
	}
	
	/**
	 * Check Table override
	 * @override
	 *
	 * @see JTable::check()
	 */
	public function check() {
		// Skip validation if event type is changing or is getting defined
		if(JFactory::getApplication()->input->get('task') == 'eventstats.changeEntity') {
			return true;
		}
		
		// Name required
		if (! $this->name) {
			$this->setError ( JText::_ ( 'COM_JREALTIME_VALIDATION_ERROR' ) );
			return false;
		}
		
		// Type of event required
		if (! $this->type) {
			$this->setError ( JText::_ ( 'COM_JREALTIME_VALIDATION_ERROR' ) );
			return false;
		}
		
		// Validation for viewurl/mintimeonpage track event type
		if($this->type == 'viewurl' || $this->type == 'mintimeonpage') {
			// Menu page item required if menupage mode chosen
			if ($this->validationParams->get('viewtype') == 'menupage' && !$this->validationParams->get('menupage')) {
				$this->setError ( JText::_('COM_JREALTIME_VALIDATION_ERROR' ) );
				return false;
			}
			
			// Link url required and to be valid
			if ($this->validationParams->get('viewtype') == 'urlpage' && (!$this->validationParams->get('urlpage') || !filter_var($this->validationParams->get('urlpage'), FILTER_VALIDATE_URL))) {
				$this->setError ( JText::_('COM_JREALTIME_VALIDATION_ERROR_URL' ) );
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Store Table override
	 * @override
	 *
	 * @see JTable::store()
	 */
	public function store($updateNulls = true) {
		return parent::store($updateNulls);
	}
	
	/**
	 * Delete Table override
	 * @override
	 *
	 * @see JTable::delete()
	 */
	public function delete($pk = null) {
		$eventDeleted = parent::delete($pk);

		if($eventDeleted) {
			// Delete reference table evemts track by foreign key
			$query = $this->_db->getQuery(true)->delete('#__realtimeanalytics_eventstats_track');
			$query->where('event_id = ' . (int)$pk);
			$this->_db->setQuery($query);
			// Check for a database error.
			$this->_db->execute();
			if ($this->_db->getErrorNum()) {
				$eventDeleted = false;
			}
		}

		return $eventDeleted;
	}
	
	/**
	 * Class constructor
	 *
	 * @param Object& $_db
	 *        	return Object&
	 */
	public function __construct($_db) {
		parent::__construct ( '#__realtimeanalytics_eventstats', 'id', $_db );
	}
}