<?php
/** 
 * @package JREALTIMEANALYTICS::EVENTSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage eventstats
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
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
		$reportStartDelimiter . JText::_ ( 'COM_JREALTIME_EVENT_REPORT' ) . JText::sprintf ( 'COM_JREALTIME_EVENT_OCCURENCES', count ( $this->eventDetails ) ) . $reportDelimiter,
		$this->record->name 
), $delimiter, $enclosure );
echo $singleRow;

$headers = array (
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_EVENTTITLE_USERNAME' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_EVENTTITLE_EVENTDATE' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_EVENTTITLE_GEOLOCATION' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_EVENTTITLE_IPADDRESS' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_EVENTTITLE_BROWSER' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_EVENTTITLE_OS' ) . $titlesDelimiter 
);
fputcsv ( $outputStream, $headers, $delimiter, $enclosure );

$anonymizeIP = $this->cparams->get('anonymize_ipaddress', 0);
foreach ( $this->eventDetails as $index=>$eventDetail ) :
	if($anonymizeIP) {
		$ipInfo = ' - ';
	} else {
		$ipInfo = $eventDetail->ip ? $eventDetail->ip . " " : JText::_('COM_JREALTIME_NOTSET');
	}
	$values = array (
			$eventDetail->customer_name ? $eventDetail->customer_name : JText::_('COM_JREALTIME_NOTSET'),
			date('Y-m-d H:i:s', $eventDetail->event_timestamp),
			$eventDetail->geolocation ? $eventDetail->geolocation : JText::_('COM_JREALTIME_NOTSET'),
			$ipInfo,
			$eventDetail->browser ? $eventDetail->browser : JText::_('COM_JREALTIME_NOTSET'),
			$eventDetail->os ? $eventDetail->os : JText::_('COM_JREALTIME_NOTSET')
	);
	fputcsv ( $outputStream, $values, $delimiter, $enclosure );
endforeach;