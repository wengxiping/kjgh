<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class plgSystemPayplansInstallerScript 
{
    public function postflight($type, $parent)
	{
		if ($type == 'install' || $type == 'update') {
			$name = 'payplans';
			$folder = 'system';
			$status = 1;
			
			$db	= JFactory::getDBO();		        
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__extensions'))
					->set($db->quoteName('enabled') . ' = ' . $db->quote($status))
					->where($db->quoteName('folder') . ' = ' . $db->quote($folder) , 'AND')
					->where($db->quoteName('type') . ' = ' . $db->quote('plugin') , 'AND')
					->where($db->quoteName('element') . ' = ' . $db->quote($name) , 'AND');

			$db->setQuery($query);			
			if (!$db->execute())
				return false;
		}

		return true;
	}
}
