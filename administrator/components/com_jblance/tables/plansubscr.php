<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	15 March 2012
 * @file name	:	tables/plan.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Class for table (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
	
class TablePlanSubscr extends JTable {
	
	/**
	 * @param database A database connector object
	 */
	function __construct(&$db){
		parent::__construct('#__jblance_plan_subscr', 'id', $db);
	}
	
	public function planJoin($subscr_id){
		$db	= JFactory::getDbo();
	
		$query = "SELECT ug_id FROM #__jblance_plan_subscr ps ".
				 "INNER JOIN #__jblance_plan p ON ps.plan_id=p.id ".
				 "WHERE ps.id=".$db->quote($subscr_id);
		$db->setQuery($query);
		$ug_id	= $db->loadResult();
		return $ug_id;
	}
}
