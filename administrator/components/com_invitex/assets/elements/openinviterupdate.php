<?php
/**
 * @version    SVN: <svn_id>
 * @package    Invitex
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

/**
 * Open inviter
 *
 * @package     Invitex
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldOpeninviterupdate extends JFormField
{
	/**
	 * Function to get input
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function getInput()
	{
		$this->fetchElement($this->name, $this->value, $this->element, $this->options['controls']);
	}

	/**
	 * Function to get element
	 *
	 * @param   STRING  $name          names
	 * @param   STRING  $value         value
	 * @param   STRING  &$node         node
	 * @param   STRING  $control_name  control name
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function fetchElement($name,$value,&$node,$control_name)
	{
		$oi_path = JPATH_SITE . '/components/com_invitex/openinviter/openinviter.php';

		if (!JFile::exists($oi_path))
		{
			return;
		}

		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::base() . 'components/com_invitex/assets/css/invitex.css');
		$document->addScript(JURI::base() . 'components/com_invitex/assets/js/invitex.js');
		require JPATH_SITE . "/components/com_invitex/openinviter/config.php";
		require_once JPATH_SITE . '/components/com_invitex/openinviter/openinviter.php';
		require_once JPATH_SITE . "/components/com_invitex/models/emogrifier.php";
		$inviter = new OpenInviter;
		$oi_services = $inviter->getPlugins();
		$transport_curl = '';
		$transport_wget = '';

		if (isset($openinviter_settings['transport']) == 'curl')
		{
			$transport_curl = ' selected ';
		}
		else
		{
			$transport_wget = ' selected ';
		}

		$params = JComponentHelper::getParams('com_invitex');
		$private_key_cronjob = $params->get('private_key_cronjob');
		$url = JURI::root() . "index.php?option=com_invitex&task=autoupdate&tmpl=component&pkey="
		. $private_key_cronjob;
		$msg = JText::_('CONFRM_UP');

		echo '<button class="btn btn-primary" type="button" onclick=\'autoup("' . $url . '","'
		. $msg . '")\' >' . JText::_('COM_INVITEXD_UPDATE') . '</button>';
	}
}
