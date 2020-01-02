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

// START FORMATTING ZONES based on CSV structure

// STATS DETAILS
fputcsv ( $outputStream, array (
		$reportStartDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_DETAILS' ) . $reportDelimiter 
), $delimiter, $enclosure );
echo $singleRow;

fputcsv ( $outputStream, array (
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_TOTAL_VISITED_PAGES' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_TOTAL_VISITORS' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_TOTAL_UNIQUE_VISITORS' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_MEDIUM_VISIT_TIME' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_MEDIUM_VISITED_PAGES_PERUSER' ) . $titlesDelimiter,
		$titlesDelimiter . JText::_ ( 'COM_JREALTIME_BOUNCE_RATE' ) . $titlesDelimiter
), $delimiter, $enclosure );

fputcsv ( $outputStream, array (
		$this->data [TOTALVISITEDPAGES],
		$this->data [TOTALVISITORS],
		$this->data [TOTALUNIQUEVISITORS],
		$this->data [MEDIUMVISITTIME],
		$this->data [MEDIUMVISITEDPAGESPERSINGLEUSER],
		$this->data [BOUNCERATE]
), $delimiter, $enclosure );
// Intra reports spacer
echo $doubleRow;

// GEOLOCATED STATS
if (isset ( $this->data [NUMUSERSGEOGROUPED] ['serverside'] ) && count ( $this->data [NUMUSERSGEOGROUPED] ['serverside'] )) {
	fputcsv ( $outputStream, array (
			$reportStartDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_GEOLOCATION_AWARE' ) . $reportDelimiter 
	), $delimiter, $enclosure );
	echo $singleRow;
	
	foreach ( $this->data [NUMUSERSGEOGROUPED] ['serverside'] as $geoData ) {
		$countries = array ();
		$countries [] = isset ( $this->geotrans [$geoData [1]] ['name'] ) ? $this->geotrans [$geoData [1]] ['name'] : JText::_ ( 'COM_JREALTIME_NOTSET' );
		$countries [] = $geoData [0];
		// Format date time
		fputcsv ( $outputStream, $countries, $delimiter, $enclosure );
	}
	// Intra reports spacer
	echo $doubleRow;
}

// OS STATS
if (isset ( $this->data [NUMUSERSOSGROUPED] ) && count ( $this->data [NUMUSERSOSGROUPED] )) {
	fputcsv ( $outputStream, array (
			$reportStartDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_OS' ) . $reportDelimiter 
	), $delimiter, $enclosure );
	echo $singleRow;
	
	foreach ( $this->data [NUMUSERSOSGROUPED] as $os ) {
		$os = array_reverse ( $os );
		// Output rows
		fputcsv ( $outputStream, $os, $delimiter, $enclosure );
	}
	// Intra reports spacer
	echo $doubleRow;
}

// BROWSER STATS
if (isset ( $this->data [NUMUSERSBROWSERGROUPED] ) && count ( $this->data [NUMUSERSBROWSERGROUPED] )) {
	fputcsv ( $outputStream, array (
			$reportStartDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_BROWSER' ) . $reportDelimiter 
	), $delimiter, $enclosure );
	echo $singleRow;
	
	foreach ( $this->data [NUMUSERSBROWSERGROUPED] as $browser ) {
		$browser = array_reverse ( $browser );
		// Output rows
		fputcsv ( $outputStream, $browser, $delimiter, $enclosure );
	}
	// Intra reports spacer
	echo $doubleRow;
}

// DEVICE STATS
if (isset ( $this->data [NUMUSERSDEVICEGROUPED] ) && count ( $this->data [NUMUSERSDEVICEGROUPED] )) {
	fputcsv ( $outputStream, array (
			$reportStartDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_DEVICE' ) . $reportDelimiter
	), $delimiter, $enclosure );
	echo $singleRow;

	foreach ( $this->data [NUMUSERSDEVICEGROUPED] as $device ) {
		$device = array_reverse ( $device );
		// Output rows
		fputcsv ( $outputStream, $device, $delimiter, $enclosure );
	}
	// Intra reports spacer
	echo $doubleRow;
}

// LANDING PAGES
if (isset ( $this->data [LANDING_PAGES] ) && count ( $this->data [LANDING_PAGES] )) {
	fputcsv ( $outputStream, array (
			$reportStartDelimiter . JText::_ ( 'COM_JREALTIME_LANDING_PAGES' ) . $reportDelimiter 
	), $delimiter, $enclosure );
	echo $singleRow;
	
	fputcsv ( $outputStream, array (
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_PAGE' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_NUMUSERS' ) . $titlesDelimiter 
	), $delimiter, $enclosure );
	foreach ( $this->data [LANDING_PAGES] as $page ) {
		// Reverse array
		$page = array_reverse ( $page );
		fputcsv ( $outputStream, $page, $delimiter, $enclosure );
	}
	// Intra reports spacer
	echo $doubleRow;
}

// LEAVEOFF PAGES
if (isset ( $this->data [LEAVEOFF_PAGES] ) && count ( $this->data [LEAVEOFF_PAGES] )) {
	fputcsv ( $outputStream, array (
			$reportStartDelimiter . JText::_ ( 'COM_JREALTIME_LEAVEOFF_PAGES' ) . $reportDelimiter 
	), $delimiter, $enclosure );
	echo $singleRow;
	
	fputcsv ( $outputStream, array (
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_PAGE' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_NUMUSERS' ) . $titlesDelimiter 
	), $delimiter, $enclosure );
	foreach ( $this->data [LEAVEOFF_PAGES] as $page ) {
		// Reverse array
		$page = array_reverse ( $page );
		fputcsv ( $outputStream, $page, $delimiter, $enclosure );
	}
	// Intra reports spacer
	echo $doubleRow;
}

// VISITS BY PAGE
if (isset ( $this->data [VISITSPERPAGE] ) && count ( $this->data [VISITSPERPAGE] )) {
	fputcsv ( $outputStream, array (
			$reportStartDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_PAGES' ) . $reportDelimiter 
	), $delimiter, $enclosure );
	echo $singleRow;
	
	fputcsv ( $outputStream, array (
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_PAGE' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_LASTVISIT' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_NUMVISITS' ) . $titlesDelimiter 
	), $delimiter, $enclosure );
	foreach ( $this->data [VISITSPERPAGE] as $visitedPage ) {
		// Reverse array
		$visitedPage = array_reverse ( $visitedPage );
		// Format date time
		$visitedPage [1] = date ( 'Y-m-d H:i:s', $visitedPage [1] );
		fputcsv ( $outputStream, $visitedPage, $delimiter, $enclosure );
	}
	// Intra reports spacer
	echo $doubleRow;
}

// VISITS BY USER
if (isset ( $this->data [TOTALVISITEDPAGESPERUSER] ) && count ( $this->data [TOTALVISITEDPAGESPERUSER] )) {
	fputcsv ( $outputStream, array (
			$reportStartDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_USERS' ) . $reportDelimiter 
	), $delimiter, $enclosure );
	echo $singleRow;
	
	$anonymizeIP = $this->cparams->get('anonymize_ipaddress', 0);
	$showUserGroup = $this->cparams->get('show_usergroup', 0);
	$showReferral = $this->cparams->get('show_referral', 0);
	
	$userPagesFieldTitles = array (
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_NAME' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_LASTVISIT' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_VISIT_LIFE' ) . $titlesDelimiter,
			$titlesDelimiter . 'Browser' . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_OS_TITLE' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_DEVICE' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_IPADDRESS' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_('COM_JREALTIME_SERVERSTATS_GEOLOCATION') . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_VISITED_PAGES' ) . $titlesDelimiter
	);
	
	// Inject additional title if show usergroup
	if($showUserGroup) {
		array_splice($userPagesFieldTitles, 1, 0, $titlesDelimiter . JText::_('COM_JREALTIME_TITLETYPE') . $titlesDelimiter);
	}
	
	// Inject additional title if show referral
	if($showReferral) {
		array_splice($userPagesFieldTitles, -1, 0, $titlesDelimiter . JText::_('COM_JREALTIME_SERVERSTATS_REFERRAL_LABEL') . $titlesDelimiter);
	}
	
	fputcsv ( $outputStream, $userPagesFieldTitles, $delimiter, $enclosure );
	
	foreach ( $this->data [TOTALVISITEDPAGESPERUSER] as $userPage ) {
		if($anonymizeIP) {
			$userPage[6] = '-';
		}
		// Reverse array
		// Format date time
		$userPagesNormalized = array (
				$userPage [1],
				date ( 'Y-m-d H:i:s', $userPage [2] ),
				gmdate ( 'H:i:s', $userPage [7] * $this->cparams->get ( 'daemonrefresh' ) ),
				$userPage [3],
				$userPage [4],
				$userPage [9],
				$userPage [6] . " ",
				$userPage [8],
				$userPage [0] 
		);
		
		// Inject additional value if show usergroup
		if($showUserGroup) {
			$userGroupIndex = $showReferral ? 11 : 10;
			array_splice($userPagesNormalized, 1, 0, ($userPage[$userGroupIndex] ? $userPage[$userGroupIndex] : JText::_('COM_JREALTIME_NA')));
		}
		
		// Inject additional title if show referral
		if($showReferral) {
			array_splice($userPagesNormalized, -1, 0, ($userPage[10] ? $userPage[10] : JText::_('COM_JREALTIME_SERVERSTATS_REFERRAL_LABEL_DIRECT')));
		}
		
		fputcsv ( $outputStream, $userPagesNormalized, $delimiter, $enclosure );
	}
	// Intra reports spacer
	echo $doubleRow;
}

// VISITS BY IP
if (isset ( $this->data [TOTALVISITEDPAGESPERIPADDRESS] ) && count ( $this->data [TOTALVISITEDPAGESPERIPADDRESS] )) {
	fputcsv ( $outputStream, array (
			$reportStartDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_VISITSBY_IPADDRESS' ) . $reportDelimiter 
	), $delimiter, $enclosure );
	echo $singleRow;
	
	fputcsv ( $outputStream, array (
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_IPADDRESS' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_EVENTTITLE_GEOLOCATION' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_LASTVISIT' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_VISIT_LIFE' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_VISITED_PAGES' ) . $titlesDelimiter
	), $delimiter, $enclosure );
	foreach ( $this->data [TOTALVISITEDPAGESPERIPADDRESS] as $iprecord ) {
		// Format date time
		$userPagesNormalized = array (
				$iprecord [2] . " ",
				$iprecord [4],
				date ( 'Y-m-d H:i:s', $iprecord [1] ),
				gmdate ( 'H:i:s', $iprecord [3] * $this->cparams->get ( 'daemonrefresh' ) ),
				$iprecord [0] 
		);
		fputcsv ( $outputStream, $userPagesNormalized, $delimiter, $enclosure );
	}
	// Intra reports spacer
	echo $doubleRow;
}

// REFERRAL STATS
if (isset ( $this->data [REFERRALTRAFFIC] ) && count ( $this->data [REFERRALTRAFFIC] )) {
	fputcsv ( $outputStream, array (
			$reportStartDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_REFERRAL' ) . $reportDelimiter 
	), $delimiter, $enclosure );
	echo $singleRow;
	
	fputcsv ( $outputStream, array (
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_REFERRAL_LINK' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_IPADDRESS' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_REFERRAL_COUNTER' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_REFERRAL_PERCENTAGE' ) . $titlesDelimiter 
	), $delimiter, $enclosure );
	
	$anonymizeIP = $this->cparams->get('anonymize_ipaddress', 0);
	foreach ( $this->data [REFERRALTRAFFIC] as $referral ) {
		if($anonymizeIP) {
			$ipInfo = ' - ';
		} else {
			$ipInfo = ($referral[2] == 1 ? $referral[1] : $referral[2] .  ' ' . JText::_('COM_JREALTIME_SERVERSTATS_MULTIPLE_IP')) . " ";
		}
		// Format date time
		$referrals = array (
				$referral [0],
				$ipInfo,
				$referral [3],
				(sprintf ( '%.2f', ($referral [3] / $referral [4]) * 100 ) . '%') 
		);
		fputcsv ( $outputStream, $referrals, $delimiter, $enclosure );
	}
	// Intra reports spacer
	echo $doubleRow;
}

// SEARCHED KEYWORDS
if (isset ( $this->data [SEARCHEDPHRASE] ) && count ( $this->data [SEARCHEDPHRASE] )) {
	fputcsv ( $outputStream, array (
			$reportStartDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_SEARCHES' ) . $reportDelimiter 
	), $delimiter, $enclosure );
	echo $singleRow;
	
	fputcsv ( $outputStream, array (
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_SEARCHES_PHRASE' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_SEARCHES_COUNTER' ) . $titlesDelimiter,
			$titlesDelimiter . JText::_ ( 'COM_JREALTIME_SERVERSTATS_SEARCHES_PERCENTAGE' ) . $titlesDelimiter 
	), $delimiter, $enclosure );
	foreach ( $this->data [SEARCHEDPHRASE] as $phrase ) {
		// Format date time
		$phrases = array (
				$phrase [0],
				$phrase [1],
				(sprintf ( '%.2f', ($phrase [1] / $phrase [2]) * 100 ) . '%') 
		);
		fputcsv ( $outputStream, $phrases, $delimiter, $enclosure );
	}
}

fclose ( $outputStream );