<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	20 March 2012
 * @file name	:	tables/budget.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Class for table (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
	
class TableCategory extends JTable {
	
	/**
	* @param database A database connector object
	*/
	function __construct(&$db){
		parent::__construct('#__jblance_category', 'id', $db);
	}
	
	/**
	 * Method to retrieve children from parent ID
	 **/
	public function getChildren($parent_id){
		$db		= $this->getDbo();
	
		$query	= "SELECT c.id, c.category FROM #__jblance_category c WHERE parent=".$db->quote($parent_id)." ORDER BY c.ordering";
		$db->setQuery($query);
	
		return $db->loadColumn();
	}
}
