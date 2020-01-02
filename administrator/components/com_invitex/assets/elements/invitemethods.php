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
 * Invite Methods filed
 *
 * @package     Invitex
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldInvitemethods extends JFormField
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
		$options	= $this->getmethods_multiselect_options();
		$fieldName = $name;
		$html = '<span>' . JHtml::_(
		'select.genericlist', $options, $fieldName, 'class="inputbox"  multiple="multiple" size="5" ', 'value', 'text', $value, $control_name . $name
		) . "</span>";

		if (version_compare(JVERSION, 3, 'le'))
		{
			$move_up_img = JURI::base() . 'components/com_invitex/assets/images/move_up.png';
			$move_down_img = JURI::base() . 'components/com_invitex/assets/images/move_down.png';

			$html .= '<span><button type="button" class="btn" onclick="moveUpItem(\'jforminvite_methods\')"><img class="invitex_image" src="'
			. $move_up_img . '" />' . JText::_('COM_INVITEX_MOVE_UP') . '</button>';

			$html .= '<button type="button" class="btn" onclick="moveDownItem(\'jforminvite_methods\')"><img class="invitex_image" src="'
			. $move_down_img . '" />' . JText::_('COM_INVITEX_MOVE_DOWN') . '</button></span>';
		}

		return $html;
	}

	/**
	 * Function to get input
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function getmethods_multiselect_options()
	{
		$params = JComponentHelper::getParams('com_invitex');

		if (($params->get('invite_methods')))
		{
			$config_methods	= $params->get('invite_methods');
		}

		$opt = $inv_methods = array();
		$inv_methods['manual'] = JText::_('INV_METHOD_MANUAL');
		$oi_path = JPATH_SITE . '/components/com_invitex/openinviter/openinviter.php';

		if (JFile::exists($oi_path))
		{
			$inv_methods['oi_email'] = JText::_('INV_METHOD_OI_EMAIL');
			$inv_methods['oi_social'] = JText::_('INV_METHOD_OI_SOCIAL');
		}

		$inv_methods['other_tools']	= JText::_('INV_METHOD_OTHER_TOOLS');
		$inv_methods['inv_by_url']	= JText::_('INV_METHOD_BY_URL');
		$inv_methods['social_apis']	= JText::_('INV_METHOD_SOCIAL_APIS');
		$inv_methods['email_apis']	= JText::_('INV_METHOD_EMAIL_APIS');
		$inv_methods['sms_apis']	= JText::_('INV_METHOD_SMS_APIS');

		if (!empty($config_methods))
		{
			foreach ($config_methods as $m)
			{
				if (isset($inv_methods[$m]))
				{
					$opt[] = JHTML::_('select.option', $m, $inv_methods[$m]);
					unset($inv_methods[$m]);
				}
			}
		}

		foreach ($inv_methods as $v => $t)
		{
			$opt[] = JHTML::_('select.option', $v, $t);
		}

		return $opt;
	}
}
