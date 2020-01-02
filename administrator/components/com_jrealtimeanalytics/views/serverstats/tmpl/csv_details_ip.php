<?php
/** 
 * @package JREALTIMEANALYTICS::SERVERSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage serverstats
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

// CSV format
$delimiter = ';';
$enclosure = '"';
$singleRow = PHP_EOL;
$doubleRow = PHP_EOL . PHP_EOL;
$reportStartDelimiter = '__';
$reportDelimiter = '_________________';
$titlesDelimiter = '-';

// Open out stream
$outputStream = fopen ( "php://output", "w" );

// STATS DETAILS
fputcsv ( $outputStream, array (
		$reportStartDelimiter . JText::sprintf ( 'COM_JREALTIME_SERVERSTATS_IP_DETAILS', $this->app->input->get('identifier') ) . $reportDelimiter 
), $delimiter, $enclosure );
echo $singleRow;

$headers = array (
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_NAME' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_USERS_DETAILS_VISITEDPAGE' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_VISIT_LIFE' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_USERS_DETAILS_LASTVISIT' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_GEOLOCATION_STATS' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_BROWSERNAME' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_EVENTTITLE_OS_STATS' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_DEVICE' ) . $titlesDelimiter
);
fputcsv ( $outputStream, $headers, $delimiter, $enclosure );

$totalTime = 0;
$totalAverageTime = 0;
$counter = 0;
foreach ( $this->detailData as $userDetail ) :
	$values = array (
			$userDetail->customer_name,
			$userDetail->visitedpage,
			gmdate ( 'H:i:s', $userDetail->impulse * $this->daemonRefresh ),
			date ( 'Y-m-d H:i:s', $userDetail->visit_timestamp ),
			$userDetail->geolocation,
			$userDetail->browser,
			$userDetail->os,
			$userDetail->device
	);
	fputcsv ( $outputStream, $values, $delimiter, $enclosure );
	$counter ++;
	$totalTime += $userDetail->impulse * $this->daemonRefresh;
	$totalAverageTime = $totalTime / $counter;
endforeach;
echo $doubleRow;

fputcsv ( $outputStream, array (
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_PAGES_DETAILS_TOTALDURATION' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_PAGES_DETAILS_AVERAGEPAGE_DURATION' ) . $titlesDelimiter 
), $delimiter, $enclosure );

fputcsv ( $outputStream, array (
		gmdate ( 'H:i:s', $totalTime ),
		gmdate ( 'H:i:s', $totalAverageTime ) 
), $delimiter, $enclosure );