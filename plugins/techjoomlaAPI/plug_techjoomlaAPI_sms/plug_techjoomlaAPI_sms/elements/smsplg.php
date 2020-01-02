<?php
/**
 * @version    SVN: <svn_id>
 * @package    Invitex
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

/**
 * JFormFieldSmsplg form custom element class.
 *
 * @package     Invitex
 * @subpackage  com_invitex
 * @since       2.6
 */
class JFormFieldSmsplg extends JFormField
{
	protected $type = 'smsplg';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	public function getInput()
	{
		return $this->fetchElement($this->name, $this->value, $this->element, $this->options['control']);
	}

	/**
	 * Get needed field data
	 *
	 * @param   string  $name         Name of the field
	 * @param   string  $value        Value of the field
	 * @param   string  &$node        Node of the field
	 * @param   string  $controlName  Field control name
	 *
	 * @return   string  Field HTML
	 */
	public function fetchElement($name, $value, &$node, $controlName)
	{
		$db = JFactory::getDbo();

		$condition = array(0 => '\'sms\'');
		$conditionType = join(',', $condition);

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('extension_id', 'id'));
		$query->select($db->quoteName('name'));
		$query->select($db->quoteName('element'));
		$query->select($db->quoteName('enabled', 'published'));
		$query->from('#__extensions');
		$query->where('enabled=1');
		$query->where('folder IN (' . $conditionType . ')');
		$db->setQuery($query);

		$smsPlugin = $db->loadobjectList();

		$options = array();

		foreach ($smsPlugin as $smsOpt)
		{
			$smsOptName = ucfirst(str_replace('plugsms', '', $smsOpt->element));
			$options[] = JHtml::_('select.option', $smsOpt->element, $smsOptName);
		}

		$fieldName = $name;

		$html = JHtml::_(
		'select.genericlist', $options, $fieldName, 'class="inputbox"', 'value', 'text', $value,
		$controlName . $name
		);

		// Show link for payment plugins.
		$html .= '<a
			href="index.php?option=com_plugins&view=plugins&filter_folder=sms&filter_enabled="
			target="_blank"
			class="btn btn-small btn-primary">'
				. JText::_('PLG_TECHJOOMLAAPI_SMS_SETUP_SMS_PLUGINS') .
			'</a>';

		return $html;
	}

	/**
	 * Get field tooltip
	 *
	 * @param   string  $label        Label of the field
	 * @param   string  $description  Description of the field
	 * @param   string  &$node        Node of the field
	 * @param   string  $controlName  Field control name
	 * @param   string  $name         Field name
	 *
	 * @return   string  Field HTML
	 */
	public function fetchTooltip($label, $description, &$node, $controlName, $name)
	{
		return null;
	}
}
