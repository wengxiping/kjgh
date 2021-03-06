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

class PPThemesHelperSettings extends PPThemesHelperAbstract
{
	/**
	 * Renders a textbox for settings
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function textbox($name, $title, $desc = '', $options = array(), $instructions = '', $class = '')
	{
		$theme = PP::themes();
		
		if (empty($desc)) {
			$desc = $title . '_DESC';
		}

		$size = '';
		$postfix = '';
		$prefix = '';
		$attributes = '';
		$type = 'text';

		if (isset($options['type'])) {
			$type = $options['type'];
		}

		if (isset($options['attributes'])) {
			$attributes = $options['attributes'];
		}

		if (isset($options['postfix'])) {
			$postfix = $options['postfix'];
		}

		if (isset($options['prefix'])) {
			$prefix = $options['prefix'];
		}

		if (isset($options['size'])) {
			$size = $options['size'];
		}


		$theme->set('attributes', $attributes);
		$theme->set('type', $type);
		$theme->set('size', $size);
		$theme->set('class', $class);
		$theme->set('instructions', $instructions);
		$theme->set('name', $name);
		$theme->set('title', $title);
		$theme->set('desc', $desc);
		$theme->set('prefix', $prefix);
		$theme->set('postfix', $postfix);

		$contents = $theme->output('admin/helpers/settings/textbox');

		return $contents;
	}

	/**
	 * Renders a toggle button
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function toggle($name, $title, $desc = '', $attributes = '', $note = '', $wrapperAttributes = '')
	{
		$theme = PP::themes();

		if (empty($desc)) {
			$desc = $title . '_DESC';
		}
		
		if ($note) {
			$note = JText::_($note);
		}

		if (is_array($wrapperAttributes)) {
			$wrapperAttributes = implode(' ', $wrapperAttributes);
		}

		$theme->set('note', $note);
		$theme->set('name', $name);
		$theme->set('title', $title);
		$theme->set('desc', $desc);
		$theme->set('attributes', $attributes);
		$theme->set('wrapperAttributes', $wrapperAttributes);

		$contents = $theme->output('admin/helpers/settings/toggle');

		return $contents;
	}
}
