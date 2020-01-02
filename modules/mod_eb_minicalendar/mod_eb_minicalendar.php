<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

// Require library + register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

EventbookingHelper::loadLanguage();
EventbookingHelper::loadComponentCssForModules();

$config = EventbookingHelper::getConfig();

JFactory::getDocument()->addScriptDeclaration(
	'var siteUrl = "' . EventbookingHelper::getSiteUrl() . '";'
);

EventbookingHelper::addLangLinkForAjax();

JHtml::_('jquery.framework');
JHtml::_('script', 'media/com_eventbooking/assets/js/eventbookingjq.js', false, false);
JHtml::_('script', 'media/com_eventbooking/assets/js/minicalendar.js', false, false);

$currentDateData = EventbookingModelCalendar::getCurrentDateData();
$year            = $currentDateData['year'];
$month           = (int) $params->get('default_month', 0);
$categoryId      = (int) $params->get('id', 0);

$Itemid = (int) $params->get('item_id');

if (!$Itemid)
{
	$Itemid = EventbookingHelperRoute::findView('calendar');
}

if (!$month)
{
	$month = $currentDateData['month'];
}

// Get calendar data for the current month and year
$model = new EventbookingModelCalendar(array('remember_states' => false, 'ignore_request' => true));
$model->setState('month', $month)
	->setState('year', $year)
	->setState('id', $categoryId)
	->setState('mini_calendar', 1);

if ($Itemid)
{
	$model->setState('mini_calendar_item_id', $Itemid);
}

$rows = $model->getData();
$data = EventbookingHelperData::getCalendarData($rows, $year, $month, true);

$days     = array();
$startDay = (int) $config->calendar_start_date;

for ($i = 0; $i < 7; $i++)
{
	$days[$i] = EventbookingHelperData::getDayNameHtmlMini(($i + $startDay) % 7, true);
}

$listMonth = array(JText::_('EB_JAN'), JText::_('EB_FEB'), JText::_('EB_MARCH'),
	JText::_('EB_APR'), JText::_('EB_MAY'), JText::_('EB_JUNE'), JText::_('EB_JUL'),
	JText::_('EB_AUG'), JText::_('EB_SEP'), JText::_('EB_OCT'), JText::_('EB_NOV'),
	JText::_('EB_DEC'),);

if (!$Itemid)
{
	$Itemid = EventbookingHelper::getItemid();
}

require JModuleHelper::getLayoutPath('mod_eb_minicalendar', 'default');

