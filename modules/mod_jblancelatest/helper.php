<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	29 March 2012
 * @file name	:	modules/mod_jblancelatest/helper.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 // no direct access
 defined('_JEXEC') or die('Restricted access');
 include_once(JPATH_ADMINISTRATOR.'/components/com_jblance/helpers/jblance.php');	//include this helper file to make the class accessible in all other PHP files
 include_once(JPATH_ADMINISTRATOR.'/components/com_jblance/helpers/link.php');	//include this helper file to make the class accessible in all other PHP files
 JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_jblance/tables');

class ModJblanceLatestHelper {	
	
	public static function getLatestProjects($total_row, $limit_title, $project_type){
		$db	  = JFactory::getDbo();
		$user = JFactory::getUser();
		$now  = JFactory::getDate();
		$type = 'TRUE';
		
		if($project_type == 'featured')
			$type = 'is_featured=1';
		elseif($project_type == 'urgent')
			$type = 'is_urgent=1';
		elseif($project_type == 'private')
			$type = 'is_private=1';
		elseif($project_type == 'sealed')
			$type = 'is_sealed=1';
		elseif($project_type == 'nda')
			$type = 'is_nda=1';
	
		$query = "SELECT p.*,(TO_DAYS(p.start_date) - TO_DAYS(NOW())) AS daydiff FROM #__jblance_project p ".
				 "WHERE p.status=".$db->quote('COM_JBLANCE_OPEN')." AND p.approved=1  AND ".$db->quote($now)." > p.start_date AND ".$type." ".
				 "ORDER BY p.is_featured DESC, p.id DESC ".
				 "LIMIT 0,".(int)$total_row;
		$db->setQuery($query);
		$db->execute();
		$total = $db->getNumRows();
	
		$db->setQuery($query);
		$rows2 = $db->loadObjectList();
		
		$rows = null;
		if(count($rows2)){
			$i = 0;
			foreach($rows2 as $row){
				$rows[$i] = new stdClass();
				$row->project_title = self::limitTitle($row->project_title, $limit_title);
				
				$rows[$i] = $row;
				$rows[$i]->categories = JblanceHelper::getCategoryNames($row->id_category);
				$rows[$i]->bids = self::countBids($row->id);
				$i++;
			}
		}
	
		return $rows;
	}
	
	public static function countBids($id){
		$db = JFactory::getDbo();
		$row = JTable::getInstance('project', 'Table');
		$row->load($id);
		
		//for nda projects, bid count should include only signed bids
		$ndaQuery = 'TRUE';
		if($row->is_nda)
			$ndaQuery = "is_nda_signed=1";
		
		$query = "SELECT COUNT(*) FROM #__jblance_bid WHERE project_id=".$db->quote($id)." AND ".$ndaQuery;
		$db->setQuery($query);
		$total = $db->loadResult();
		return $total;
	}
	
	public static function limitTitle($title, $limit_title){
		$len = strlen($title);
		if($len < $limit_title || $limit_title == 0){
			return $title;
		}
		else {
			$trimmed = substr($title, 0, $limit_title);
			return $trimmed.'...';
		}
	}
}
