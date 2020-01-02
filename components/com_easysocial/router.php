<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php');

/**
 * Responsible to build urls into SEF urls
 *
 * @since   1.0
 * @access  public
 */
function EasySocialBuildRoute(&$query)
{
	// check if we want to this query allow to use sef caching or not.
	if (isset($query['view']) && $query['view'] && !ESR::isViewSefCacheAllow($query['view'])) {
		$segments = FRoute::build($query);
		return $segments;
	}

	$debug = JFactory::getApplication()->input->get('debug', false, 'bool');

	$oriQuery = $query;
	$dbSegment = ESR::getDbSegments($oriQuery, $debug);

	if ($dbSegment === false) {
		$segments = FRoute::build($query);
		ESR::encode($segments);
		ESR::setDbSegments($oriQuery, $segments, $query, $debug);

		return $segments;

	}

	$segments = $dbSegment->segments;

	// now we need to remove the extra query that are already process.
	parse_str($dbSegment->rawurl, $rawQuery);
	foreach ($rawQuery as $key => $val) {
		unset($query[$key]);
	}

	return $segments;
}

/**
 * Responsible to rewrite urls from SEF into proper query strings.
 *
 * @since   1.0
 * @access  public
 */
function EasySocialParseRoute($segments)
{
	$debug = JFactory::getApplication()->input->get('debug', false, 'bool');

	// If there is only 1 segment and the segment is index.php, it's just submitting
	if (count($segments) == 1 && $segments[0] == 'index.php') {
		return array();
	}

	// lets format the segment so that the 1st index will be the view.
	$test = ESR::format($segments);

	// check if we should retrieve from caching or not.
	if (!ESR::isViewSefCacheAllow($test[0])) {
		$query  = FRoute::parse($segments);
		return $query;
	}


	$tmp = $segments;
	$query = ESR::getDbVars($tmp, $debug);

	if ($query === false) {
		// if false, we try to fall back to normal parse rules.
		$query  = FRoute::parse($segments);
	}

	return $query;
}
