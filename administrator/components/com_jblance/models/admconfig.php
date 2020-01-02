<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	14 March 2012
 * @file name	:	models/admconfig.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 use Joomla\Utilities\ArrayHelper;

 jimport('joomla.application.component.model');
 
class JblanceModelAdmconfig extends JModelLegacy {
	function __construct(){
		parent :: __construct();
	}
	
	function getConfig(){
	
		$row = JTable::getInstance('config', 'Table');
		$row->load(1);
	
		// Convert the params field to an array.
		$registry = new JRegistry;
		$registry->loadString($row->params);
		$params = $registry->toObject();
	
		$return[0] = $row;
		$return[1] = $params;
		return $return;
	}
	
	public function getShowUserGroup(){
	
		// Initialize variables
		$app = JFactory::getApplication();
		$db	 = JFactory::getDbo();
	
		$filter_order     = $app->getUserStateFromRequest('com_jblance_filter_order_usrgrp', 'filter_order', 'ug.ordering', 'cmd');
		$filter_order_Dir = $app->getUserStateFromRequest('com_jblance_filter_order_Dir_usrgrp', 'filter_order_Dir', 'asc', 'word');
		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'int');
		$limitstart	= $app->getUserStateFromRequest('com_jblance.limitstart', 'limitstart', 0, 'int');
		
		$this->setState('filter_order', $filter_order);
		$this->setState('filter_order_Dir', $filter_order_Dir);
		$lists['order_Dir']	= $this->getState('filter_order_Dir');
		$lists['order']     = $this->getState('filter_order');
	
		// Get the total number of records for pagination
		$query	= "SELECT COUNT(*) FROM #__jblance_usergroup";
		$db->setQuery($query);
		$total = $db->loadResult();
	
		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);
	
		$query	= "SELECT ug.*, (SELECT COUNT(*) FROM #__jblance_user u WHERE u.ug_id=ug.id) usercount FROM #__jblance_usergroup ug ".
				  "ORDER BY ordering";
		$db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
		$rows	= $db->loadObjectList();
	
		$return[0] = $rows;
		$return[1] = $pageNav;
		$return[2] = $lists;
		return $return;
	}
	
	//7.Salary Type - edit
	function getEditUserGroup(){
		$app  	= JFactory::getApplication();
		$db		= JFactory::getDbo();
		$row 	= JTable::getInstance('jbusergroup', 'Table');
		$cid 	= $app->input->get('cid', array(), 'array');
		$cid 	= ArrayHelper::toInteger($cid);

		$isNew = (empty($cid))? true : false;
		if(!$isNew)
			$row->load($cid[0]);
	
		$fields = $this->getFields();
	
		// Convert the params field to an array.
		$registry = new JRegistry;
		$registry->loadString($row->params);
		$params = $registry->toArray();
	
		$return[0] = $row;
		$return[1] = $fields;
		$return[2] = $params;
	
		return $return;
	}
	
	//2.Membership Plans - show
	function getShowPlan(){
		$app 	= JFactory::getApplication();
		$db		= JFactory::getDbo();
		$where 	= array();
	
		$filter_order     = $app->getUserStateFromRequest('com_jblance_filter_order_plan', 'filter_order', 'p.ordering', 'cmd');
		$filter_order_Dir = $app->getUserStateFromRequest('com_jblance_filter_order_Dir_plan', 'filter_order_Dir', 'asc', 'word');
		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'int');
		$limitstart	= $app->getUserStateFromRequest('com_jblance.limitstart', 'limitstart', 0, 'int');
	
		$this->setState('filter_order', $filter_order);
		$this->setState('filter_order_Dir', $filter_order_Dir);
		$lists['order_Dir']	= $this->getState('filter_order_Dir');
		$lists['order']     = $this->getState('filter_order');
		
		$ug_id	 	= $app->getUserStateFromRequest('com_jblance_filter_plan_ug_id', 'ug_id', '', 'int');
		$select = JblanceHelper::get('helper.select');		// create an instance of the class SelectHelper
		$lists['ug_id'] = $select->getSelectUserGroups('ug_id', $ug_id, 'COM_JBLANCE_SELECT_USERGROUP', '', 'onchange="document.adminForm.submit();"');
		
		if($ug_id != ''){
			$where[] = "p.ug_id=".$db->quote($ug_id);
		}
		$where = (count($where) ? ' WHERE ('.implode( ') AND (', $where ) . ')' : '');
	
		$query = "SELECT COUNT(*) FROM #__jblance_plan";
		$db->setQuery($query);
		$total = $db->loadResult();
	
		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);
	
		$query = "SELECT p.*, COUNT(s.id) as subscr, ug.name groupName FROM #__jblance_plan p ".
				 "LEFT JOIN #__jblance_plan_subscr AS s ON s.plan_id = p.id ".
				 "LEFT JOIN `#__jblance_usergroup` AS ug ON p.ug_id = ug.id ".
				 $where.
				 "GROUP BY p.id ".
				 "ORDER BY p.ordering ASC";
		$db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
		$rows = $db->loadObjectList();
		
		//check for default plan for each user group
		$query = "SELECT id,name FROM #__jblance_usergroup WHERE published=1";
		$db->setQuery($query);
		$usergroups = $db->loadObjectList();
		
		foreach($usergroups as $usergroup){
			$query = "SELECT id FROM #__jblance_plan WHERE default_plan=1 AND ug_id=".$db->quote($usergroup->id);
			$db->setQuery($query);
			$defaultPlanId = $db->loadResult();
			
			if(empty($defaultPlanId)){
				$app->enqueueMessage(JText::sprintf('COM_JBLANCE_NO_DEFAULT_PLAN_FOR_THE_USERGROUP', $usergroup->name), 'error');
				//$return = JRoute::_('index.php?option=com_jblance&view=guest&layout=showfront', false);
			}
		}
	
		$return[0] = $rows;
		$return[1] = $pageNav;
		$return[2] = $lists;
		return $return;
	}
	
	//2.Membership Plans - edit
	function getEditPlan(){
		$app  	= JFactory::getApplication();
		$row 	= JTable::getInstance('plan', 'Table');
		$cid 	= $app->input->get('cid', array(), 'array');
		$cid 	= ArrayHelper::toInteger($cid);
		
		$isNew = (empty($cid))? true : false;
		if(!$isNew)
			$row->load($cid[0]);
		
		// Convert the params field to an array.
		$registry = new JRegistry;
		$registry->loadString($row->params);
		$params = $registry->toArray();
	
		$return[0] = $row;
		$return[1] = $params;
	
		return $return;
	}
	
	//7a.Pay Modes - show
	function getShowPaymode(){
		$app 	= JFactory::getApplication();
		$db		= JFactory::getDbo();
		
		$filter_order     = $app->getUserStateFromRequest('com_jblance_filter_order_paymode', 'filter_order', 'p.ordering', 'cmd');
		$filter_order_Dir = $app->getUserStateFromRequest('com_jblance_filter_order_Dir_paymode', 'filter_order_Dir', 'asc', 'word');
		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'int');
		$limitstart	= $app->getUserStateFromRequest('com_jblance.limitstart', 'limitstart', 0, 'int');
		
		$this->setState('filter_order', $filter_order);
		$this->setState('filter_order_Dir', $filter_order_Dir);
		$lists['order_Dir']	= $this->getState('filter_order_Dir');
		$lists['order']     = $this->getState('filter_order');
	
		$query = "SELECT COUNT(*) FROM #__jblance_paymode p";
		$db->setQuery($query);
		$total = $db->loadResult();
	
		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);
	
		$query = "SELECT * FROM #__jblance_paymode p ".
				 "ORDER BY p.ordering";
		$db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
		$rows = $db->loadObjectList();
	
		$return[0] = $rows;
		$return[1] = $pageNav;
		$return[2] = $lists;
	
		return $return;
	}
	
	//7a.Pay Modes - edit
	function getEditPaymode(){
		$app  	= JFactory::getApplication();
		$db		= JFactory::getDbo();
		$cid 	= $app->input->get('cid', array(), 'array');
		$cid 	= ArrayHelper::toInteger($cid);
	
		$paymode = JTable::getInstance('paymode', 'Table');
		$paymode->load($cid[0]);
		
		// Convert the params field to an array.
		$registry = new JRegistry;
		$registry->loadString($paymode->params);
		$params = $registry->toObject();
		
		$gwcode = $paymode->gwcode;
		// get the JForm object
		jimport('joomla.form.form');
		$pathToGatewayXML = JPATH_COMPONENT_SITE."/gateways/forms/$gwcode.xml";
		if(file_exists($pathToGatewayXML)){
			$form = JForm::getInstance($gwcode, $pathToGatewayXML, array('control' => 'params', 'load_data' => true));
			$form->bind($params);
		}
		else
			$form = null;
	
		$return[0] = $paymode;
		$return[1] = $params;
		$return[2] = $form;
		return $return;
	}
	
	//7.Custom Field - show
	function getShowCustomField(){
		$app 	= JFactory::getApplication();
		$db		= JFactory::getDbo();
	
		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'int');
		$limitstart	= $app->getUserStateFromRequest('com_jblance.limitstart', 'limitstart', 0, 'int');
		$filter_field_type = $app->getUserStateFromRequest('com_jblance.filter_cust_field_type', 'filter_field_type', 'profile', 'string');
		$filter_order     = $app->getUserStateFromRequest('com_jblance_filter_order_custom', 'filter_order', 'c.ordering', 'cmd');
		$filter_order_Dir = $app->getUserStateFromRequest('com_jblance_filter_order_Dir_custom', 'filter_order_Dir', 'asc', 'word');
		
		$this->setState('filter_order', $filter_order);
		$this->setState('filter_order_Dir', $filter_order_Dir);
	
		$where = '';
		if(!empty($filter_field_type))
			$where = " WHERE field_for=".$db->quote($filter_field_type);
	
		$lists['order_Dir']	= $this->getState('filter_order_Dir');
		$lists['order']     = $this->getState('filter_order');
		$lists['field_type'] = $this->getSelectFieldtype('filter_field_type', $filter_field_type, 0, 'onchange="document.adminForm.submit();"');
	
		$query = "SELECT COUNT(*) FROM #__jblance_custom_field c ".$where;
		$db->setQuery($query);
		$total = $db->loadResult();
	
		jimport('joomla.html.pagination');
		$pageNav = new JPagination( $total, $limitstart, $limit);
	
		$query = "SELECT * FROM #__jblance_custom_field c ".
				 $where." ".
				 "ORDER BY c.ordering";//echo $query;
		$db->setQuery($query/*, $pageNav->limitstart, $pageNav->limit*/);
		$rows = $db->loadObjectList();
	
		$parents = $children = array();
		foreach($rows as $ct){
			if($ct->parent == 0)
				$parents[] = $ct;
			else
				$children[] = $ct;
		}
		//$ordered = '';
		
		if(count($parents)){
			foreach($parents as $pt){
				$ordered[] = $pt;
				foreach($children as $ct){
					if($ct->parent == $pt->id){
						$ordered[]= $ct;
					}
				}
			}
			$rows = $ordered;
		}
		
		$return[0] = $rows;
		$return[1] = $pageNav;
		$return[2] = $lists;
		$return[3] = $filter_field_type;
		
		return $return;
	}
	
	//7.Custom Field - edit
	function getEditCustomField(){
		$app 	= JFactory::getApplication();
		$db		= JFactory::getDbo();
		$row 	= JTable::getInstance('custom', 'Table');
		$cid 	= $app->input->get('cid', array(), 'array');
		$cid 	= ArrayHelper::toInteger($cid);
		
		$isNew = (empty($cid)) ? true : false;
		if(!$isNew)
			$row->load($cid[0]);
	
		$filter_field_type = $app->getUserStateFromRequest('com_jblance.filter_cust_field_type', 'field_for', 'profile', 'string');
		$lists['field_for'] = $this->getSelectFieldtype('field_for', $filter_field_type, 'profile', 'onchange="document.adminForm.submit();"');
		if($filter_field_type)
			$where = " field_for = ".$db->quote($filter_field_type);
	
		//make selection custom group
		$query = "SELECT id AS value, field_title AS text FROM #__jblance_custom_field WHERE parent=0 AND". $where." ORDER BY ordering";
		$db->setQuery($query);
		$users = $db->loadObjectList();
	
		$types = array();
		foreach($users as $item){
			$types[] = JHtml::_('select.option', $item->value, JText::_($item->text));
		}
		$groups = JHtml::_('select.genericlist', $types, 'parent', 'class="input-medium required" size="8"', 'value', 'text', $row->parent);
	
		$return[0] = $row;
		$return[1] = $groups;
		$return[2] = $lists;
		return $return;
	}
	
	//Email Templates
	function getEmailTemplate(){
		$app  	 = JFactory::getApplication();
		$db 	 = JFactory :: getDbo();
		$tempFor = $app->input->get('tempfor', 'subscr-pending', 'string');
	
		$query = "SELECT * FROM #__jblance_emailtemplate WHERE templatefor = ".$db->quote($tempFor);
		$db->setQuery($query);
		$template = $db->loadObject();
	
		return $template;
	}
	
	//13.Category - show
	function getShowCategory(){
		$app = JFactory::getApplication();
		$db	= JFactory::getDbo();
		$select = JblanceHelper::get('helper.select');		// create an instance of the class SelectHelper
	
		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'int');
		$limitstart	= $app->getUserStateFromRequest('com_jblance.limitstart', 'limitstart', 0, 'int');
		$filter_order     = $app->getUserStateFromRequest('com_jblance_filter_order_category', 'filter_order', 'c.ordering', 'cmd');
		$filter_order_Dir = $app->getUserStateFromRequest('com_jblance_filter_order_Dir_category', 'filter_order_Dir', 'asc', 'word');
		
		$this->setState('filter_order', $filter_order);
		$this->setState('filter_order_Dir', $filter_order_Dir);
		$lists['order_Dir']	= $this->getState('filter_order_Dir');
		$lists['order']     = $this->getState('filter_order');
	
		$query = "SELECT COUNT(*) FROM #__jblance_category c";
		$db->setQuery($query);
		$total = $db->loadResult();
	
		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);
	
		$query = "SELECT * FROM #__jblance_category c WHERE c.parent=0 ORDER BY c.ordering";
		$db->setQuery($query);
		$categs = $db->loadObjectList();
	
		// subcategories view as tree
		$tree = array();
	
		foreach($categs as $v) {
			$indent = '';
			$tree[] = $v;
			$tree = $select->getSubcategories($v->id, $indent, $tree, 1, true);
		}
		$rows = array_slice($tree, $pageNav->limitstart, $pageNav->limit);
	
		$return[0] = $rows;
		$return[1] = $pageNav;
		$return[2] = $lists;
		return $return;
	}
	
	//13.Category - edit
	function getEditCategory(){
		$app  	= JFactory::getApplication();
		$db		= JFactory::getDbo();
		$row 	= JTable::getInstance('category', 'Table');
		$cid 	= $app->input->get('cid', array(), 'array');
		$cid 	= ArrayHelper::toInteger($cid);
		
		$isNew = (empty($cid)) ? true : false;
		if(!$isNew)
			$row->load($cid[0]);
	
		return $row;
	}
	
	function getShowBudget(){
		$app = JFactory::getApplication();
		$db	= JFactory::getDbo();
		
		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'int');
		$limitstart	= $app->getUserStateFromRequest('com_jblance.limitstart', 'limitstart', 0, 'int');
		$filter_order     = $app->getUserStateFromRequest('com_jblance_filter_order_budget', 'filter_order', 'b.ordering', 'cmd');
		$filter_order_Dir = $app->getUserStateFromRequest('com_jblance_filter_order_Dir_budget', 'filter_order_Dir', 'asc', 'word');
		
		$this->setState('filter_order', $filter_order);
		$this->setState('filter_order_Dir', $filter_order_Dir);
		$lists['order_Dir']	= $this->getState('filter_order_Dir');
		$lists['order']     = $this->getState('filter_order');
		
		$query = "SELECT COUNT(*) FROM #__jblance_budget b";
		$db->setQuery($query);
		$total = $db->loadResult();
		
		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);
		
		$query = "SELECT * FROM #__jblance_budget b ORDER BY b.ordering";
		$db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
		$rows = $db->loadObjectList();
		
		$return[0] = $rows;
		$return[1] = $pageNav;
		$return[2] = $lists;
		return $return;
	}
	
	function getEditBudget(){
		$app  	= JFactory::getApplication();
		$db		= JFactory::getDbo();
		$row 	= JTable::getInstance('budget', 'Table');
		$cid 	= $app->input->get('cid', array(), 'array');
		$cid 	= ArrayHelper::toInteger($cid);
		
		$isNew = (empty($cid)) ? true : false;
		if(!$isNew)
			$row->load($cid[0]);
	
		return $row;
	}
	
	function getShowDuration(){
		$app = JFactory::getApplication();
		$db	= JFactory::getDbo();
		
		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'int');
		$limitstart	= $app->getUserStateFromRequest('com_jblance.limitstart', 'limitstart', 0, 'int');
		$filter_order     = $app->getUserStateFromRequest('com_jblance_filter_order_duration', 'filter_order', 'd.ordering', 'cmd');
		$filter_order_Dir = $app->getUserStateFromRequest('com_jblance_filter_order_Dir_duration', 'filter_order_Dir', 'asc', 'word');
		
		$this->setState('filter_order', $filter_order);
		$this->setState('filter_order_Dir', $filter_order_Dir);
		$lists['order_Dir']	= $this->getState('filter_order_Dir');
		$lists['order']     = $this->getState('filter_order');
		
		$query = "SELECT COUNT(*) FROM #__jblance_duration d";
		$db->setQuery($query);
		$total = $db->loadResult();
		
		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);
		
		$query = "SELECT * FROM #__jblance_duration d ORDER BY d.ordering";
		$db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
		$rows = $db->loadObjectList();
		
		$return[0] = $rows;
		$return[1] = $pageNav;
		$return[2] = $lists;
		return $return;
	}
	
	function getEditDuration(){
		$app  	= JFactory::getApplication();
		$row 	= JTable::getInstance('duration', 'Table');
		$cid 	= $app->input->get('cid', array(), 'array');
		$cid 	= ArrayHelper::toInteger($cid);
		
		$isNew = (empty($cid)) ? true : false;
		if(!$isNew)
			$row->load($cid[0]);
	
		return $row;
	}
	
	function getShowLocation(){
		$app 	= JFactory::getApplication();
		$db		= JFactory::getDbo();
		$where 	= array();
	
		$limit			  = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'int');
		$limitstart		  = $app->getUserStateFromRequest('com_jblance.limitstart', 'limitstart', 0, 'int');
		$filter_order     = $app->getUserStateFromRequest('com_jblance_filter_order_location', 'filter_order', 'l.lft', 'cmd');
		$filter_order_Dir = $app->getUserStateFromRequest('com_jblance_filter_order_Dir_location', 'filter_order_Dir', 'asc', 'word');
		$search			  = $app->getUserStateFromRequest('com_jblance_location_search', 'search', '', 'string');
		if(strpos($search, '"') !== false){
			$search = str_replace(array('=', '<'), '', $search);
		}
		$search = strtolower($search);
	
		$this->setState('filter_order', $filter_order);
		$this->setState('filter_order_Dir', $filter_order_Dir);
		$orderby = $this->_buildContentOrderBy();
		$lists['order_Dir']	= $this->getState('filter_order_Dir');
		$lists['order']     = $this->getState('filter_order');
		$lists['search'] 	= $search;
		
		if(isset($search) && $search != ''){
			$searchEscaped = $db->quote('%'.$db->escape($search, true).'%', false);
			$where[] = "l.title LIKE ".$searchEscaped;
		}
		
		$where[] = "l.extension=".$db->quote('');
		
		$where = (count($where) ? ' WHERE ('.implode(') AND (', $where) . ')' : '' );
		
		$query = "SELECT * FROM #__jblance_location l".
				 $where.
				 $orderby;
		$db->setQuery($query);
		$db->execute();//echo $query;
		$total = $db->getNumRows();
		
		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);
		
		$db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
		$rows = $db->loadObjectList();
	
		// Preprocess the list of items to find ordering divisions.
		$ordering = array();
		foreach ($rows as &$row){
			$ordering[$row->parent_id][] = $row->id;
		}
	
		$return[0] = $rows;
		$return[1] = $pageNav;
		$return[2] = $lists;
		$return[3] = $ordering;
		return $return;
	}
	
	function getEditLocation(){
		$app  	= JFactory::getApplication();
		$row 	= JTable::getInstance('location', 'Table');
		$cid 	= $app->input->get('cid', array(), 'array');
		$cid 	= ArrayHelper::toInteger($cid);
	
		$isNew = (empty($cid)) ? true : false;
		if(!$isNew)
			$row->load($cid[0]);
	
		return $row;
	}	
	function getOptimise(){
	
		// Initialize variables
		$app	= JFactory::getApplication();
		$db		= JFactory::getDbo();
		$result = array();
	
		//get list of user ids removed from Joomla user table
		$query = "SELECT user_id FROM #__jblance_user WHERE user_id NOT IN (SELECT id FROM #__users)";
		$db->setQuery($query);
		$db->execute();
		$num_rows = $db->getNumRows();
		$user_ids = $db->loadColumn();
		$user_ids = ArrayHelper::toInteger($user_ids);
		$user_ids = implode(',', $user_ids);
		if($num_rows > 0)
			$result[] = $num_rows.' users will be deleted from JoomBri users table';
		
		//if user id is empty, return null
		if(empty($user_ids))
			return null;
	
		//get list of project ids to be removed
		$query = "SELECT id FROM #__jblance_project WHERE assigned_userid IN (".$user_ids.") OR publisher_userid IN (".$user_ids.")";
		$db->setQuery($query);
		$db->execute();
		$num_rows = $db->getNumRows();
		$project_ids = $db->loadColumn();
		$project_ids = ArrayHelper::toInteger($project_ids);
		
		if(!empty($project_ids) && is_array($project_ids))
			$project_ids = implode(',', $project_ids);
		else 
			$project_ids = 0;
		
		if($num_rows > 0)
			$result[] = $num_rows.' Projects will be deleted';
	
		// count entries from bid table
		$query = "SELECT COUNT(id) FROM #__jblance_bid WHERE user_id IN (".$user_ids.") OR project_id IN (".$project_ids.")";
		$db->setQuery($query);
		$num_rows = $db->loadResult();
		if($num_rows > 0)
			$result[] = $num_rows.' entries will be deleted from Bids table';
		
		//get list of services to be removed
		$query = "SELECT COUNT(id) FROM #__jblance_service WHERE user_id IN (".$user_ids.")";
		$db->setQuery($query);
		$num_rows = $db->loadResult();
		if($num_rows > 0)
		    $result[] = $num_rows.' entries will be deleted from Service table';
		
		//get list of service orders to be removed
		$query = "SELECT COUNT(id) FROM #__jblance_service_order WHERE user_id IN (".$user_ids.")";
		$db->setQuery($query);
		$num_rows = $db->loadResult();
		if($num_rows > 0)
		    $result[] = $num_rows.' entries will be deleted from Service Order table';
	
		// count entries from custom field value table
		$query = "SELECT COUNT(id) FROM #__jblance_custom_field_value WHERE userid IN (".$user_ids.")";
		$db->setQuery($query);
		$num_rows = $db->loadResult();
		if($num_rows > 0)
			$result[] = $num_rows.' entries will be deleted from Custom Field Value table';
	
		// count entries from deposit table
		$query = "SELECT COUNT(id) FROM #__jblance_deposit WHERE user_id IN (".$user_ids.")";
		$db->setQuery($query);
		$num_rows = $db->loadResult();
		if($num_rows > 0)
			$result[] = $num_rows.' entries will be deleted from Deposit table';
	
		// count entries from escrow table
		$query = "SELECT COUNT(id) FROM #__jblance_escrow WHERE from_id IN (".$user_ids.") OR to_id IN (".$user_ids.")";
		$db->setQuery($query);
		$num_rows = $db->loadResult();
		if($num_rows > 0)
			$result[] = $num_rows.' entries will be deleted from Escrow table';
		
		// count entries from Expiry Alert table
		$query = "SELECT COUNT(id) FROM #__jblance_expiry_alert WHERE user_id IN (".$user_ids.")";
		$db->setQuery($query);
		$num_rows = $db->loadResult();
		if($num_rows > 0)
			$result[] = $num_rows.' entries will be deleted from Expiry Alert table';
	
		// count entries from Favourite table
		$query = "SELECT COUNT(id) FROM #__jblance_favourite WHERE actor IN (".$user_ids.") OR target IN (".$user_ids.")";
		$db->setQuery($query);
		$num_rows = $db->loadResult();
		if($num_rows > 0)
			$result[] = $num_rows.' entries will be deleted from Favourite table';
	
		// count entries from feeds table
		$query = "SELECT COUNT(id) FROM #__jblance_feed WHERE actor IN (".$user_ids.") OR target IN (".$user_ids.")";
		$db->setQuery($query);
		$num_rows = $db->loadResult();
		if($num_rows > 0)
			$result[] = $num_rows.' entries will be deleted from Feeds table';
	
		// count entries from feeds hide table
		$query = "SELECT COUNT(id) FROM #__jblance_feed_hide WHERE user_id IN (".$user_ids.")";
		$db->setQuery($query);
		$num_rows = $db->loadResult();
		if($num_rows > 0)
			$result[] = $num_rows.' entries will be deleted from Feeds Hide table';
	
		// count entries from forum table
		$query = "SELECT COUNT(id) FROM #__jblance_forum WHERE user_id IN (".$user_ids.")";
		$db->setQuery($query);
		$num_rows = $db->loadResult();
		if($num_rows > 0)
			$result[] = $num_rows.' entries will be deleted from Forum table';
	
		// count entries from message table
		$query = "SELECT COUNT(id) FROM #__jblance_message WHERE idFrom IN (".$user_ids.") OR idTo IN (".$user_ids.")";
		$db->setQuery($query);
		$num_rows = $db->loadResult();
		if($num_rows > 0)
			$result[] = $num_rows.' entries will be deleted from Message table';
	
		// count entries from notify table
		$query = "SELECT COUNT(id) FROM #__jblance_notify WHERE user_id IN (".$user_ids.")";
		$db->setQuery($query);
		$num_rows = $db->loadResult();
		if($num_rows > 0)
			$result[] = $num_rows.' entries will be deleted from Notify table';
	
		// count entries from plan subscr table
		$query = "SELECT COUNT(id) FROM #__jblance_plan_subscr WHERE user_id IN (".$user_ids.")";
		$db->setQuery($query);
		$num_rows = $db->loadResult();
		if($num_rows > 0)
			$result[] = $num_rows.' entries will be deleted from Plan Subscription table';
	
		// count entries from portfolio table
		$query = "SELECT COUNT(id) FROM #__jblance_portfolio WHERE user_id IN (".$user_ids.")";
		$db->setQuery($query);
		$num_rows = $db->loadResult();
		if($num_rows > 0)
			$result[] = $num_rows.' entries will be deleted from Portfolio table';
	
		// count entries from project file table
		$query = "SELECT COUNT(id) FROM #__jblance_project_file WHERE project_id IN (".$project_ids.")";
		$db->setQuery($query);
		$num_rows = $db->loadResult();
		if($num_rows > 0)
			$result[] = $num_rows.' entries will be deleted from Project File table';
	
		// count entries from rating table
		$query = "SELECT COUNT(id) FROM #__jblance_rating WHERE actor IN (".$user_ids.") OR target IN (".$user_ids.")";
		$db->setQuery($query);
		$num_rows = $db->loadResult();
		if($num_rows > 0)
			$result[] = $num_rows.' entries will be deleted from rating table';
	
		// count entries from report table
		$query = "SELECT COUNT(id) FROM #__jblance_report WHERE (`method` like 'project%' AND params IN (".$project_ids.")) OR (`method` like 'profile%' AND params IN (".$user_ids."))";
		$db->setQuery($query);
		$num_rows = $db->loadResult();
		if($num_rows > 0)
			$result[] = $num_rows.' entries will be deleted from Report table';
	
		// count entries from reporter table
		$query = "SELECT COUNT(id) FROM #__jblance_report_reporter WHERE user_id IN (".$user_ids.")";
		$db->setQuery($query);
		$num_rows = $db->loadResult();
		if($num_rows > 0)
			$result[] = $num_rows.' entries will be deleted from reporter table';
	
		// count entries from transaction table
		$query = "SELECT COUNT(id) FROM #__jblance_transaction WHERE user_id IN (".$user_ids.")";
		$db->setQuery($query);
		$num_rows = $db->loadResult();
		if($num_rows > 0)
			$result[] = $num_rows.' entries will be deleted from Transaction table';
	
		// count entries from withdraw table
		$query = "SELECT COUNT(id) FROM #__jblance_withdraw WHERE user_id IN (".$user_ids.")";
		$db->setQuery($query);
		$num_rows = $db->loadResult();
		if($num_rows > 0)
			$result[] = $num_rows.' entries will be deleted from Withdraw table';
	
		$return[0] = $result;
		$return[1] = $user_ids;
		$return[2] = $project_ids;
	
		return $return;
	}
	
	function getFileManager(){
	    $list = $this->getFolderFileList();//print_r($test);
	    $tree = $this->getFolderTree();
	    
	    $return[0] = $list;
	    $return[1] = $tree;
	    return $return;
	}
	
	/**
	 * Method to get model state variables
	 *
	 * @param   string  $property  Optional parameter name
	 * @param   mixed   $default   Optional default value
	 *
	 * @return  object  The property where specified, the state object where omitted
	 *
	 * @since   1.5
	 */
	public function getState($property = null, $default = null)
	{
	    static $set;
	    
	    if (!$set) {
	        $input  = JFactory::getApplication()->input;
	        $folder = $input->get('folder', '', 'path');
	        $this->setState('folder', $folder);
	        
	        $parent = str_replace("\\", '/', dirname($folder));
	        $parent = ($parent == '.') ? null : $parent;
	        $this->setState('parent', $parent);
	        $set = true;
	    }
	    
	    return parent::getState($property, $default);
	}
	
	/**
	 * Build imagelist
	 *
	 * @return  array
	 *
	 * @since 1.5
	 */
	public function getFolderFileList()
	{
	    static $list;
	    $db		= JFactory::getDbo();
	    
	    // Only process the list once per request
	    if(is_array($list)) {
	        return $list;
	    }
	    
	    // Get current path from request
	    $current = (string) $this->getState('folder');
	    
	    $basePath  = JB_BASE_PATH . ((strlen($current) > 0) ? '/' . $current : '');
	    $mediaBase = str_replace(DIRECTORY_SEPARATOR, '/', JB_BASE_PATH . '/');
	    
	    // Reset base path
	    if(strpos(realpath($basePath), JPath::clean(realpath(JB_BASE_PATH))) !== 0){
	        $basePath = JB_BASE_PATH;
	    }
	    
	    $images  = array ();
	    $folders = array ();
	    $docs    = array ();
	    $videos  = array ();
	    
	    $fileList   = false;
	    $folderList = false;
	    
	    if(file_exists($basePath)){
	        // Get the list of files and folders from the given folder
	        $fileList   = JFolder::files($basePath);
	        $folderList = JFolder::folders($basePath);
	    }
	    
	    if($current == 'profile' || $current == 'profile/original'){
	        $query = "SELECT ju.picture FROM #__jblance_user ju";
	        $db->setQuery($query);
	        $dbFiles = $db->loadColumn();
	    }
	    elseif($current == 'project' || $current == 'project/thumb'){
	        $query = "SELECT p.project_image FROM #__jblance_project p ".   
	                 "UNION ".
	                 "SELECT pf.file_name FROM #__jblance_project_file pf";
	        $db->setQuery($query);
	        $dbFiles = $db->loadColumn();
	    }
	    elseif($current == 'portfolio' || $current == 'portfolio/thumb'){
	        $query = "SELECT p.picture FROM #__jblance_portfolio p UNION ".
                     "SELECT attachment1 p FROM #__jblance_portfolio p UNION ".
                     "SELECT attachment2 p FROM #__jblance_portfolio p UNION ".
                     "SELECT attachment3 p FROM #__jblance_portfolio p UNION ".
                     "SELECT attachment4 p FROM #__jblance_portfolio p UNION ".
                     "SELECT attachment5 p FROM #__jblance_portfolio p";
	        $db->setQuery($query);
	        $dbFiles = $db->loadColumn();
	    }
	    if($current == 'bid'){
	        $query = "SELECT attachment b FROM #__jblance_bid b";
	        $db->setQuery($query);
	        $dbFiles = $db->loadColumn();
	    }
	    elseif($current == 'category' || $current == 'category/thumb'){
	        $query = "SELECT c.category_image FROM #__jblance_category c ";
	        $db->setQuery($query);
	        $dbFiles = $db->loadColumn();
	    }
	    elseif($current == 'custom'){
	        $query = "SELECT cfv.value FROM #__jblance_custom_field_value cfv ".
                     "LEFT JOIN #__jblance_custom_field cf ON cf.id = cfv.fieldid ".
                     "WHERE cf.field_type = 'File'";
	        $db->setQuery($query);
	        $dbFiles = $db->loadColumn();
	    }
	    elseif($current == 'service' || $current == 'service/thumb'){
	        $query = "SELECT s.attachment FROM #__jblance_service s";
	        $db->setQuery($query);
	        $dbFiles = $db->loadColumn();
	    }
	    elseif($current == 'message'){
	        $query = "SELECT m.attachment FROM #__jblance_message m";
	        $db->setQuery($query);
	        $dbFiles = $db->loadColumn();
	    }
	    
	    // Iterate over the files if they exist
	    if ($fileList !== false){
	        $tmpBaseObject = new JObject;
	        
	        foreach ($fileList as $file){
	            if (is_file($basePath . '/' . $file) && substr($file, 0, 1) != '.' && strtolower($file) !== 'index.html'){
	                $tmp = clone $tmpBaseObject;
	                $tmp->name = $file;
	                $tmp->title = $file;
	                $tmp->path = str_replace(DIRECTORY_SEPARATOR, '/', JPath::clean($basePath . '/' . $file));
	                $tmp->path_relative = str_replace($mediaBase, '', $tmp->path);
	                $tmp->size = filesize($tmp->path);
	                
	                $filePos = $this->strpos_array($tmp->name, $dbFiles);
	                
	                //check if the file name is available in the database; if not available, can delete
	                if($filePos > 0 or $filePos ===  0){
	                    $tmp->canDelete = false;
	                }
	                else {
	                    $tmp->canDelete = true;
	                }
	                        
                    $ext = strtolower(JFile::getExt($file));
                    
                    switch ($ext){
                        // Image
                        case 'jpg':
                        case 'png':
                        case 'gif':
                        case 'xcf':
                        case 'odg':
                        case 'bmp':
                        case 'jpeg':
                        case 'ico':
                            $info = @getimagesize($tmp->path);
                            $tmp->width  = @$info[0];
                            $tmp->height = @$info[1];
                            $tmp->type   = @$info[2];
                            $tmp->mime   = @$info['mime'];
                            
                            if(($info[0] > 60) || ($info[1] > 60)){
                                $dimensions = JHelperMedia::imageResize($info[0], $info[1], 60);
                                $tmp->width_60 = $dimensions[0];
                                $tmp->height_60 = $dimensions[1];
                            }
                            else {
                                $tmp->width_60 = $tmp->width;
                                $tmp->height_60 = $tmp->height;
                            }
                            
                            if(($info[0] > 16) || ($info[1] > 16)){
                                $dimensions = JHelperMedia::imageResize($info[0], $info[1], 16);
                                $tmp->width_16 = $dimensions[0];
                                $tmp->height_16 = $dimensions[1];
                            }
                            else {
                                $tmp->width_16 = $tmp->width;
                                $tmp->height_16 = $tmp->height;
                            }
                            
                            $images[] = $tmp;
                            break;
                            
                        // Video
                        case 'mp4':
                            $tmp->icon_32 = 'media/mime-icon-32/' . $ext . '.png';
                            $tmp->icon_16 = 'media/mime-icon-16/' . $ext . '.png';
                            $videos[] = $tmp;
                            break;
                            
                        // Non-image document
                        default:
                            $tmp->icon_32 = 'media/mime-icon-32/' . $ext . '.png';
                            $tmp->icon_16 = 'media/mime-icon-16/' . $ext . '.png';
                            $docs[] = $tmp;
                            break;
                    }
	            }
	        }
	    }
	    
	    // Iterate over the folders if they exist
	    if ($folderList !== false){
	        $tmpBaseObject = new JObject;
	        
	        foreach ($folderList as $folder){
	            $tmp = clone $tmpBaseObject;
	            $tmp->name = basename($folder);
	            $tmp->path = str_replace(DIRECTORY_SEPARATOR, '/', JPath::clean($basePath . '/' . $folder));
	            $tmp->path_relative = str_replace($mediaBase, '', $tmp->path);
	            $count = JHelperMedia::countFiles($tmp->path);
	            $tmp->files = $count[0];
	            $tmp->folders = $count[1];
	            $tmp->canDelete = false;
	            
	            $folders[] = $tmp;
	        }
	    }
	    
	    $list = array('folders' => $folders, 'docs' => $docs, 'images' => $images, 'videos' => $videos);
	    
	    return $list;
	}
	
	/**
	 * @param string $fileName
	 * @param array $dbFileName
	 * @return boolean true if filename exist in the database
	 */
	function strpos_array($fileName, $dbFileName) {
	    
	    if(is_array($dbFileName)){
	        foreach($dbFileName as $str){
	            $pos = strpos($str, $fileName);
	            if($pos !== FALSE) {
	                return $pos;
	            }
	        }
	    }
	}
	
	
	/**
	 * Get the folder tree
	 * @param   mixed  $base  Base folder | null for using base media folder
	 * @return  array
	 */
	public function getFolderTree($base = null){
	    // Get some paths from the request
	    if (empty($base)){
	        $base = JB_BASE_PATH;
	    }
	    
	    $mediaBase = str_replace(DIRECTORY_SEPARATOR, '/', JB_BASE_PATH . '/');
	    
	    // Get the list of folders
	    jimport('joomla.filesystem.folder');
	    $folders = JFolder::folders($base, '.', true, true);
	    
	    $tree = array();
	    
	    foreach ($folders as $folder){
	        $folder   = str_replace(DIRECTORY_SEPARATOR, '/', $folder);
	        $name     = substr($folder, strrpos($folder, '/') + 1);
	        $relative = str_replace($mediaBase, '', $folder);
	        $absolute = $folder;
	        $path     = explode('/', $relative);
	        $node     = (object) array('name' => $name, 'relative' => $relative, 'absolute' => $absolute);
	        $tmp      = &$tree;
	        
	        for ($i = 0, $n = count($path); $i < $n; $i++){
	            if (!isset($tmp['children'])){
	                $tmp['children'] = array();
	            }
	            
	            if ($i == $n - 1){
	                // We need to place the node
	                $tmp['children'][$relative] = array('data' => $node, 'children' => array());
	                break;
	            }
	            
	            if (array_key_exists($key = implode('/', array_slice($path, 0, $i + 1)), $tmp['children'])){
	                $tmp = &$tmp['children'][$key];
	            }
	        }
	    }
	    
	    $tree['data'] = (object) array('name' => JText::_('COM_JBLANCE'), 'relative' => '', 'absolute' => $base);
	    return $tree;
	}
	
	/* Misc Functions */
	function _buildContentOrderBy(){
		$app = JFactory::getApplication();
	
		$orderby = '';
		$filter_order     = $this->getState('filter_order');
		$filter_order_Dir = $this->getState('filter_order_Dir');
	
		/* Error handling is never a bad thing*/
		if(!empty($filter_order) && !empty($filter_order_Dir) ){
			$orderby = ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
		}
	
		return $orderby;
	}
	
	public function &getFields(){
		// Initialize variables
		$app	= JFactory::getApplication();
		$db		= JFactory::getDbo();
	
		$query	= "SELECT * FROM #__jblance_custom_field ".
				  "WHERE field_for='profile' ".
				  "ORDER BY ordering";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
	
		$parents = $children = array();
		foreach($rows as $ct){
			if($ct->parent == 0)
				$parents[] = $ct;
			else
				$children[] = $ct;
		}
		//$ordered = '';
	
		if(count($parents)){
			foreach($parents as $pt){
				$ordered[] = $pt;
				foreach($children as $ct){
					if($ct->parent == $pt->id){
						$ordered[]= $ct;
					}
				}
			}
			$rows = $ordered;
		}
	
		return $rows;
	}
	
	//7.getSelectDuration
	function getSelectDuration($var, $default, $disabled, $event){
		$option = '';
		if($disabled == 1)
			$option = 'disabled';
	
		$types[] = JHtml::_('select.option', 'days', JText::_('COM_JBLANCE_DAYS'));
		$types[] = JHtml::_('select.option', 'weeks', JText::_('COM_JBLANCE_WEEKS'));
		$types[] = JHtml::_('select.option', 'months', JText::_('COM_JBLANCE_MONTHS'));
		$types[] = JHtml::_('select.option', 'years', JText::_('COM_JBLANCE_YEARS'));
	
		$lists = JHtml::_('select.genericlist', $types, $var, "class=\"input-small\" size=\"1\" $option $event", 'value', 'text', $default);
		return $lists;
	}
	
	//20.getSelectFieldtype
	function getSelectFieldtype($var, $default, $disabled, $event){
		$option = '';
		if($disabled == 1)
			$option = 'disabled';
	
		$types[] = JHtml::_('select.option', 'profile', JText::_('COM_JBLANCE_PROFILE'));
		$types[] = JHtml::_('select.option', 'project', JText::_('COM_JBLANCE_PROJECT'));
	
		$lists 	 = JHtml::_('select.genericlist', $types, $var, "class='input-medium' size='1' $option $event", 'value', 'text', $default);
		return $lists;
	}
	
	function getSelectTheme($var, $default){
		$types[] = JHtml::_('select.option', 'blue', JText::_('Blue'));
		$types[] = JHtml::_('select.option', 'green', JText::_('Green'));
		$types[] = JHtml::_('select.option', 'red', JText::_('Red'));
		$types[] = JHtml::_('select.option', 'orange', JText::_('Orange'));
		$types[] = JHtml::_('select.option', 'grey', JText::_('Grey'));
		$types[] = JHtml::_('select.option', 'black', JText::_('Black'));
	
		$lists 	 = JHtml::_('select.genericlist', $types, $var, 'class="input-medium" size="1"', 'value', 'text', $default);
	
		return $lists;
	}
	
	function getselectDateFormat($var, $default){
		$types[] = JHtml::_('select.option', 'd-m-Y', JText::_('dd-mm-yyyy'));
		$types[] = JHtml::_('select.option', 'm-d-Y', JText::_('mm-dd-yyyy'));
		$types[] = JHtml::_('select.option', 'Y-m-d', JText::_('yyyy-mm-dd'));
	
		$lists 	 = JHtml::_('select.genericlist', $types, $var, 'class="input-medium" size="1"', 'value', 'text', $default);
	
		return $lists;
	}
	
	//Get the Joomla user group title for non-super users
	function getJoomlaUserGroupTitles($id){
		$db = JFactory::getDbo();
		$id = JblanceHelper::sanitizeCsvString($id);	//sanitize the comma separted string
		$query = "SELECT title FROM #__usergroups ug WHERE ug.id IN (".$id.")";
		$db->setQuery($query);
		$cats = $db->loadColumn();
		if($cats)
			return implode($cats, ", ");
		else
			return '';
	}
}
