<?php
/**
 * @package		Mosets Tree
 * @copyright	(C) 2005-2009 Mosets Consulting. All rights reserved.
 * @license		GNU General Public License
 * @author		Lee Cher Yeong <mtree@mosets.com>
 * @url			http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

use \Mosets\owner;

class modMTStatsHelper {
	
	public static function getTotalListings() {
		$db 		= JFactory::getDBO();
		$jdate 		= JFactory::getDate();
		$now 		= $jdate->toSql();
		$nullDate	= $db->getNullDate();

		$db->setQuery( 'SELECT COUNT(*) FROM #__mt_links '
				. "\n WHERE link_published='1' && link_approved='1' "
				. "\n AND ( publish_up = ".$db->Quote($nullDate)." OR publish_up <= '$now'  ) "
				. "\n AND ( publish_down = ".$db->Quote($nullDate)." OR publish_down >= '$now' ) " );
		return $db->loadResult();
	}

    public static function getTotalCategories() {
		$db = JFactory::getDBO();
		$db->setQuery( 'SELECT COUNT(*) FROM #__mt_cats WHERE cat_published=1 && cat_approved=1' );
		return $db->loadResult();
	}
	
    public static function getTotalOwners() {

	    require_once JPATH_ROOT. '/components/com_mtree/controllers/owner.php';

	    $owner = new owner;
	    return $owner->getTotalListingOwners();
	}

}