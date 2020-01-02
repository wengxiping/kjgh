<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPThemesHelperFloatLabel extends PPThemesHelperAbstract
{
	/**
	 * Generates a country input with a floating label
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function country($label, $name, $value, $id = '', $attributes = '')
	{
		if (!$id) {
			$id = $name;
		}

		$label = JText::_($label);

		$theme = PP::themes();
		$theme->set('label', $label);
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);

		$output = $theme->output('site/helpers/floatlabel/country');

		return $output;
	}

	/**
	 * Generates a list input with a floating label
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function lists($label, $name, $value, $id = '', $attributes = '', $options = array())
	{
		if (!$id) {
			$id = $name;
		}

		$label = JText::_($label);

		$theme = PP::themes();
		$theme->set('label', $label);
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);
		$theme->set('options', $options);

		$output = $theme->output('site/helpers/floatlabel/lists');

		return $output;
	}

	/**
	 * Generates a checkbox input
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function checkbox($label, $name, $value, $id = '', $attributes = '', $options = array())
	{
		if (!$id) {
			$id = $name;
		}
		
		$label = JText::_($label);
		$theme = PP::themes();
		$theme->set('label', $label);
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);
		$theme->set('options', $options);

		$output = $theme->output('site/helpers/floatlabel/checkbox');

		return $output;
	}

	/**
	 * Generates a text input with a floating label
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function password($label, $name, $value, $id = '', $attributes = '')
	{
		if (!$id) {
			$id = $name;
		}

		$label = JText::_($label);

		$theme = PP::themes();
		$theme->set('label', $label);
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);

		$output = $theme->output('site/helpers/floatlabel/password');

		return $output;
	}

	/**
	 * Generates a text input with a floating label
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function text($label, $name, $value, $id = '', $attributes = '', $options = array())
	{
		if (!$id) {
			$id = $name;
		}

		$label = JText::_($label);
		$theme = PP::themes();
		$theme->set('label', $label);
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);
		$theme->set('options', $options);

		$output = $theme->output('site/helpers/floatlabel/text');

		return $output;
	}

	/**
	 * Generates a text input with a floating label
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function textarea($label, $name, $value, $id = '', $attributes = '')
	{
		if (!$id) {
			$id = $name;
		}

		$label = JText::_($label);

		$theme = PP::themes();
		$theme->set('label', $label);
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);

		$output = $theme->output('site/helpers/floatlabel/textarea');

		return $output;
	}

	/**
	 * As there is no way to generate a nice toggler option with floatlabel, we'll use dropdown instead
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function toggler($label, $name, $value, $id = '', $attributes = '', $options = array())
	{
		if (!$id) {
			$id = $name;
		}

		if (!$options) {
			$options = array();
			$no = new stdClass();
			$no->title = JText::_('No');
			$no->value = 0;
			$options[] = $no;

			$yes = new stdClass();
			$yes->title = JText::_('Yes');
			$yes->value = 1;
			$options[] = $yes;
		}

		$label = JText::_($label);

		$theme = PP::themes();
		$theme->set('label', $label);
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('attributes', $attributes);
		$theme->set('options', $options);

		$output = $theme->output('site/helpers/floatlabel/lists');

		return $output;
	}
}
