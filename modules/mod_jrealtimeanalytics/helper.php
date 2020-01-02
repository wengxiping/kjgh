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

/**
 * Get data from main component model to show inside a module
 *
 * @package JREALTIMEANALYTICS::modules
 * @subpackage mod_jrealtimeanalytics
 * @since 2.1
 */
class ModJRealtimeAnalyticsHelper {
	/**
	 * Retrieve counter informations about the stats for the day
	 * 
	 * @param Object $params
	 * @param Object $componentModel
	 * @return mixed
	 */
	public static function getData($params, $componentModel) {
		// Get data from component model
		$totalVisitedPage = $componentModel->getDataCounters();
		
		return $totalVisitedPage;
	}
}
