<?php
/**
 * @package    Invitex
 *
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

/**
 * Cron element.
 *
 * @since  1.6
 */
class JFormFieldCron extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 * @since  1.0
	 */
	public $type = 'cron';

	/**
	 * Method to get the field input markup. @TODO: Add access check.
	 *
	 * @since  2.2.1
	 *
	 * @return   string  The field input markup
	 */
	protected function getInput()
	{
		switch ($this->name)
		{
			case 'jform[private_key_cronjob]' :

				return $this->getCronKey($this->name, $this->value, $this->element, $this->options['control']);

				break;

			case 'jform[cron_get]' :

				return $this->getCronUrl($this->name, $this->value, $this->element, $this->options['control']);

				break;
		}
	}

	/**
	 * Return cron key
	 *
	 * @param   string  $name          name of field
	 * @param   mixed   $value         value of field
	 * @param   string  $node          node of field
	 * @param   string  $control_name  controller name
	 *
	 * @since  2.2.1
	 *
	 * @return  string                 return html
	 */
	protected function getCronKey($name, $value, $node, $control_name)
	{
		// Generate randome string

		if (empty($value))
		{
			$length       = 10;
			$characters   = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$randomString = '';

			for ($i = 0; $i < $length; $i++)
			{
				$randomString .= $characters[rand(0, strlen($characters) - 1)];
			}

			return "<input type='text' name='$name' value=" . $randomString . ">";
		}

		return "<input type='text' name='$name' value=" . $value . "></label>";
	}

	/**
	 * Return cron url
	 *
	 * @param   string  $name          name of field
	 * @param   mixed   $value         value of field
	 * @param   string  $node          node of field
	 * @param   string  $control_name  controller name
	 *
	 * @since  2.2.1
	 *
	 * @return  string                 return html
	 */
	protected function getCronUrl($name, $value, $node, $control_name)
	{
		$params = JComponentHelper::getParams('com_invitex');
		$private_key_cronjob = $params->get('private_key_cronjob');

		if ($name == 'jform[oi_update_cron]')
		{
			$oi_path = JPATH_SITE . '/components/com_invitex/openinviter/openinviter.php';

			if (!file_exists($oi_path))
			{
				return;
			}

			$return = "<input type='text' class='input input-xlarge' onclick='this.select();' value=" . JUri::root() .
			'index.php?option=com_invitex&tmpl=component&task=autoupdate&pkey=' . $private_key_cronjob . ">";
		}
		else
		{
			$return = "<input type='text' class='input input-xlarge' onclick='this.select();' value=" . JUri::root() .
			'index.php?option=com_invitex&tmpl=component&task=mailto&pkey=' . $private_key_cronjob . ">";
		}

		return $return;
	}
}
