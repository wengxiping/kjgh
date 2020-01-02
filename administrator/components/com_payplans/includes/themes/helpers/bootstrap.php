<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPThemesHelperBootstrap extends PPThemesHelperAbstract
{
	/**
	 * Renders popover from bootstrap
	 *
	 * @since	3.7.0
	 * @access	public
	 */
	public function popover($title = '', $content = '', $placement = '' , $placeholder = '' , $html = false )
	{
		if (!$content) {
			$content = $title . '_TOOLTIP';
		}

		if (!$placeholder) {
			$placeholder = $title .'_PLACEHOLDER';
		}

		$title = JText::_($title);
		$content = JText::_($content);
		$placeholder = JText::_($placeholder);
		
		$theme = PP::themes();	
		$theme->set('title', $title);
		$theme->set('content', $content);
		$theme->set('placement', $placement);
		$theme->set('placeholder', $placeholder);
		$theme->set('html', $html);

		return $theme->output('admin/helpers/bootstrap/popover');
	}
}