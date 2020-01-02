<?php
namespace Mosets;

defined('_JEXEC') or die;

use \JFactory;
use \JRoute;
use \MText;

/**
 * Support all, except when listings are set to order by Featured/Random.
 *
 * @package Mosets
 */
class adjacentListing
{
	protected $db = null;

	protected $nowDate = null;

	protected $nullDate = null;

	protected $direction = null;

	function __construct()
	{
		$this->db = JFactory::getDBO();
		$this->nowDate	= JFactory::getDate()->toSql();
		$this->nullDate	= $this->db->getNullDate();
	}

	public function index()
	{
		$this->direction = JFactory::getApplication()->input->getInt('direction', 1);
		$link_id = JFactory::getApplication()->input->getInt('link_id', 0);

		if( $this->direction != 1 ) {
			$this->direction = -1;
		}

		$adjacent_link_id = self::getAdjacentListingID($link_id, $this->direction);

		if( $adjacent_link_id === false )
		{
			// The listing has no adjacent listings. Redirect to category page.

			$listing = self::getListing($link_id);

			if( $this->direction == 1 ) {
				JFactory::getApplication('site')->enqueueMessage( MText::_('NO_NEXT_ADJACENT_LISTING_MSG', $listing->tlcat_id ) );
			} else {
				JFactory::getApplication('site')->enqueueMessage( MText::_('NO_PREVIOUS_ADJACENT_LISTING_MSG', $listing->tlcat_id ) );
			}

			JFactory::getApplication('site')->redirect(JRoute::_('index.php?option=com_mtree&task=listcats&cat_id=' . $listing->cat_id, false));
		}
		else
		{
			// This listing has adjacent listing
			self::redirectToListing($adjacent_link_id);
		}

	}

	public function redirectToListing($link_id)
	{
		global $Itemid;

		JFactory::getApplication('site')->redirect(JRoute::_('index.php?option=com_mtree&task=viewlink&link_id=' . $link_id . '&Itemid=' . $Itemid, false));
	}
	/**
	 * Get the adjacent listing based on the current listing specified in argument.
	 *
	 * @param $link_id Int ID
	 *
	 * @param $direction
	 *
	 * @return string
	 */
	public function getAdjacentListing( $link_id, $direction )
	{
		$listing = self::getListing( $link_id );

		if(!$listing) {
			return false;
		}

		$adjacent_link_id = self::getAdjacentListingID( $link_id, $direction );
		$adjacent_listing = self::getListing( $adjacent_link_id );

		return $adjacent_listing;
	}

	/**
	 * Get the adjacent Listing ID
	 *
	 * @param $link_id      String Listing ID
	 * @param $navigate     Int 1|-1 Indicate whether we are looking for the next or previous listing
	 *
	 * @return bool
	 */

	public function getAdjacentListingID( $link_id, $navigate )
	{
		global $mtconf;

		$listing = self::getListing( $link_id );

		$mtconf->setCategory( $listing->cat_id );

		if(!$listing) {
			return false;
		}

		$sql = "SELECT l.link_id FROM #__mt_links AS l"
			.	"\n LEFT JOIN #__mt_cl AS cl ON cl.link_id = l.link_id "
			.	"\n LEFT JOIN #__mt_cats AS cat ON cl.cat_id = cat.cat_id "
			.	"\n WHERE link_published='1' && link_approved='1' && cl.cat_id = " . $this->db->quote($listing->cat_id) . ' '
			.	"\n AND ( publish_up = ".$this->db->Quote($this->nullDate)." OR publish_up <= '$this->nowDate'  ) "
			.	"\n AND ( publish_down = ".$this->db->Quote($this->nullDate)." OR publish_down >= '$this->nowDate' ) "
			.   self::getSQLComparisonStatement(
					$mtconf->get('first_listing_order1'),
					$mtconf->get('first_listing_order2'),
					$navigate,
					$listing->{$mtconf->get('first_listing_order1')}
				);

		$sql .= self::getSQLOrderBy();

		$sql .= "\n LIMIT 1";
		$this->db->setQuery( $sql );

		$adjacent_link_id = $this->db->loadResult();

		if( is_null($adjacent_link_id) ) {
			return false;
		}

		return $adjacent_link_id;
	}

	public function getListing($link_id)
	{
		# Get all link data
		$this->db->setQuery( "SELECT l.*, tlcat.cat_id AS tlcat_id, cl.cat_id AS cat_id FROM (#__mt_links AS l, #__mt_cl AS cl)"
			. "\n LEFT JOIN #__mt_cats AS cat ON cat.cat_id = cl.cat_id"
			. "\n LEFT JOIN #__mt_cats AS tlcat ON tlcat.lft <= cat.lft AND tlcat.rgt >= cat.rgt AND tlcat.cat_parent =0 "
			. "\n WHERE link_published='1' AND link_approved > 0 AND l.link_id='".$link_id."' "
			. "\n AND ( publish_up = ".$this->db->Quote($this->nullDate)." OR publish_up <= '$this->nowDate'  ) "
			. "\n AND ( publish_down = ".$this->db->Quote($this->nullDate)." OR publish_down >= '$this->nowDate' ) "
			. "\n AND l.link_id = cl.link_id AND cl.main = 1"
			. "\n LIMIT 1"
		);
		return $this->db->loadObject();

	}

	/**
	 * Return a part of SQL query that produce the next or previous listing.
	 *
	 * @param $column           String database column the query will be using for the comparison
	 * @param $direction        String ASC|DESC The listings sorting direction in a category
	 * @param $navigate         Int 1|-1 Indicate whether we are looking for the next or previous listing
	 * @param $comparisonString String The value of the current listing in which, we will use for comparison.
	 *
	 * @return string
	 */
	public function getSQLComparisonStatement( $column, $direction, $navigate, $comparisonString )
	{
		switch(strtolower($direction)) {
			case 'asc':
				if( $navigate == 1 ) {
					$operator = '>';
				} else {
					$operator = '<';
				}
				break;
			default:
			case 'desc':
			if( $navigate == 1 ) {
				$operator = '<';
			} else {
				$operator = '>';
			}
				break;
		}
		$return = "\n AND  " . $column . ' ' . $operator . ' ' . $this->db->quote($comparisonString) . ' ';

		return $return;
	}

	public function getSQLOrderBy()
	{
		global $mtconf;

		$min_votes_to_show_rating = $mtconf->get('min_votes_to_show_rating');
		$first_listing_order1 = $mtconf->get('first_listing_order1');
		$first_listing_order2 = $mtconf->get('first_listing_order2');
		$second_listing_order1 = $mtconf->get('second_listing_order1');
		$second_listing_order2 = $mtconf->get('second_listing_order2');

		$sql = '';
		$sql .= "\n ORDER BY ";

		if( $min_votes_to_show_rating > 0 && $first_listing_order1 == 'link_rating' )
		{
			$sql .= "link_votes >= " . $min_votes_to_show_rating . ' DESC, ';
		}

		$sql .= $first_listing_order1 . ' ' . self::orderDirection($first_listing_order2); //$first_listing_order2;

		if( $this->hasSecondOrder() )
		{
			$sql .= ', ';
			$sql .= $second_listing_order1 . ' ' . self::orderDirection($second_listing_order2); //$second_listing_order2;
		}

		return $sql;
	}

	public function orderDirection($order2)
	{
		if( self::isDirectionNext() ) {
			return $order2;
		}

		return self::reverseOrderDirection($order2);
	}

	public function isDirectionNext()
	{
		if( $this->direction == '1' ) {
			return true;
		}
		return false;
	}

	public function reverseOrderDirection($order2)
	{
		if( strtolower($order2) == 'asc' ) {
			return 'desc';
		}
		return 'asc';
	}

	private function hasSecondOrder()
	{
		global $mtconf;

		if( $mtconf->get('second_listing_order1') != 'none' )
		{
			return true;
		}

		return false;
	}

}
