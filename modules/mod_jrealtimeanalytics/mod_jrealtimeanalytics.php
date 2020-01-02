<?php 
//namespace modules\mod_jrealtimeanalytics
/**
 * @package JREALTIMEANALYTICS::modules
 * @subpackage mod_jrealtimeanalytics
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ();

// Include the login functions only once
require_once __DIR__ . '/helper.php';

// Load component translations
$jLang = JFactory::getLanguage ();
$jLang->load ( 'com_jrealtimeanalytics', JPATH_ROOT . '/components/com_jrealtimeanalytics', 'en-GB', true, true );
if ($jLang->getTag () != 'en-GB') {
	$jLang->load ( 'com_jrealtimeanalytics', JPATH_SITE, null, true, false );
	$jLang->load ( 'com_jrealtimeanalytics', JPATH_ROOT . '/components/com_jrealtimeanalytics', null, true, false );
}

// Get component params
$cParams = JComponentHelper::getParams('com_jrealtimeanalytics');

// Include component model
require_once JPATH_ADMINISTRATOR . '/components/com_jrealtimeanalytics/framework/exception/exception.php';
require_once JPATH_ADMINISTRATOR . '/components/com_jrealtimeanalytics/framework/model/model.php';

// Instantiate model
JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jrealtimeanalytics/models', 'JRealtimeModel');
$serverStatsModel = JModelLegacy::getInstance('Serverstats', 'JRealtimeModel');

if($cParams->get('module_default_period_interval', 'week') == 'custom' && $cParams->get('module_start_date', null) && $cParams->get('module_end_date', null)) {
	$startPeriod = $cParams->get('module_start_date');
	$endPeriod = $cParams->get('module_end_date');
} elseif($cParams->get('module_default_period_interval', 'week') == 'day') {
	$startPeriod = date ( "Y-m-d" );
	$endPeriod = date ( "Y-m-d" );
} elseif($cParams->get('module_default_period_interval', 'week') == 'week') {
	$dt = time();
	$startPeriod = date('N', $dt)==1 ? date('Y-m-d', $dt) : date('Y-m-d', strtotime('last monday', $dt));
	$endPeriod = date('N', $dt)==7 ? date('Y-m-d', $dt) : date('Y-m-d', strtotime('next sunday', $dt));
} elseif ($cParams->get('module_default_period_interval', 'week') == 'month') {
	$startPeriod = date ( "Y-m-01", strtotime ( date ( "Y-m-d" ) ) );
	$endPeriod = date ( "Y-m-d", strtotime ( "-1 day", strtotime ( "+1 month", strtotime ( date ( "Y-m-01" ) ) ) ) );
} else {
	$startPeriod = date ( "Y-m-d" );
	$endPeriod = date ( "Y-m-d" );
}

$serverStatsModel->setState('fromPeriod', $startPeriod);
$serverStatsModel->setState('toPeriod', $endPeriod); 

$statsData = ModJRealtimeAnalyticsHelper::getData($cParams, $serverStatsModel);

$layout = $params->get ( 'layout', 'default' );

// Add stylesheet
$doc = JFactory::getDocument();
$doc->addStyleSheet(JUri::base(true) . '/modules/mod_jrealtimeanalytics/assets/style.css');

if($cParams->get('visualmap_stats', 0)) {
	$doc->addStylesheet ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/libraries/jqvmap/jqvmap.css' );
	$doc->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/libraries/jqvmap/jquery.vmap.js' );
	$doc->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/libraries/jqvmap/jquery.vmap.world.js' );
	$doc->addScriptDeclaration (
		'var jrealtimeGeoMapDataModule = ' . json_encode ( $statsData['geomap_data'] ) . ';
		jQuery(function($){
			$("div.module-mapcharts").vectorMap({
				map : "world_en",
				color : "#DEDEDE",
				hoverOpacity : 0.7,
				selectedColor : "#666666",
				enableZoom : true,
				showTooltip : true,
				values : jrealtimeGeoMapDataModule,
				scaleColors : [ "#B4D6E6", "#4B97BA" ],
				normalizeFunction : "polynomial",
				onLabelShow : function(event, label, name, value) {
					label.text(name + ": " + value)
				}
			});
		});
	');
}

require JModuleHelper::getLayoutPath ( 'mod_jrealtimeanalytics', $layout );