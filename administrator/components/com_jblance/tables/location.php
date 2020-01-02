<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	12 March 2015
 * @file name	:	tables/location.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Shows list of Locations(jblance)
 */
 defined('_JEXEC') or die('Restricted access');

 class TableLocation extends JTableNested {
 
 	/**
 	 * @param database A database connector object
 	 */
 	function __construct(JDatabaseDriver $db){
 		parent::__construct('#__jblance_location', 'id', $db);
 	}
 	
 	/**
 	 * Method to retrieve children from parent ID
 	 **/
 	public function getChildren($parent_id){
 		$db		= $this->getDbo();
 	
 		$query	= "SELECT n.id,n.title FROM #__jblance_location AS n, #__jblance_location AS p ".
				  "WHERE n.lft BETWEEN p.lft AND p.rgt AND p.id = ".$db->quote($parent_id)." ORDER BY n.lft";
 		$db->setQuery($query);
 	
 		return $db->loadColumn();
 	}
 	
 	/**
 	 * Method to retrieve parent from child ID
 	 **/
 	public function getParent($child_id){
 		$db		= $this->getDbo();
 	
 		$query	= "SELECT p.id,p.title FROM #__jblance_location AS n, #__jblance_location AS p ".
				  "WHERE n.lft BETWEEN p.lft AND p.rgt AND n.id = ".$db->quote($child_id)." ORDER BY p.lft";
 		$db->setQuery($query);
 	
 		return $db->loadColumn();
 	}
 	
 	public function check(){
 		// Generate a valid alias
 		$this->generateAlias();
 		
 		return true;
 	}
 	
 	/**
 	 * Generate a valid alias from title / date.
 	 * Remains public to be able to check for duplicated alias before saving
 	 *
 	 * @return  string
 	 */
 	public function generateAlias(){
 		if (empty($this->alias)){
 			$this->alias = $this->title;
 		}
 	
 		$this->alias = JApplicationHelper::stringURLSafe($this->alias);
 	
 		if(trim(str_replace('-', '', $this->alias)) == ''){
 			$this->alias = JFactory::getDate()->format("Y-m-d-H-i-s");
 		}
 		
 		return $this->alias;
 	}
 }
