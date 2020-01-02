<?php
/**
 * @package    TechJoomlaAPI_Yahoo
 * @author     TechJoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport("joomla.html.parameter.element");
jimport('joomla.html.html');
jimport('joomla.form.formfield');

$lang = JFactory::getLanguage();
$lang->load('plug_techjoomlaAPI_yahoo', JPATH_ADMINISTRATOR);

/**
 * Field for API configuration documentation
 *
 * @since  1.0
 */
class JFormFieldPathapi extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.0
	 */
	public $type = 'Pathapi';

	/**
	 * Method to get the field input markup.
	 *
	 * TODO: Add access check.
	 *
	 * @return	string	The field input markup.
	 *
	 * @since	1.6
	 */
	protected function getInput()
	{
		$html = '';

		if ($this->id == 'jform_params_pathapi_yahoo')
		{
			$link = "https://techjoomla.com/documentation-for-invitex/configuring-yahoo-api-plugin.html";
			$html = '<div class="instructions">Go to
			<a href="' . $link . '" target="_blank">How to configure Techjoomla-Yahoo API</a>
			<br /></div>';
		}

		return $html;
	}
}
