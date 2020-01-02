<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @copyright  Copyright (C) 2005 - 2018. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');
jimport('joomla.application.component.controller');

/**
 * Field API list
 *
 * @since  1.6
 */
class JFormFieldApilist extends JFormField
{
	/**
	 * Function to get input html
	 *
	 * @return  HTML
	 *
	 * @since   1.6
	 */
	public function getInput()
	{
		$html = "<span>" . $this->fetchElement($this->name, $this->value, $this->element, $this->options['controls']) . "</span>";

		if (JVERSION < '3.0')
		{
			$class = "inv-elements-gateways-link";
		}
		else
		{
			$class = "";
		}

		// Show link for payment plugins.
		$html .= '<a
			href="index.php?option=com_plugins&view=plugins&filter_folder=techjoomlaAPI&filter_enabled="
			target="_blank"
			class="btn btn-small btn-primary ' . $class . '">'
				. JText::_('COM_INVITEX_SETTINGS_SETUP_SOCIAL_PLUGINS') .
			'</a>';

		return $html;
	}

	/**
	 * Function to fetch element
	 *
	 * @param   STRING  $name          name
	 * @param   STRING  $value         value
	 * @param   STRING  &$node         node
	 * @param   STRING  $control_name  control name
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		$options = array();

		if ($name == 'jform[invite_methods][]')
		{
			$options = $this->getmethods_multiselect_options();
			$fieldName = $name;
			$html = JHtml::_(
			'select.genericlist', $options, $fieldName, 'class="inputbox"  multiple="multiple" size="5" ', 'value', 'text', $value, $control_name . $name
			);

			if (JVERSION < 3.0)
			{
				$move_up_img = JURI::base() . 'components/com_invitex/assets/images/move_up.png';
				$move_down_img = JURI::base() . 'components/com_invitex/assets/images/move_down.png';
				$html .= '<button type="button" class="btn" onclick="moveUpItem(\'configinvite_methods\')">';
				$html .= '<img class="invitex_image" src="' . $move_up_img . '" />' . JText::_('COM_INVITEX_MOVE_UP') . '</button>';
				$html .= '<button type="button" class="btn" onclick="moveDownItem(\'configinvite_methods\')">';
				$html .= '<img class="invitex_image" src="' . $move_down_img . '" />'
				. JText::_('COM_INVITEX_MOVE_DOWN') . '</button></span>';
			}

			return $html;
		}
		else
		{
			$api_plg_installed = $this->getAPIpluginData();

			foreach ($api_plg_installed as $api)
			{
				$apinames = ucfirst(str_replace('plug_techjoomlaAPI_', '', $api->element));
				$options[] = JHtml::_('select.option', $api->element, $apinames);
			}

			$fieldName = $name;

			return JHtml::_(
			'select.genericlist',  $options, $fieldName, 'class="inputbox"  multiple="multiple" size="5"  ', 'value', 'text', $value, $control_name . $name
			);
		}
	}

	/**
	 * Function to get API data
	 *
	 * @return  OBJECT  API data
	 *
	 * @since   1.6
	 */
	public function getAPIpluginData()
	{
		$db = JFactory::getDbo();
		$condtion = array(0 => '\'techjoomlaAPI\'');
		$condtionatype = join(',', $condtion);
		$query = "SELECT extension_id as id,name,element,enabled as published FROM #__extensions WHERE folder in ($condtionatype) AND enabled=1";
		$db->setQuery($query);

		return $db->loadobjectList();
	}
}
