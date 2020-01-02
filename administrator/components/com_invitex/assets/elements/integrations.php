<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.html.pane');
jimport('joomla.application.component.helper');
jimport('joomla.filesystem.folder');
jimport('joomla.form.formfield');

/**
 * Custom Legend field for component params.
 *
 * @package  InviteX
 * @since    1.6
 */
class JFormFieldIntegrations extends JFormField
{
	/**
	 * Method to get installed components.
	 *
	 * @return	array
	 *
	 * @since	1.6
	 */
	public function getInput()
	{
		return $this->fetchElement($this->name, $this->value, $this->element, $this->options['controls']);
	}

	/**
	 * Method to get installed components.
	 *
	 * @param   STRING  $name          name
	 * @param   STRING  $value         value
	 * @param   STRING  &$node         node
	 * @param   STRING  $control_name  control_name
	 *
	 * @return	array
	 *
	 * @since	1.6
	 */
	public function fetchElement($name,$value,&$node,$control_name)
	{
		$communityfolder = JPATH_SITE . '/components/com_community';
		$cbfolder = JPATH_SITE . '/components/com_comprofiler';
		$esfolder = JPATH_SITE . '/components/com_easysocial';
		$jwfolder = JPATH_SITE . '/components/com_awdwall';
		$altafolder = JPATH_SITE . '/components/com_altauserpoints';
		$alphafolder = JPATH_SITE . '/components/com_alphauserpoints';
		$vmfolder = JPATH_SITE . '/components/com_virtuemart';
		$payplansfolder = JPATH_SITE . '/components/com_payplans';

		// If point integration
		if ($name == 'jform[pt_option]')
		{
			$options[] = JHTML::_('select.option', 'no', JText::_('COM_INVITEX_NO'));

			if (JFolder::exists($communityfolder))
			{
				$options[] =	JHTML::_('select.option', 'jspt', JText::_('JSPT'));
			}

			if (JFolder::exists($esfolder))
			{
				$options[] = JHTML::_('select.option', 'espt', JText::_('ESPT'));
			}

			if (JFolder::exists($altafolder))
			{
				$options[] = JHTML::_('select.option', 'alta', JText::_('ALTA_POINTS'));
			}

			if (JFolder::exists($alphafolder))
			{
				$options[] = JHTML::_('select.option', 'alpha', JText::_('ALPHA_POINTS'));
			}

			$fieldName = $name;

			$attributes = 'class="inputbox" onchange="toggle_display_point_integration();"';

			return JHtml::_(
			'select.genericlist', $options, $fieldName, $attributes, 'value', 'text', $value, $control_name . $name
			);
		}

		$options = array();
		$options[] = JHTML::_('select.option', 'Joomla', JText::_('INV_JOOMLA'));

		if (JFolder::exists($communityfolder))
		{
			$options[] =	JHTML::_('select.option', 'JomSocial', JText::_('INV_JS'));
		}

		if (JFolder::exists($cbfolder))
		{
			$options[] = JHTML::_('select.option', 'Community Builder', JText::_('INV_CB'));
		}

		if (JFolder::exists($jwfolder))
		{
			$options[] = JHTML::_('select.option', 'Jomwall', JText::_('INV_JW'));
		}

		if (JFolder::exists($esfolder))
		{
			$options[] = JHTML::_('select.option', 'EasySocial', JText::_('INV_ES'));
		}

		$fieldName = $name;

		$html = JHtml::_('select.genericlist', $options, $fieldName, 'class="inputbox"', 'value', 'text', $value, $control_name . $name);

		if ($name == 'jform[invitation_during_reg]')
		{
			if (JFolder::exists($cbfolder))
			{
				$html .= '<br/><span >' . JText::_('INV_NOTE_FOR_CB_RIDIRECT_PLUGIN') . '</span>';
				$html .= "<a href=" . JUri::root() . 'components/com_invitex/CB_plug_redirectasregister/plug_redirectasregister.zip'
				. ">" . JText::_('INV_DOWNLOAD') . "</a>";
			}
		}

		return $html;
	}
}
