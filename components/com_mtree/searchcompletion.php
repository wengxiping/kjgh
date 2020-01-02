<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2015-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class MtreeSearchCompletion
{
	var $_db = null;

	public function __construct(JDatabaseDriver $database)
	{
		$this->_db = $database;
	}

	public function forQuery($query, $type='listing', $cat_id=0)
	{
		$cache = JFactory::getCache('com_mtree-searchcompletion');

		return $cache->call(array($this, 'getResultsJson'), $query, $type, $cat_id);
	}

	public function getResultsJson($query, $type, $cat_id)
	{
		return json_encode($this->getResultsArray($query, $type, $cat_id));
	}

	public function getResultsArray($query, $type, $cat_id)
	{
		if( $type == 'category' )
		{
			$listings = $this->searchCategoryNameWithKeyword($query, $cat_id);
		}
		else
		{
			$listings = $this->searchListingNameWithKeyword($query, $cat_id);
			array_walk($listings, array('self','buildImageUrl') );
		}

		array_walk($listings, array('self','buildHref'), $type );

		return $listings;
	}

	protected function searchListingNameWithKeyword( $keyword, $cat_id=0 )
	{
		global $mtconf;

		$jdate		= JFactory::getDate();
		$now		= $jdate->toSql();
		$nullDate	= $this->_db->getNullDate();

		$only_subcats_sql = '';
		if ( $cat_id > 0 ) {
			$mtCats = new mtCats( $this->_db );
			$subcats = $mtCats->getSubCats_Recursive( $cat_id, true );
			$subcats[] = $cat_id;
			if ( !empty($subcats) ) {
				$only_subcats_sql = "\n AND cat.cat_id IN (" . implode( ", ", $subcats ) . ")";
			}
		}

		$this->_db->setQuery( "SELECT l.link_id, link_name, img.filename AS img_filename"
			.   "\n FROM #__mt_links AS l "
			.	"\n LEFT JOIN #__mt_images AS img ON img.link_id = l.link_id AND img.ordering = 1 "
			.	"\n LEFT JOIN #__mt_cl AS cl ON cl.link_id = l.link_id "
			.	"\n LEFT JOIN #__mt_cats AS cat ON cat.cat_id = cl.cat_id "
			.	"\n WHERE link_published='1' && link_approved='1' "
			.	"\n AND ( publish_up = ".$this->_db->Quote($nullDate)." OR publish_up <= '$now'  ) "
			.	"\n AND ( publish_down = ".$this->_db->Quote($nullDate)." OR publish_down >= '$now' ) "
			.   "\n AND link_name LIKE \"%" . $this->_db->escape($keyword) . "%\" "
			.	"\n AND cl.link_id = l.link_id "
			.	"\n AND cl.main = 1 "
			.	( (!empty($only_subcats_sql)) ? $only_subcats_sql : '' )
		.   " LIMIT " . $mtconf->get('search_completion_max_listings') );
		return $this->_db->loadObjectList();
	}

	protected function searchCategoryNameWithKeyword( $keyword, $cat_id=0 )
	{
		$only_subcats_sql = '';

		if ( $cat_id > 0 ) {
			$mtCats = new mtCats( $this->_db );
			$subcats = $mtCats->getSubCats_Recursive( $cat_id, true );
			$subcats[] = $cat_id;
			if ( !empty($subcats) ) {
				$only_subcats_sql = "\n AND cat.cat_id IN (" . implode( ", ", $subcats ) . ")";
			}
		}

		$this->_db->setQuery( "SELECT cat_id, cat_name FROM #__mt_cats AS cat"
			.	"\n WHERE "
				. "\nLOWER(cat.cat_name) LIKE '%" . $this->_db->escape($keyword) . "%' "
			.	"\n AND cat_published='1' AND cat_approved='1' "
			.	( (!empty($only_subcats_sql)) ? $only_subcats_sql : '' )
		);
		return $this->_db->loadObjectList();

	}

	function buildHref(&$item, $key, $type)
	{
		global $Itemid;

		if( $type == 'category' )
		{
			$item->href = JRoute::_( 'index.php?option=com_mtree&task=listcats&cat_id=' . $item->cat_id . '&Itemid=' . $Itemid );
		} else {
			$item->href = JRoute::_( 'index.php?option=com_mtree&task=viewlink&link_id=' . $item->link_id . '&Itemid=' . $Itemid );
		}
	}

	function buildImageUrl(&$listing)
	{
		global $mtconf;

		if( !is_null($listing->img_filename) )
		{
			$listing->image_url = ltrim($mtconf->get('relative_path_to_listing_small_image'), '/') . $listing->img_filename;
		}
		else
		{
			$listing->image_url = ltrim($mtconf->get('relative_path_to_images'), '/') . 'noimage_thb.png';
		}

	}


}