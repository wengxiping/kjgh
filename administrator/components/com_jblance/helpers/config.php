<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	18 February 2013
 * @file name	:	helpers/finance.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
defined('_JEXEC') or die('Restricted access');

class ConfigHelper {
	
	protected static $instances = null;
	
	public static function getInstance(){
	
		if(!self::$instances){
			self::$instances = self::load();
		}
		return self::$instances;
	}
	
	public static function load(){
		$config = JTable::getInstance('config', 'Table');
		$config->load(1);
	
		$registry = new JRegistry;
		$registry->loadString($config->params);
		$params = $registry->toObject();
		return $params;
	}
	
}
