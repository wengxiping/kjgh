<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class ThemesHelperPanel extends ThemesHelperAbstract
{
	/**
	 * Back end panel heading
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function heading($text, $description = '')
	{
		if (!$description) {
			$description = $text . '_DESC';
		}

		$text = JText::_($text);
		$description = JText::_($description);

		$theme = ES::themes();
		$theme->set('text', $text);
		$theme->set('description', $description);

		$output = $theme->output('admin/html/panel/heading');

		return $output;
	}

	/**
	 * Generates a settings row in the panel body
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function label($text, $help = true, $helpText = '', $columns = 5, $required = false)
	{
		if ($help && !$helpText) {
			$helpText = JText::_($text . '_HELP');

			// Added backward compactibilty.
			if ($helpText == $text . '_HELP') {
				$helpText = JText::_($text . '_DESC');
			}
		}

		$text = JText::_($text);

		$theme = ES::themes();
		$theme->set('columns', $columns);
		$theme->set('text', $text);
		$theme->set('help', $help);
		$theme->set('helpText', $helpText);
		$theme->set('required', $required);

		$output = $theme->output('admin/html/panel/label');

		return $output;
	}

	/**
	 * Generates a standard form line for a form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function formInput($title, $name, $value = '', $desc = '')
	{
		$desc = !$desc ? $title . '_DESC' : $desc;
		$desc = JText::_($desc);
		$title = JText::_($title);

		$theme = ES::themes();

		$theme->set('title', $title);
		$theme->set('desc', $desc);
		$theme->set('name', $name);
		$theme->set('value', $value);

		return $theme->output('admin/html/panel/form.input');
	}


	/**
	 * Generates a standard form line for a form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function formBoolean($title, $name, $value = '', $desc = '', $readonly = false)
	{
		$desc = !$desc ? $title . '_DESC' : $desc;
		$desc = JText::_($desc);
		$title = JText::_($title);

		$theme = ES::themes();

		$theme->set('title', $title);
		$theme->set('desc', $desc);
		$theme->set('name', $name);
		$theme->set('value', $value);

		$attributes = '';
		if ($readonly) {
			$attributes = ' disabled="disabled"';
		}

		$theme->set('attributes', $attributes);

		return $theme->output('admin/html/panel/form.boolean');
	}
}
