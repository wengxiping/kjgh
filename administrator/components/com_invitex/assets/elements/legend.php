<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

jimport('joomla.form.formfield');

/**
 * Help by @manoj
 * How to use this?
 * See the code below that needs to be added in form xml
 * Make sure, you pass a unique id for each field
 * Also pass a hint field as Help text
 *
 * <field menu="hide" type="legend" id="inv-product-display"
 * name="inv-product-display" default="COM_INVITEX_DISPLAY_SETTINGS" hint="COM_INVITEX_DISPLAY_SETTINGS_HINT" label="" />
 *
 */

/**
 * Custom Legend field for component params.
 *
 * @package  InviteX
 * @since    2.9.9
 */
class JFormFieldLegend extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'Legend';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 *
	 * @since	1.6
	 */
	public function getInput()
	{
		$document = JFactory::getDocument();
		$document->addStyleSheet(JUri::root() . 'media/com_invitex/css/invitex.css');

		$legendClass = 'inv-elements-legend';
		$hintClass = "inv-elements-legend-hint";
		$hint = "";

		if (JVERSION < '3.0')
		{
			$element = (array) $this->element;

			if (isset($element['@attributes']['hint'] ))
			{
				$hint = $element['@attributes']['hint'];
			}

			if (isset($element['@attributes']['class'] ))
			{
				$hintClass .= $element['@attributes']['class'];
			}
		}
		else
		{
			$hint = $this->hint;

			/*Tada...
			Let's remove controls class from parent
			And, remove control-group class from grandparent*/
			$script = 'jQuery(document).ready(function(){
				jQuery("#' . $this->id . '").parent().removeClass("controls");
				jQuery("#' . $this->id . '").parent().parent().removeClass("control-group");
			});';

			$document->addScriptDeclaration($script);
		}

		// Show them a legend.
		$return = '<legend class="clearfix ' . $legendClass . '" id="' . $this->id . '">' . JText::_($this->value) . '</legend>';

		// Show them a hint below the legend.
		// Let them go - GaGa about the legend.
		if (!empty($hint))
		{
			$return .= '<span class="disabled ' . $hintClass . '">' . JText::_($hint) . '</span>';
			$return .= '<br/><br/>';
		}

		return $return;
	}
}
