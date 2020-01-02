<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldEBLocation extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	protected $type = 'eblocation';

	protected function getOptions()
	{
		$user      = JFactory::getUser();
		$config    = EventbookingHelper::getConfig();
		$db        = JFactory::getDbo();
		$options   = [];
		$options[] = JHtml::_('select.option', 0, JText::_('EB_SELECT_LOCATION'));
		$query     = $db->getQuery(true)
			->select('id, name')
			->from('#__eb_locations')
			->where('published = 1')
			->order('name');

		if (!$user->authorise('core.admin', 'com_eventbooking') && !$config->show_all_locations_in_event_submission_form)
		{
			$query->where('user_id = ' . (int) $user->id);
		}

		$db->setQuery($query);

		foreach ($db->loadObjectList() as $location)
		{
			$options[] = JHtml::_('select.option', $location->id, $location->name);
		}

		return $options;
	}
}
