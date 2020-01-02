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

class PPThemesHelperString extends PPThemesHelperAbstract
{
	/**
	 * Renders the search form on table listings
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function escape($string)
	{
		return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Provides ability to truncate a string
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function truncate($text, $maxChars = 250, $stripTags = true, $ellipses = true)
	{
		if ($stripTags) {
			$text = strip_tags($text);
		}

		$length = JString::strlen($text);

		if ($length <= $maxChars) {
			return $text;
		}

		$text = JString::substr($text, 0, $maxChars);

		if ($ellipses) {
			$text .= JText::_('COM_PP_ELLIPSES');
		}
		
		return $text;
	}
}
