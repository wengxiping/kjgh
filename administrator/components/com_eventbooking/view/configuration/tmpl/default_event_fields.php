<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

if (!empty($this->editor))
{
	echo JHtml::_('bootstrap.addTab', 'configuration', 'event-custom-fields', JText::_('EB_EVENT_CUSTOM_FIELDS', true));

	$extra = '';

	if (file_exists(JPATH_ROOT . '/components/com_eventbooking/fields.xml'))
	{
		$extra = file_get_contents(JPATH_ROOT . '/components/com_eventbooking/fields.xml');
	}

	echo $this->editor->display('event_custom_fields', $extra, '100%', '550', '75', '8', false, null, null, null, array('syntax' => 'xml'));

	echo JHtml::_('bootstrap.endTab');
}