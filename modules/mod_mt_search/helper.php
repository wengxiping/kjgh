<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2005-2011 Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class modMTSearchHelper {

	public static function getCategories( $params ) {
		$db = JFactory::getDBO();
		$tmp_mtconf = new mtConfig($db);
		
		$showCatDropdown= intval( $params->get( 'showCatDropdown', 0 ) );
		$parent_cat_id		= intval( $params->get( 'parent_cat', 0 ) );

		$tmp_mtconf->setCategory($parent_cat_id);
		
		if ( $showCatDropdown == 1 && $parent_cat_id >= 0 ) {
			$sql = 'SELECT cat_id, cat_name, cat_parent FROM #__mt_cats AS cat '
				. ' WHERE cat_approved=1 AND cat_published=1 AND cat_parent = ' . $parent_cat_id;
				
			if( $tmp_mtconf->get('first_cat_order1') != '' )
			{
				$sql .= ' ORDER BY ' . $tmp_mtconf->get('first_cat_order1') . ' ' . $tmp_mtconf->get('first_cat_order2');
				if( $tmp_mtconf->get('second_cat_order1') != '' )
				{
					$sql .= ', ' . $tmp_mtconf->get('second_cat_order1') . ' ' . $tmp_mtconf->get('second_cat_order2');
				}
			}
			
			$db->setQuery( $sql );
			$categories = $db->loadObjectList();

			// Parse it through isAuthorisedToViewCategory, so that only categories where the user is authorized to view
			// is returned. This is only checked when the module is showing top level categories.
			//
			// We are not checking against non-top level categories. If admin wants to let users to search a restricted
			// category, they should control the access directly through the module.
			$authorised_categories = array();

			if( $parent_cat_id != 0 ) {
				return $categories;
			}

			foreach ($categories AS $cat )
			{
				if( isAuthorisedToViewCategory($cat->cat_id) ) {
					$authorised_categories[] = $cat;
				}
			}

			return $authorised_categories;
		} else {
			return null;
		}
	}
}