<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	13 July 2012
 * @file name	:	helpers/feeds.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */

 defined('_JEXEC') or die('Restricted access');
 
 use Joomla\Utilities\ArrayHelper;
 
 require_once(JPATH_ADMINISTRATOR.'/components/com_jblance/helpers/jblance.php');
 jimport('joomla.filesystem.file');
 
class FeedsHelper {
	
	function add($feed, $params = '', $points = 1){
		$db = JFactory::getDbo();
		$db->insertObject('#__jblance_feed', $feed);
	}
	
	function getRawFeeds($userid = 0, $limit = 50, $offset = 0){
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$where = ''; $onActor = '';
		$queryStringsAnd = $queryStringsOr = array();
		
		if(!empty($userid))
			$queryStringsOr[] = "f.target=".$db->quote($userid);
		
		if(!empty($userid)){
			$queryStringsOr[] = "f.actor=".$db->quote($userid);
			$onActor = " AND (f.actor=".$db->quote($userid).")";
		}
			
		if(!empty($userid)){
			//get the list of acitivity id in hidden feed table
			$subQuery	= 'SELECT fh.activity_id FROM #__jblance_feed_hide as fh WHERE fh.user_id = '. $db->quote($userid);
			$db->setQuery($subQuery);
			$subResult	= $db->loadColumn();
			$subResult  = ArrayHelper::toInteger($subResult);	//sanitize the array to integer
			$subString	= implode(',', $subResult);
		
			if( ! empty($subString))
				$queryStringsAnd[] = "f.id NOT IN (".$subString.")";
	    }
	    
		if($user->id == 0){
			// for guest, it is enough to just test access <= 0
			$queryStringsAnd[] = "(f.access <= 0)";
			
		}
		/* elseif( !( $user->usertype == 'Super Administrator'
				|| $user->usertype == 'Administrator'
				|| $user->usertype == 'Manager' )){
			$queryStringsOr[] = "((f.access = 0) {$onActor})";	//guest
			$queryStringsOr[] = "( (f.access = 10) AND ({$user->id} != 0)  {$onActor})";	//site members
			$queryStringsOr[] = "( (f.access = 20) AND (f.actor = {$user->id}) {$onActor})";	//only me
			$queryStringsAnd[] = "f.access != 30";	//no body
		}  */
		else {
			$queryStringsOr[] = "((f.access = 0) {$onActor})";	//guest
			$queryStringsOr[] = "( (f.access = 10) AND ({$user->id} != 0)  {$onActor})";	//site members
			$queryStringsOr[] = "( (f.access = 20) AND (f.actor = {$user->id}) {$onActor})";	//only me
			$queryStringsAnd[] = "f.access != 30";	//no body
		} 
	    
		$orWhere = (count($queryStringsOr) ? '  ('.implode(') OR (', $queryStringsOr ).')' : 'true' );	
		$andWhere = (count($queryStringsAnd) ? '  ('.implode(') AND (', $queryStringsAnd ).')' : 'true' );	
	    
		$query = "SELECT f.*,(TO_DAYS(NOW()) - TO_DAYS(f.created)) as daydiff FROM #__jblance_feed f".
				" WHERE ( ".$orWhere." ) AND ".$andWhere.
	  		 	" ORDER BY f.created DESC".
	  		 	" LIMIT ".(int)$limit." OFFSET ".(int)$offset;//echo $query;
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return $rows;
	}
	
	function getRawNotifyFeeds($userid = 0, $limit = 50){
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$queryStringsAnd = $queryStringsOr = array();
		
		if(!empty($userid))
			$queryStringsOr[] = "f.target=".$db->quote($userid);
		
		if(!empty($userid)){
			//get the list of acitivity id in hidden feed table
			$subQuery = "SELECT fh.activity_id FROM #__jblance_feed_hide as fh WHERE fh.user_id=".$db->quote($userid);
			$db->setQuery($subQuery);
			$subResult	= $db->loadColumn();
			$subResult = ArrayHelper::toInteger($subResult);	//sanitize the array to integer
			$subString	= implode(',', $subResult);
		
			if(!empty($subString))
				$queryStringsAnd[] = "f.id NOT IN (".$subString.")";
		}
		
		$queryStringsAnd[] = "f.is_read=0";
		
		$orWhere = (count($queryStringsOr) ? '  ('.implode(') OR (', $queryStringsOr ).')' : 'true' );
		$andWhere = (count($queryStringsAnd) ? '  ('.implode(') AND (', $queryStringsAnd ).')' : 'true' );
		
		$query = "SELECT f.*,(TO_DAYS(NOW()) - TO_DAYS(f.created)) as daydiff FROM #__jblance_feed f".
				 " WHERE ( ".$orWhere." ) AND ".$andWhere.
				 " ORDER BY f.created DESC".
				 " LIMIT ".(int)$limit;//echo $query;
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return $rows;
	}
	
	function getFeedsData($userid = 0, $limit = 50, $notify =  '', $offset = 0){
		$lang = JFactory::getLanguage();
	 	$lang->load('com_jblance', JPATH_SITE);
	 	
	 	//for notification, get raw feeds for the target only. For feeds, get for both target and actor
	 	if($notify == 'notify')
			$rows = $this->getRawNotifyFeeds($userid, $limit);
	 	else
	 		$rows = $this->getRawFeeds($userid, $limit, $offset);
	 	
		$htmlData = array();
		
		foreach ($rows as $row){
			$act = new stdClass();
			
			$act->id = $row->id;
			
			//get the title
			$actorLink 			= $this->actorLink($row->actor);
			$targetLink 		= $this->targetLink($row->target);
			$projectDetailLink 	= $this->projectDetailLink($row->project_id);
			$myProjectLink 		= $this->myProjectLink($row->target);
			$myBidLink	 		= $this->myBidLink($row->target);
			$managepayLink 		= $this->managepayLink($row->target);
			$serviceDetailLink 	= $this->serviceDetailLink($row->project_id);
			
			$tags = array("{actor}", "{target}", "{projectid}", "{myprojectlink}", "{mybidlink}", "{managepaylink}", "{serviceid}");
			$tagsValues = array($actorLink, $targetLink, $projectDetailLink, $myProjectLink, $myBidLink, $managepayLink, $serviceDetailLink);
			$title = str_replace($tags, $tagsValues, $row->title);
			
			$act->title = $title;
			
			//get the content
			$act->content = $row->content;
			
			//get the logo
			$act->logo = JblanceHelper::getLogo($row->actor, 'class="pull-left img-polaroid" style="width: 36px; height: 36px;"');
			
			//get the days diff
			$day = $row->daydiff;
					
			if($day == 0){
				$act->daysago = JText::_('COM_JBLANCE_TODAY');
			}
			else if($day == 1){
				$act->daysago = JText::_('COM_JBLANCE_YESTERDAY');
			}
			else if($day < 7){
				$act->daysago = JText::sprintf('COM_JBLANCE_DAYS_AGO', $day);
			}
			else if(($day >= 7) && ($day < 30)){
				$dayinterval = 7;						
				$act->daysago = (intval($day/$dayinterval) == 1 ? JText::_('COM_JBLANCE_WEEK_AGO') : JText::sprintf('COM_JBLANCE_WEEKS_AGO', intval($day/$dayinterval)));
			}	
			else if(($day >= 30)){
				$dayinterval = 30;
				$act->daysago = (intval($day/$dayinterval) == 1 ? JText::_('COM_JBLANCE_MONTH_AGO') : JText::sprintf('COM_JBLANCE_MONTHS_AGO', intval($day/$dayinterval)));
			}
			
			$act->isMine = self::isMine($userid, $row->actor);
			
			$htmlData[] = $act;
		}
		return $htmlData;
	}
	
	function actorLink($id){
		$linkName = ($id == 0)? false : true;
		$user	  = JFactory::getUser($id);
		
		$config = JblanceHelper::getConfig();
		$showUsername = $config->showUsername;
		$nameOrUsername = ($showUsername) ? 'username' : 'name';
		
		// Wrap the name with link to his/her profile
		$html = $user->$nameOrUsername;
		
		if($linkName){
			$html = LinkHelper::GetProfileLink($user->id, $user->$nameOrUsername);
		}
		return $html;
	}
	
	function targetLink($id){
		$linkName = ($id == 0)? false : true;
		$user	  = JFactory::getUser($id);
		
		$config = JblanceHelper::getConfig();
		$showUsername = $config->showUsername;
		$nameOrUsername = ($showUsername) ? 'username' : 'name';
		
		// Wrap the name with link to his/her profile
		$html = $user->$nameOrUsername;
		
		if($linkName){
			$html = LinkHelper::GetProfileLink($user->id, $user->$nameOrUsername);
		}
		return $html;
	}
	
	function projectDetailLink($id){
		$project = JTable::getInstance('project', 'Table');
		$project->load($id);
		
		$html = LinkHelper::getProjectLink($id, $project->project_title);
		return $html;
	}
	
	function serviceDetailLink($id){
		$service	= JTable::getInstance('service', 'Table');
		$service->load($id);
		
		$html = LinkHelper::getServiceLink($id, $service->service_title);
		return $html;
	}
	
	//generate link to buyer's "My Projects" page
	function myProjectLink($target){
		$user	  = JFactory::getUser();
		$html = '';
		
		//generate link if the target == current userid
		if($target == $user->id){
			$myProjectURL = JRoute::_('index.php?option=com_jblance&view=project&layout=showmyproject');
			$html = JHtml::_('link', $myProjectURL, '<i class="jbf-icon-search"></i>');
		}
		return $html;
	}
	
	//generate link to freelancer's "My Bids" page
	function myBidLink($target){
		$user	  = JFactory::getUser();
		$html = '';
		
		//generate link if the target == current userid
		if($target == $user->id){
			$myBidURL = JRoute::_('index.php?option=com_jblance&view=project&layout=showmybid');
			$html = JHtml::_('link', $myBidURL, '<i class="jbf-icon-search"></i>');
		}
		return $html;
	}
	
	//generate link to Manage Payment page
	function managepayLink($target){
		$user	  = JFactory::getUser();
		$html = '';
		
		//generate link if the target == current userid
		if($target == $user->id){
			$myBidURL = JRoute::_('index.php?option=com_jblance&view=membership&layout=managepay');
			$html = JHtml::_('link', $myBidURL, '<i class="jbf-icon-search"></i>');
		}
		return $html;
	}
	
	static public function isMine($id1, $id2){
		return ($id1 == $id2) && (($id1 != 0) || ($id2 != 0));
	}
}
