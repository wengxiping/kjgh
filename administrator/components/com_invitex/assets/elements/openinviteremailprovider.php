<?php
/**
 * @version    SVN: <svn_id>
 * @package    Invitex
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.html.pane');
jimport('joomla.application.component.helper');
jimport('joomla.filesystem.folder');
jimport('joomla.form.formfield');

/**
 * Open inviter email provider filed
 *
 * @package     Invitex
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldOpeninviteremailprovider extends JFormField
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
		return $this->fetchElement($this->name, $this->value, $this->element, $this->options['controls']);
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

		if ($this->id == 'jform_selectionsemail')
		{
			require JPATH_SITE . "/components/com_invitex/openinviter/config.php";
			require_once JPATH_SITE . '/components/com_invitex/openinviter/openinviter.php';
			require_once JPATH_SITE . "/components/com_invitex/models/emogrifier.php";
			$inviter = new OpenInviter;
			$oi_services = $inviter->getPlugins();

			foreach ($oi_services as $type => $providers)
			{
				if ($type == 'email')
				{
					foreach ($providers as $provider => $details)
					{
						$options[] = JHTML::_('select.option', $details['name'], $details['name']);
						$fieldName = $name;
					}

					return JHtml::_(
					'select.genericlist', $options, $fieldName, 'class="inputbox"  multiple="true" ', 'value', 'text', $value, $control_name . $name
					);
				}
			}
		}

		return $html;
	}
}
