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

class JabuilderModelLogin extends JModelForm
{
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_jabuilder.login', 'login', array('load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}
	
	public function getRow($pid) {
		
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		
		$query	->select('*')
				->from($db->quoteName('#__jabuilder_pages'))
				->where($db->quoteName('id').'='.(int) $pid);
		
		$db->setQuery($query);
		
		return $db->loadObject();
	}
	
	public function autologin($userid, $session_id)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		
		$query	->select('*') 
				->from($db->quoteName('#__session'))
				->where($db->quoteName('userid').'='.$db->quote($userid))
				->where($db->quoteName('session_id').'='.$db->quote($session_id))
				->where($db->quoteName('client_id').'=1');
		
		$db->setQuery($query);
		
		$result = $db->loadObject();
		
		if (!empty($result))
		{
			$query	->clear()
					->select($db->quoteName(array('username','name','email')))
					->from($db->quoteName('#__users'))
					->where($db->quoteName('id').'='.$db->quote($userid));
			
			$db->setQuery($query);
			
			$row = $db->loadObject();
			
			$user = array (
				'status'	=> 1,
				'type'		=> 'Joomla',
				'username'	=> $row->username,
				'fullanem'	=> $row->name,
				'email'		=> $row->email,
				'password_clear' => ''
			);
			
			$options = array (
			'action' => 'core.login.site'
			);

			JPluginHelper::importPlugin('user');

			$dispatcher = JDispatcher::getInstance();

			$dispatcher->trigger('onUserLogin', array($user, $options));
		}
	}
	
	public function login()
	{
		JSession::checkToken() or die( 'Invalid Token' );
		
		$app = JFactory::getApplication();

		$input = $app->input;
		
		$credentials = array();
		
		$credentials['username'] = $input->post->get('username');
		
		$credentials['password'] = $input->post->get('password');
		
		$credentials['secretkey'] = '';
		
		$options = array();
		
		$options['remember'] = false;
		
		$options['return']   = $input->post->get('return');
		
		if (true !== $app->login($credentials, $options))
		{
			$data['remember'] = (int) $options['remember'];
			$data['username'] = '';
			$data['password'] = '';
			$data['secretkey'] = '';
			$app->setUserState('users.login.form.data', $data);
		}
		
		// return to edit page even login fail
		$app->redirect( JURI::current().base64_decode($options['return']) );
	}
	
}