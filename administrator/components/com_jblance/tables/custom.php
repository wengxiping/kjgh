<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	14 March 2012
 * @file name	:	tables/config.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Class for table (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
	
class TableCustom extends JTable {
	
	/**
	* @param database A database connector object
	*/
	function __construct(&$db){
		parent::__construct('#__jblance_custom_field', 'id', $db);
	}
	
	/**
	* Method to retrieve parent's ID
	**/	 	
	public function getCurrentParentId(){
		$db		= $this->getDbo();
		
		$query	= 'SELECT '.$db->quoteName('id').' '
				. 'FROM '.$db->quoteName('#__jblance_custom_field').' '
				. 'WHERE '.$db->quoteName('ordering').'<'.$db->Quote($this->ordering).' '
				. 'AND '.$db->quoteName('field_type').'='.$db->Quote('group').' '
				. 'ORDER BY '.$db->quoteName('ordering').' DESC';
		$db->setQuery($query);

		return $db->loadResult();
	}
}
