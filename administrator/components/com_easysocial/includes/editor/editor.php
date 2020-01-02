<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialEditor extends EasySocial
{
	/**
	 * This is to fix the "none" editor bug on Joomla 3.7.0
	 * 
	 *
	 * @since	2.0.17
	 * @access	public
	 */
	public function getEditor($type)
	{
		// Fix issues with Joomla 3.7.0 doesn't render core js by default
		JHtml::_('behavior.core');

		$editor = JFactory::getEditor($type);

		return $editor;
	}

	/**
	 * Since Joomla 3.7.0 has different implementation of $editor->getContent, we need to abstract it here.
	 * 
	 * Joomla 3.7.0 TinyMCE replaces xx-yy-zz with xx_yy_zz
	 * Joomla 3.6.0 TinyMCE doesn't replace anything
	 *
	 * @since	2.0.17
	 * @access	public
	 */
	public function getContent($editor, $inputName)
	{
		$isJoomla37 = version_compare(JVERSION, '3.7.0') !== -1;
		$type = $editor->get('_name');

		if ($type == 'tinymce' && $isJoomla37) {
			$inputName = str_ireplace('-', '_', $inputName);
		}

		return $editor->getContent($inputName);
	}
}