<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('JPATH_BASE') or die();
jimport('joomla.html.parameter.element');
jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * Custom point note field for component params.
 *
 * @package  InviteX
 * @since    1.6
 */
class JFormFieldPointnote extends JFormField
{
	public $type = 'pointnote';

	/**
	 * Method to get the installed components.
	 *
	 * @return	array
	 *
	 * @since	1.6
	 */
	public function getInput()
	{
		$communityfolder = JPATH_SITE . '/components/com_community';
		$cbfolder = JPATH_SITE . '/components/com_comprofiler';
		$esfolder = JPATH_SITE . '/components/com_easysocial';
		$jwfolder = JPATH_SITE . '/components/com_awdwall';
		$altafolder = JPATH_SITE . '/components/com_altauserpoints';
		$alphafolder = JPATH_SITE . '/components/com_alphauserpoints';
		$vmfolder = JPATH_SITE . '/components/com_virtuemart';
		$payplansfolder = JPATH_SITE . '/components/com_payplans';
		$legendClass = 'inv-elements-legend';
		$hintClass = "inv-elements-legend-hint";
		$document = JFactory::getDocument();
		$document->addStyleSheet(JUri::base() . 'components/com_invitex/assets/css/invitex.css');
		$document->addScript(JUri::base() . 'components/com_invitex/assets/js/invitex.js');

		$params = JComponentHelper::getParams('com_invitex');
		$rem_after_days = $params->get('rem_after_days');
		$rem_repeat_times = $params->get('rem_repeat_times');
		$rem_every = $params->get('rem_every');
		$html = array();

		$element = (array) $this->element;

		if (isset($element['@attributes']['id'] ))
		{
			$this->id = $element['@attributes']['id'];
		}

		if (JFolder::exists($alphafolder))
		{
			$aup_click_link = "<a href='" . JURI::root() . "components/com_invitex/alphapoints/invitex_aup.zip'>" . JText::_('HERE') . "</a>";
			$aup_install_link = "<a href='" . JURI::base() . "index.php?option=com_alphauserpoints&task=plugins' target='_blank'>"
			. JText::_('HERE') . "</a>";
			$html[] = '<span id="alphauserpoint_note_desc " class=" clearfix point_system_integration_display point_system_integration_display_for_alpha">'
			. JText::sprintf('AUP_POINT_SYSTEM_NOTE', $aup_click_link, $aup_install_link, JText::_('POINTS_INVITER'), JText::_('POINTS_INVITEE'))
			. "</span>";
		}

		if (JFolder::exists($altafolder))
		{
			$aup_click_link = "<a href='" . JURI::root() . "components/com_invitex/altapoints/invitex_aup.zip'>" . JText::_('HERE') . "</a>";
			$aup_install_link = "<a href='" . JURI::base() . "index.php?option=com_altauserpoints&task=plugins' target='_blank'>"
			. JText::_('HERE') . "</a>";
			$html[] = '<span id="altauserpoint_note_desc " class=" clearfix point_system_integration_display point_system_integration_display_for_alta">'
			. JText::sprintf('AUP_POINT_SYSTEM_NOTE', $aup_click_link, $aup_install_link, JText::_('POINTS_INVITER'), JText::_('POINTS_INVITEE'))
			. "</span>";
		}

		if (JVERSION >= '3.0')
		{
			$hint = $this->hint;

			//  toggle_display_point_integration function is called to show related notes and also

			// Let's remove controls class from parent

			// And, remove control-group class from grandparent
			$script = 'jQuery(document).ready(function(){


				jQuery("#' . $this->id . '").parent().removeClass("controls");
				jQuery("#' . $this->id . '").parent().parent().removeClass("control-group");

				toggle_display_point_integration();

			});';

			$document->addScriptDeclaration($script);
		}

		if (JFolder::exists($communityfolder))
		{
			$html[] = '<span id="jomsocialpoint_note_desc" class="clearfix point_system_integration_display">'
			. JText::_('JS_POINT_SYSTEM_NOTE') . '</span>';
		}

		if (JFolder::exists($esfolder))
		{
			$html[] = '<span id="easysocialpoint_note_desc" class=" clearfix  point_system_integration_display">'
			. JText::_('ES_POINT_SYSTEM_NOTE') . '</span>';
		}

		$html_str = implode('', $html);

		if ($html_str)
		{
			$return = '<div class=" " id="' . $this->id . '"></div>';
			$return .= '<div class="disabled ' . $hintClass . '">' . $html_str . '</div>';

			return $return;
		}
	}
}
