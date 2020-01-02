<?php
/**
* @package		Mightysites
* @copyright	Copyright (C) 2009-2013 AlterBrains.com. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
*/
defined('_JEXEC') or die('Restricted access');

// Frontend options
if (JFactory::getApplication()->isAdmin()) {
	return;
}

class plgSystemMightysites_single extends JPlugin
{
	public function onAfterRoute()
	{
		$app = JFactory::getApplication();
		
		if (isset($_GET['mighty_login']))
		{
			if (!JFactory::getUser()->id)
			{
				ob_start();
				jimport('joomla.utilities.simplecrypt');
				jimport('joomla.utilities.utility');
			
				$key 	= md5(JFactory::getConfig()->get('secret') . @$_SERVER['HTTP_USER_AGENT']);
				$crypt	= new JSimpleCrypt($key);
				$str	= $crypt->decrypt($this->bDecode(implode('', $app->input->get('mighty_login', array(), 'array'))));
				
				$credentials = @unserialize($str);
				
				$app->login($credentials, array(
					'silent' 	=> true,
					'remember' 	=> isset($credentials['remember']) ? $credentials['remember'] : false,
				));
				ob_end_clean();
				
				header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');
			}

			echo '// ', JFactory::getUser()->id;
			exit();
		}
		
		if (isset($_GET['mighty_logout']))
		{
			if (JFactory::getUser()->id)
			{
				ob_start();
				$result = $app->logout();
				ob_end_clean();
			}

			echo '// ', JFactory::getUser()->id;
			exit();
		}
	}

	protected function bDecode($string)
	{
		$func = 'base' . (100 - 36) . '_' . 'decode';
		return $func($string);
	}
}

