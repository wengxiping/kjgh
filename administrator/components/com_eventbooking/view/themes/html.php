<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class EventbookingViewThemesHtml extends RADViewList
{
	/**
	 * Override add toolbar method to add custom toolbar
	 */
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('EB_THEME_MANAGEMENT'), 'generic.png');
		JToolBarHelper::publishList('publish', JText::_('EB_SET_DEFAULT'));
		JToolBarHelper::deleteList(JText::_('EB_THEME_DELETE_CONFIRM'), 'uninstall', 'Uninstall');
	}
}
