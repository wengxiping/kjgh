<?php
/**
 * @package		Mosets Tree
 * @copyright	(C) 2005-2009 Mosets Consulting. All rights reserved.
 * @license		GNU General Public License
 * @author		Lee Cher Yeong <mtree@mosets.com>
 * @url			http://www.mosets.com/tree/
 */


defined('_JEXEC') or die('Restricted access');

class mAdvancedSearch {
	
	var $_db = null;
	var $conditions = null;
	var $totalResults = null;
	var $arrayLinkId = array();
	var $cfvCounter = 0;
	var $hasKeywordSearch = false;
	var $avlDateFrom = null;
	var $avlDateTo = null;
	var $hasAvlSearch = false;
	var $hasSimpleSearchableCustomFields = false;
	var $searchKeyword = null;

	var $where = array();
	var $join = array();
	var $having = array();
	var $limitToCategory = array();
	var $operator = null;
	var $sort = null;
	var $sql_order_by=null;
	var $allowed_sort = array('link_featured','link_created','link_modified','link_hits','link_visited','link_rating','link_votes','link_name','price','year');
	
	function __construct( $database ) {
		$this->_db = $database;
	}

	function addCondition( $field, $searchFieldValues ) {
		$where = call_user_func_array(array($field, 'getWhereCondition'),$searchFieldValues);
		$this->where[] = str_replace('cfv#.', 'cfv' . $this->cfvCounter . '.', $where);
		if(!$field->isCore()) {
			$this->join[] = 'LEFT JOIN #__mt_cfvalues AS cfv' . $this->cfvCounter . ' ON l.link_id = cfv' . $this->cfvCounter . '.link_id AND cfv' . $this->cfvCounter . '.cf_id = ' . $field->getId();
			$this->cfvCounter++;
		}
	}

	/**
	 * Accepts a keyword that will be searched against all simple searchable fields.
	 *
	 * @param $keyword String Search keyword
	 */
	function addKeywordSearch( $keyword )
	{
		$this->hasKeywordSearch = true;
		$this->searchKeyword = $keyword;

		$this->checkHasSimpleSearchableCustomFields();
	}

	/**
	 * Accepts 2 dates that will search for available listings
	 *
	 * @param $date_from Start date to search
	 * @param $date_to End date to search
	 */
	function addAvlSearch( $date_from, $date_to )
	{
		$this->hasAvlSearch = true;

		$this->avlDateTo= date('Y-m-d', strtotime($date_to));
		$this->avlDateFrom = date('Y-m-d', strtotime($date_from));
	}

	/**
	 * Runs an SQL query to determine if there are simple searchable custom fields.
	 */
	function checkHasSimpleSearchableCustomFields()
	{
		$this->_db->setQuery("SELECT COUNT(*) FROM #__mt_customfields WHERE published = 1 AND simple_search = 1 AND iscore = 0");
		$searchable_custom_fields_count = $this->_db->loadResult();

		if( $searchable_custom_fields_count > 0 ) {
			$this->hasSimpleSearchableCustomFields = true;
		} else {
			$this->hasSimpleSearchableCustomFields = false;
		}

	}

	/**
	 * Returns the WHERE clause for searching simple searchable fields. This will be used in the main search query.
	 *
	 * @return bool|string
	 */
	function getKeywordSearchWhereClause()
	{
		if($this->hasKeywordSearch)
		{
			$this->_db->setQuery("SELECT field_type,published,simple_search FROM #__mt_customfields WHERE iscore = 1");
			$searchable_core_fields = $this->_db->loadObjectList('field_type');

			$link_fields = array('link_name', 'link_desc', 'firstname', 'lastname', 'address', 'city', 'postcode', 'state', 'country', 'email', 'website', 'contactperson', 'mobile', 'date', 'year', 'telephone', 'fax', 'metakey', 'metadesc', 'price' );

			$words = parse_words($this->searchKeyword);

			foreach($words AS $key => $value) {
				$words[$key] = $this->_db->escape( $value, true );
			}

			$wheres0 = array();

			foreach ($words as $word)
			{
				foreach( $link_fields AS $lf ) {
					if (
							(substr($lf, 0, 5) == "link_" && array_key_exists('core'.substr($lf,5),$searchable_core_fields) && $searchable_core_fields['core'.substr($lf,5)]->published == 1 && $searchable_core_fields['core'.substr($lf,5)]->simple_search == 1)
							OR
							(array_key_exists('core'.$lf,$searchable_core_fields) && $searchable_core_fields['core'.$lf]->published == 1 && $searchable_core_fields['core'.$lf]->simple_search == 1)
					) {
						if(in_array($lf,array('metakey','metadesc','email'))) {
							$wheres0[] = "\n LOWER(l.$lf) LIKE '%$word%'";
						} else {
							$wheres0[] = "\n LOWER($lf) LIKE '%$word%'";
						}
					}
				}
				if( $this->hasSimpleSearchableCustomFields ) {
					$wheres0[] = "\n" .' (cf.simple_search = 1 AND cf.published = 1 AND LOWER(cfv.value) LIKE \'%' . $word . '%\')';
				}
			}

			$sql = "\n (" . implode( ' OR ', $wheres0 ) . ")";

			return $sql;

		}

		return false;
	}

	/**
	 * Returns the WHERE clause for searching availability. This will be used in the main search query.
	 *
	 * @return bool|string
	 */
	function getAvlSearchWhereClause()
	{
		if( $this->hasAvlSearch )
		{
			$sql = '';

			if( !empty($this->avlDateFrom) && !empty($this->avlDateTo) )
			{
				$sql = "( ";
				$sql .= "avl.date >= '" . $this->avlDateFrom . "' ";
				$sql .= "AND ";
				$sql .= "avl.date < '" . $this->avlDateTo . "' ";
				$sql .= "AND ";
				$sql .= "avl.status = 0 ";
				$sql .= " )";
			}
			else if( !empty($this->avlDateFrom) && empty($this->avlDateTo) )
			{
				$sql = "( ";
				$sql .= "avl.date >= '" . $this->avlDateFrom . "' ";
				$sql .= "AND ";
				$sql .= "avl.status = 0 ";
				$sql .= " )";
			}
			else if( empty($this->avlDateFrom) && !empty($this->avlDateTo) )
			{
				$sql = "( ";
				$sql .= "avl.date < '" . $this->avlDateTo . "' ";
				$sql .= "AND ";
				$sql .= "avl.status = 0 ";
				$sql .= " )";
			}

			if( !empty($sql) ) {
				return $sql;
			}

		}

		return false;
	}

	/***
	 * A HAVING clause used when doing an availability search. This make sure that the number of nights that are needed
	 * are matched by the same number of nights available from a particular listing.
	 *
	 * @return bool|string
	 */
	function getAvlSearchHavingClause()
	{
		if( $this->hasAvlSearch )
		{
			if( !empty($this->avlDateFrom) && !empty($this->avlDateTo) )
			{
				return "COUNT(avl.status) = DATEDIFF('" . $this->avlDateTo . "', '" . $this->avlDateFrom . "')";
			}
		}

		return false;
	}

	function addRawCondition( $condition ) {
		$this->where[] = $condition;
	}
	
	function addHavingCondition( $condition ) {
		$this->having[] = $condition;
	}
	
	function limitToCategory( $cat_ids ) {
		$this->limitToCategory = $cat_ids;
	}
	
	function useOrOperator() {
		$this->operator = 'OR';
	}
	
	function useAndOperator() {
		$this->operator = 'AND';
	}
	
	function getOperator() {
		if( $this->operator == 'OR' || $this->operator == 'AND' ) {
			return $this->operator;
		} else {
			return 'AND';
		}
	}
	
	function search($published=null,$approved=null) {

		if(count($this->where) > 0 || count($this->having) > 0 || count($this->limitToCategory) > 0 || $this->hasKeywordSearch || $this->hasAvlSearch) {

			$sql = 'SELECT DISTINCT l.link_id FROM (#__mt_links AS l';

			if( $this->hasSimpleSearchableCustomFields )
			{
				$sql .= ', #__mt_customfields AS cf';
			}
			$sql .= ')';

			if( count($this->join) > 0 ) {
				$sql .= "\n ";
				$sql .= implode( "\n ", $this->join );
			}
			$sql .= "\n LEFT JOIN #__mt_cl AS cl ON cl.link_id = l.link_id";

			if( $this->hasSimpleSearchableCustomFields )
			{
				$sql .= "\n LEFT JOIN #__mt_cfvalues AS cfv ON cfv.link_id = l.link_id AND cfv.cf_id = cf.cf_id";
			}

			if( $this->hasAvlSearch )
			{
				$sql .= "\n LEFT JOIN #__mt_avl AS avl ON avl.link_id = l.link_id";
			}

			if( count($this->where) > 0 || count($this->limitToCategory) > 0 || $this->hasKeywordSearch || $this->hasAvlSearch ) {
				$sql .= "\n WHERE ";
			}

			if( $this->hasKeywordSearch ) {
				$sql .= $this->getKeywordSearchWhereClause();
				$sql .= ' ' . $this->getOperator() . ' ';
			}

			if( $this->hasAvlSearch ) {
				$sql .= $this->getAvlSearchWhereClause();
				$sql .= ' ' . $this->getOperator() . ' ';

				if( $this->getAvlSearchHavingClause() )
				{
					$this->having[] = $this->getAvlSearchHavingClause();
				}
			}

			if( count($this->where) > 0 ) {
				$sql .= '(' . implode( ' ' . $this->getOperator() . ' ', $this->where ) . ')';
			}

			if( $published ) {
				$jdate 		= JFactory::getDate();
				$now 		= $jdate->toSql();
				$database 	= JFactory::getDBO();
				$nullDate	= $database->getNullDate();

				if( count($this->where) > 0 ) {
					$sql .= "\nAND ";
				}
				$sql .= "(publish_up = ".$database->Quote($nullDate)." OR publish_up <= '$now')  AND "
				 	. "(publish_down = ".$database->Quote($nullDate)." OR publish_down >= '$now') AND "
					. "link_published = '1'";
			}
			if( $approved ) {
				$sql .= "\nAND link_approved = '1'";
			}
			if( is_array($this->limitToCategory) && count($this->limitToCategory) > 0 ) {
				$sql .= "\nAND cl.cat_id IN (" . implode( ',', $this->limitToCategory ) . ")";
			}
			$sql .=	"\nGROUP BY l.link_id";

			if( is_array($this->having) && count($this->having) > 0 ) {
				$sql .= "\nHAVING ";
				$sql .= implode( ' OR ', $this->having );
			}

			$this->_db->setQuery( $sql );
//			 echo '<p />SQL: ' . $sql;
			$this->arrayLinkId = $this->_db->loadColumn();
			$this->totalResults = count($this->arrayLinkId);
			
			if( $this->_db->getErrorMsg() == '' ) {
				return true;
			} else {
				return false;
			}
			
		} else {
			return true;
		}
	}
	
	function loadResultList( $limitstart=0, $limit=15) {
		global $mtconf;
		
		if( count($this->arrayLinkId) > 0 ) {
			$sql = "SELECT l.*, "
			.   "tlcat.cat_id AS tlcat_id, tlcat.cat_name AS tlcat_name, cl.*, cat.*, u.username AS username, u.name AS owner, "
			.   "GROUP_CONCAT(DISTINCT img.filename ORDER BY img.ordering ASC SEPARATOR ',') AS images "
			.   "\n FROM #__mt_links AS l"
			.	"\n LEFT JOIN #__mt_images AS img ON img.link_id = l.link_id "
			.	"\n LEFT JOIN #__mt_cl AS cl ON cl.link_id = l.link_id AND cl.main = 1"
			.	"\n LEFT JOIN #__users AS u ON u.id = l.user_id "
			.	"\n LEFT JOIN #__mt_cats AS cat ON cl.cat_id = cat.cat_id"
			.	"\n LEFT JOIN #__mt_cats AS tlcat ON tlcat.lft <= cat.lft AND tlcat.rgt >= cat.rgt AND tlcat.cat_parent =0 "
			.	"\nWHERE l.link_id IN (" . implode(",", $this->arrayLinkId) . ")";
			$sql .= "\n GROUP BY l.link_id";

			if( $this->getOrderBy() != '' ) {
				$sql .= "\n ORDER BY " . $this->getOrderBy();
			} else {

				$listings = new Mosets\listings($mtconf);
				$sql .= $listings->getSQLOrderBy();

			}

			if( $limitstart >= 0 && $limit > 0 )
			{
				$sql .= "\nLIMIT $limitstart, $limit";
			}
			$this->_db->setQuery( $sql );
			return $this->_db->loadObjectList();
		} else {
			return null;
		}
	}
	
	function getTotal() {
		if( !is_null($this->arrayLinkId) ) {
			return count($this->arrayLinkId);
		} else {
			return 0;
		}
	}
	
	function setSort( $sort ) {
		$this->sort = $sort;
		if( substr($sort,0,1) == '-' ) {
			$order_by_part_2 = 'DESC';
		} else {
			$order_by_part_2 = 'ASC';
		}
		$order_by_part_1 = str_replace(array('-','+'),'',$sort);

		if( in_array($order_by_part_1,$this->allowed_sort) ) {
			$this->sql_order_by = $order_by_part_1 . ' ' . $order_by_part_2;
		}
	}
	
	function getOrderBy() {
		if( !is_null($this->sql_order_by) ) {
			return $this->sql_order_by;
		} else {
			return '';
		}
	}
}