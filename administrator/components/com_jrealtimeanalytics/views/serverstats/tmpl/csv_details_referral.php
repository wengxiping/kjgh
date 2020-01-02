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
		$reportStartDelimiter . JText::sprintf ( 'COM_JREALTIME_SERVERSTATS_REFERRAL_DETAILS', $this->app->input->getString('identifier') ) . $reportDelimiter 
), $delimiter, $enclosure );
echo $singleRow;

$headers = array (
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_IPADDRESS' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_GEOLOCATION_STATS' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_USERS_DETAILS_LASTVISIT' ) . $titlesDelimiter
);
fputcsv ( $outputStream, $headers, $delimiter, $enclosure );

$totalTime = 0;
$totalAverageTime = 0;
$counter = 0;
foreach ( $this->detailData as $userDetail ) :
	$values = array (
			$userDetail->ip . " ",
			($userDetail->geolocation ? $userDetail->geolocation : JText::_('COM_JREALTIME_NOTSET')),
			$userDetail->record_date
	);
	fputcsv ( $outputStream, $values, $delimiter, $enclosure );
	$counter ++;
	$totalTime += $userDetail->impulse * $this->daemonRefresh;
	$totalAverageTime = $totalTime / $counter;
endforeach;
echo $doubleRow;