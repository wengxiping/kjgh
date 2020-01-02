<?php 
/**
 * ------------------------------------------------------------------------
 * JA Builder Package
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;

class JabuilderControllerLogin extends JControllerLegacy
{
	protected $model;
	
	public function __construct($config = array()) 
	{
		parent::__construct($config);
		
		$this->model = $this->getModel('Login', 'JabuilderModel', array());
	}
	
	public function login()
	{
		$this->model->login();
	}
	
	public function autologin() {
		
		$user = JFactory::getUser();

		$input = JFactory::getApplication()->input;

		$id = $input->getInt('id');
		
		$userid = $input->getInt('user');
		
		$session_id = $input->getCmd('session_id');
		
		if ($user->id != $userid && !empty($userid) && !empty($session_id)) 
		{
			$this->model->autologin($userid, $session_id);
		}

		// detect Itemid
		$db = JFactory::getDbo();
		$link = 'index.php?option=com_jabuilder&view=page&id='.$id;
		$dbQuery = $db->getQuery(true)
			->select('id')
			->from('#__menu')
			->where('link=' . $db->quote($link))
			->order('id desc');
		$db->setQuery($dbQuery);
		$Itemid = $db->loadResult();

		if ($Itemid) $link .= '&Itemid=' . $Itemid;

		$url = JRoute::_($link);
		$url = str_replace('&amp;', '&', $url);
		$url .= (preg_match('/\?/', $url) ? '&' : '?') . 'jub=edit';

		$this->setRedirect($url); 
	}
}