<?php
namespace Mosets;

defined('_JEXEC') or die;

/**
 * Helper methods in retrieve listings from database
 *
 * @package Mosets
 */
class listings {

	private $mtconf;

	public $isSimpleSearch = false;

	function __construct($mtConfig)
	{
		$this->mtconf = $mtConfig;
	}

	public function getFirstOrder1() {
		if($this->isSimpleSearch) {
			return $this->mtconf->get('first_search_order1');
		}
		return $this->mtconf->get('first_listing_order1');
	}

	public function getFirstOrder2() {
		if($this->isSimpleSearch) {
			return $this->mtconf->get('first_search_order2');
		}
		return $this->mtconf->get('first_listing_order2');
	}

	public function getSecondOrder1() {
		if($this->isSimpleSearch) {
			return $this->mtconf->get('second_search_order1');
		}
		return $this->mtconf->get('second_listing_order1');
	}

	public function getSecondOrder2() {
		if($this->isSimpleSearch) {
			return $this->mtconf->get('second_search_order2');
		}
		return $this->mtconf->get('second_listing_order2');
	}

	public function getSQLOrderBy()
	{
		$min_votes_to_show_rating = $this->mtconf->get('min_votes_to_show_rating');
		$first_order1 = $this->getFirstOrder1();
		$first_order2 = $this->getFirstOrder2();
		$second_order1 = $this->getSecondOrder1();
		$second_order2 = $this->getSecondOrder2();

		$sql = '';
		$sql .= "\n ORDER BY ";

		if( $min_votes_to_show_rating > 0 && $first_order1 == 'link_rating' )
		{
			$sql .= "link_votes >= " . $min_votes_to_show_rating . ' DESC, ';
			$sql .= $this->getOrderString($first_order1, $first_order2);
		}
		else
		{
			$sql .= $this->getOrderString($first_order1, $first_order2);
		}

		if( $this->hasSecondOrder() )
		{
			$sql .= ', ';
			$sql .= $this->getOrderString($second_order1, $second_order2);
		}

		return $sql;
	}

	private function getOrderString($order_by_part_1, $order_by_part_2) {
		if( $order_by_part_1 == 'random' )
		{
			$sql_order_by = 'rand(' . $this->getRandomListingsSeed() . ')';
		}
		else //if( in_array($order_by_part_1,$this->allowed_sort) )
		{
			$sql_order_by = $order_by_part_1 . ' ' . $order_by_part_2;
		}
		return $sql_order_by;
	}


	private function hasSecondOrder()
	{
		$second_order1 = $this->getSecondOrder1();

		if( $second_order1 != 'none' )
		{
			return true;
		}

		return false;
	}

	private function getRandomListingsSeed()
	{
		$current = time();
		$random_listings_shuffle_frequency = (int) $this->mtconf->get('random_listings_shuffle_frequency');

		return $current - ($current % $random_listings_shuffle_frequency);
	}

}