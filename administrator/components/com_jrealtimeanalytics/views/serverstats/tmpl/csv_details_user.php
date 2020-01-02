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
		$reportStartDelimiter . JText::sprintf ( 'COM_JREALTIME_SERVERSTATS_USERS_DETAILS', $this->detailData [0]->customer_name ) . $reportDelimiter 
), $delimiter, $enclosure );
echo $singleRow;
$headers = array (
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_USERS_DETAILS_VISITEDPAGE' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_VISIT_LIFE' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_USERS_DETAILS_LASTVISIT' ) . $titlesDelimiter
);
if ($this->cparams->get ( 'xtd_singleuser_stats', 0 )) {
	$headers = array_merge ( $headers, array (
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_IPADDRESS' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_GEOLOCATION_STATS' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_BROWSERNAME' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_EVENTTITLE_OS_STATS' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_DEVICE' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_EMAIL' ) . $titlesDelimiter 
	) );
}
fputcsv ( $outputStream, $headers, $delimiter, $enclosure );

$totalTime = 0;
$totalAverageTime = 0;
$counter = 0;
$anonymizeIP = $this->cparams->get('anonymize_ipaddress', 0);
foreach ( $this->detailData as $userDetail ) :
	$values = array (
			$userDetail->visitedpage,
			gmdate ( 'H:i:s', $userDetail->impulse * $this->daemonRefresh ),
			date ( 'Y-m-d H:i:s', $userDetail->visit_timestamp ) 
	);
	if ($this->cparams->get ( 'xtd_singleuser_stats', 0 )) {
		if($anonymizeIP) {
			$userDetail->ip = ' - ';
		}
		$values = array_merge ( $values, array (
				$userDetail->ip . " ",
				$userDetail->geolocation,
				$userDetail->browser,
				$userDetail->os,
				$userDetail->device,
				$userDetail->email 
		) );
	}
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