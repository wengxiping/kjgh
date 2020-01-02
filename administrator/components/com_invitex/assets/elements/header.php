<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('JPATH_BASE') or die();
jimport('joomla.html.parameter.element');
jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * Cron header
 *
 * @since  1.6
 */
class JFormFieldHeader extends JFormField
{
	public $type = 'Header';

	/**
	 * Function to get input
	 *
	 * @return  null
	 *
	 * @since  1.5
	 * */
	public function getInput()
	{
		$communityfolder = JPATH_SITE . '/components/com_community';
		$cbfolder = JPATH_SITE . '/components/com_comprofiler';
		$esfolder = JPATH_SITE . '/components/com_easysocial';
		$jwfolder = JPATH_SITE . '/components/com_awdwall';
		$alphafolder = JPATH_SITE . '/components/com_alphauserpoints';
		$vmfolder = JPATH_SITE . '/components/com_virtuemart';
		$payplansfolder = JPATH_SITE . '/components/com_payplans';

		$legendClass = 'inv-elements-legend';
		$hintClass = "inv-elements-legend-hint";

		$element = (array) $this->element;

		$document = JFactory::getDocument();
		$document->addStyleSheet(JUri::base() . 'components/com_invitex/assets/css/invitex.css');

		if (isset($element['@attributes']['id']))
		{
			$id = $element['@attributes']['id'];
		}

		if (!empty($this->class) and $this->class == 'show_notes')
		{
			return $return = '<div class="alert alert-info">' . JText::_($this->value) . '</div>';
		}
		elseif($id == "show_automated_remonder_desc")
		{
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$hint = $this->hint;

				// Tada...

				// Let's remove controls class from parent

				// And, remove control-group class from grandparent
				$script = 'jQuery(document).ready(function(){
					jQuery("#' . $id . '").parent().removeClass("controls");
					jQuery("#' . $id . '").parent().parent().removeClass("control-group");
				});';

				$document->addScriptDeclaration($script);
			}

			$params = JComponentHelper::getParams('com_invitex');
			$rem_after_days = $params->get('rem_after_days');
			$rem_repeat_times = $params->get('rem_repeat_times');
			$rem_every = $params->get('rem_every');
			$return = '<span class="clearfix" id="' . $id . '"></span>';

			return $return .= '<span class=" ' . $hintClass . '">'
			. JText::sprintf('AUTOMATE_REM_NOTE', $rem_after_days, $rem_repeat_times, $rem_every) . '<br><br></span><br><br>';
		}
		else
		{
			$return = '<div class="invitex_header_div_outer"><div class="invitex_header_div_inner">'
			. JText::_($this->value) . '</div></div>';

			return $return;
		}
	}
}
