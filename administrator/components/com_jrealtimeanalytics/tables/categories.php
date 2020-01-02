<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined ( 'JPATH_PLATFORM' ) or die ();
jimport('joomla.database.tablenested');

/**
 * Category table
 *
 * @package Joomla.Legacy
 * @subpackage Table
 * @since 11.1
 */
class TableCategories extends JTableNested {
	/**
	 *
	 * @var int
	 */
	public $id = null;
	
	/**
	 *
	 * @var int
	 */
	public $asset_id = null;
	
	/**
	 *
	 * @var string
	 */
	public $parent_id = null;
	
	/**
	 *
	 * @var string
	 */
	public $lft = null;
	
	/**
	 *
	 * @var string
	 */
	public $rgt = null;
	
	/**
	 *
	 * @var int
	 */
	public $level = null;
	
	/**
	 *
	 * @var string
	 */
	public $path = null;
	
	/**
	 *
	 * @var string
	 */
	public $title = null;
	
	/**
	 *
	 * @var string
	 */
	public $alias = null;
	
	/**
	 *
	 * @var string
	 */
	public $description = null;
	
	/**
	 *
	 * @var int
	 */
	public $published = 1;
	
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
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return string
	 *
	 * @since 11.1
	 */
	protected function _getAssetName() {
		$k = $this->_tbl_key;
		
		return 'com_jrealtimeanalytics.category.' . ( int ) $this->$k;
	}
	
	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return string
	 *
	 * @since 11.1
	 */
	protected function _getAssetTitle() {
		return $this->title;
	}
	
	/**
	 * Get the parent asset id for the record
	 *
	 * @param JTable $table
	 *        	A JTable object for the asset parent.
	 * @param integer $id
	 *        	The id for the asset
	 *        	
	 * @return integer The id of the asset's parent
	 *        
	 * @since 11.1
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null) {
		$assetId = null;
		
		// This is a category under a category.
		if ($this->parent_id > 1) {
			// Build the query to get the asset id for the parent category.
			$query = $this->_db->getQuery ( true )->select ( $this->_db->quoteName ( 'asset_id' ) )
												  ->from ( $this->_db->quoteName ( '#__realtimeanalytics_categories' ) )
												  ->where ( $this->_db->quoteName ( 'id' ) . ' = ' . $this->parent_id );
			
			// Get the asset id from the database.
			$this->_db->setQuery ( $query );
			
			if ($result = $this->_db->loadResult ()) {
				$assetId = ( int ) $result;
			}
		} elseif ($assetId === null) { // This is a category that needs to parent with the extension.
			// Build the query to get the asset id for the parent category.
			$query = $this->_db->getQuery ( true );
			$query->select ( $this->_db->quoteName ( 'id' ) );
			$query->from ( $this->_db->quoteName ( '#__assets' ) );
			$query->where ( $this->_db->quoteName ( 'name' ) . ' = ' . $this->_db->quote ( 'com_jrealtimeanalytics') );
			
			// Get the asset id from the database.
			$this->_db->setQuery ( $query );
			if ($result = $this->_db->loadResult ()) {
				$assetId = ( int ) $result;
			}
		}
		
		// Return the asset id.
		if ($assetId) {
			return $assetId;
		} else {
			return parent::_getAssetParentId ( $table, $id );
		}
	}
	
	/**
	 * Override check function
	 *
	 * @return boolean
	 *
	 * @see JTable::check()
	 * @since 11.1
	 */
	public function check() {
		// Check for a title.
		if (trim ( $this->title ) == '') {
			$this->setError ( JText::_ ( 'JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_CATEGORY' ) );
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * Overloaded bind function.
	 *
	 * @param array $array
	 *        	named array
	 * @param string $ignore
	 *        	An optional array or space separated list of properties
	 *        	to ignore while binding.
	 *        	
	 * @return mixed Null if operation was satisfactory, otherwise returns an error
	 *        
	 * @see JTable::bind()
	 * @since 11.1
	 */
	public function bind($array, $ignore = '') {
		parent::bind ( $array, $ignore );
		
		// Bind the rules.
		if (isset ( $array ['rules'] ) && is_array ( $array ['rules'] )) {
			$rules = new JAccessRules ( $array ['rules'] );
			$this->setRules ( $rules );
		}
		
		// Set the new parent id if parent id not matched OR while New/Save as Copy .
		if (!empty($array['haschanged']) || !$this->id) {
			$this->setLocation ( $array ['parent_id'], 'last-child' );
		}
		
		return true;
	}
	
	/**
	 * Overridden JTable::store to set created/modified and user id.
	 *
	 * @param boolean $updateNulls
	 *        	True to update fields even if they are null.
	 *        	
	 * @return boolean True on success.
	 *        
	 * @since 11.1
	 */
	public function store($updateNulls = false) {
		return parent::store ( $updateNulls );
	}
	
	/**
	 * Constructor
	 *
	 * @param JDatabaseDriver $db
	 *        	Database driver object.
	 *        	
	 * @since 11.1
	 */
	public function __construct(JDatabaseDriver $db) {
		parent::__construct ( '#__realtimeanalytics_categories', 'id', $db );
	}
}
