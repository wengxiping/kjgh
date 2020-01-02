<?php
/**
* @package		Broadcast
* @copyright	Copyright © 2012 - All rights reserved.
* @license		GNU/GPL
* @author		TechJoomla
* @author mail	extensions@techjoomla.com
* @website		http://techjoomla.com
*/

defined('_JEXEC') or die('Restricted access');
jimport('joomla.html.pane');
jimport('joomla.application.component.helper');
jimport('joomla.filesystem.folder');
jimport('joomla.form.formfield');

class JFormFieldOpeninviter_update extends JFormField
{
	function getInput()
	{
		return $this->fetchElement($this->name,$this->value,$this->element,$this->options['controls']);
	}

	function fetchElement($name,$value,&$node,$control_name)
	{
		$options = array();

		if($name=='jform[oi_update]')
		{

			$fieldName = $name;
			$url = JURI::root()."index.php?option=com_invitex&task=autoupdate&tmpl=component&pkey=".$this->invitex_params['private_key_cronjob'];
		}
	}

}

