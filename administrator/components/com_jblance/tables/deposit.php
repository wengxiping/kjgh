<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	02 April 2012
 * @file name	:	tables/deposit.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Class for table (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
	
class TableDeposit extends JTable {
	
	/**
	* @param database A database connector object
	*/
	function __construct(&$db){
		parent::__construct('#__jblance_deposit', 'id', $db);
	}
}
