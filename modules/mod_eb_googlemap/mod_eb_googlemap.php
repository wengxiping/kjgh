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

// Require library + register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';
require_once dirname(__FILE__) . '/helper.php';

// Load component language
EventbookingHelper::loadLanguage();

// Load css
JFactory::getDocument()->addStyleSheet(JUri::root(true) . '/modules/mod_eb_googlemap/asset/style.css');
EventbookingHelper::loadComponentCssForModules();

JHtml::_('jquery.framework');
JHtml::_('script', 'media/com_eventbooking/assets/js/eventbookingjq.js', false, false);

$rootUri = JUri::root();
$config  = EventbookingHelper::getConfig();

// Module parameters
$width     = $params->get('width', 100);
$height    = $params->get('height', 400);
$zoomLevel = $params->get('zoom_level', 14);
$Itemid    = (int) $params->get('Itemid') ?: EventbookingHelper::getItemid();

if (file_exists(JPATH_ROOT . '/modules/mod_eb_googlemap/asset/marker/map_marker.png'))
{
	$markerUri = $rootUri . 'modules/mod_eb_googlemap/asset/marker/map_marker.png';
}
else
{
	$markerUri = $rootUri . 'modules/mod_eb_googlemap/asset/marker/marker.png';
}

$locations = modEventBookingGoogleMapHelper::loadAllLocations($params, $Itemid);

if (empty($locations))
{
	echo JText::_('EB_NO_EVENTS');

	return;
}

// Calculate center location of the map
$option = JFactory::getApplication()->input->getCmd('option');
$view   = JFactory::getApplication()->input->getCmd('view');

if ($option == 'com_eventbooking' && $view == 'location')
{
	$activeLocation = EventbookingHelperDatabase::getLocation(JFactory::getApplication()->input->getInt('location_id'));

	if ($activeLocation)
	{
		$homeCoordinates = $activeLocation->lat . ',' . $activeLocation->long;
	}
}

if (empty($homeCoordinates))
{
	if (trim($params->get('center_coordinates')))
	{
		$homeCoordinates = trim($params->get('center_coordinates'));
	}
	else
	{
		$homeCoordinates = $locations[0]->lat . ',' . $locations[0]->long;
	}
}

if ($config->get('map_provider', 'googlemap') == 'googlemap')
{
	$layout = 'default';
}
else
{
	$layout = 'openstreetmap';
}

require JModuleHelper::getLayoutPath('mod_eb_googlemap', $layout);
