<?php
namespace Mosets;

defined('_JEXEC') or die;

use \JFactory;
use \JRoute;
use \JText;
use \JPagination;
use \JPluginHelper;

jimport('profilepicture.profilepicture');

class owner
{
	protected $db = null;

	protected $nowDate = null;

	protected $nullDate = null;

	protected $subcats = array();

	protected $orderBy = null;

	protected $orderByDirection = null;

	protected $limit = null;

	function __construct()
	{
		global $mtconf;

		$this->db = JFactory::getDBO();
		$this->nowDate	= JFactory::getDate()->toSql();
		$this->nullDate	= $this->db->getNullDate();

		$this->orderBy = 'total_listings';
		$this->orderByDirection = 'DESC';
		$this->limit = $mtconf->get('fe_num_of_owners');
	}

	public function index()
	{
		global $mtconf, $savantConf;

		$cat_id = 0;
		if( $mtconf->getCategory() ) {
			$cat_id = $mtconf->getCategory();
		}

		$this->limitToCategory($cat_id);

		$total_owners = $this->getTotalListingOwners();
		$owners = array();
		$limitstart	= JFactory::getApplication()->input->getInt('limitstart', 0);

		if( $limitstart < 0 ) $limitstart = 0;

		if( $total_owners > 0 )
		{
			$this->db->setQuery( "SELECT u.id, u.name, u.username, COUNT(DISTINCT l.link_id) AS total_listings, COUNT(DISTINCT rev.rev_id) AS total_reviews FROM (#__mt_links AS l, #__users AS u) "
				. " LEFT JOIN #__mt_reviews AS rev ON l.user_id = rev.user_id AND rev.rev_approved = 1 "
				. " WHERE l.link_published = 1 AND l.link_approved = 1 "
				. "\n AND ( l.publish_up = ".$this->db->Quote($this->nullDate)." OR l.publish_up <= '$this->nowDate'  ) "
				. "\n AND ( l.publish_down = ".$this->db->Quote($this->nullDate)." OR l.publish_down >= '$this->nowDate' ) "
				. "\n AND l.user_id = u.id "
				. "\n GROUP BY l.user_id, rev.user_id "
				. "\n ORDER BY total_listings DESC "
				. "\n LIMIT " . $limitstart . ", " . $this->limit
			);

			$owners = $this->getListingOwners( $limitstart );
		}

		# Page Navigation
		jimport('joomla.html.pagination');
		$pageNav = new \JPagination($total_owners, $limitstart, $this->limit);

		# Page Title
		setTitle(JText::_( 'COM_MTREE_PAGE_TITLE_OWNER' ));

		# Savant Template
		$savant = new \Savant2($savantConf);
		assignCommonVar($savant);

		$savant->assign('pageNav', $pageNav);
		$savant->assign('owners', $owners);
		$savant->assign('cat_id', $cat_id);
//		$savant->assign('tlcat_id', 0);
		$savant->assign('template', $mtconf->get('template'));
		$savant->assign('profilepicture_enabled', JPluginHelper::isEnabled( 'user', 'profilepicture' ));

		$savant->display( 'page_owners.tpl.php' );
	}

	public function getTotalListingOwners()
	{
		$this->db->setQuery( "SELECT COUNT(DISTINCT l.user_id) AS total_owners "
			. " FROM ("
				. " #__mt_links AS l, #__users AS u"
				. ($this->getImplodedSubcats() ? ", #__mt_cl AS cl, #__mt_cats AS cat" : "")
			. ")"
			. " WHERE l.link_published = 1 AND l.link_approved = 1 "
			. "\n AND ( l.publish_up = ".$this->db->Quote($this->nullDate)." OR l.publish_up <= '$this->nowDate'  ) "
			. "\n AND ( l.publish_down = ".$this->db->Quote($this->nullDate)." OR l.publish_down >= '$this->nowDate' ) "
			. (
				(
					$this->getImplodedSubcats()) ? "\n AND cl.cat_id IN (" . $this->getImplodedSubcats() . ") "
						. "\n AND l.link_id = cl.link_id "
						. "\n AND cl.cat_id = cat.cat_id "
					:
					''
			  )
			. "\n AND l.user_id = u.id "
		);
		return $this->db->loadResult();
	}

	function getImplodedSubcats() {
		if( (count($this->subcats) == 1 && $this->subcats[0] == 0) || $this->subcats == null ) {
			return 0;
		} else {
			return implode( ", ", $this->subcats );
		}
	}

	public function getListingOwners($limitstart=0, $limit=null)
	{
		if(!$limitstart) {
			$limitstart = 0;
		}

		if(!$limit)
		{
			$limit = $this->limit;
		}

		$sql = "SELECT u.id, u.name, u.username, COUNT(DISTINCT l.link_id) AS total_listings, COUNT(DISTINCT rev.rev_id) AS total_reviews "
				. " FROM (#__mt_links AS l, #__users AS u, #__mt_cl AS cl, #__mt_cats AS cat) "
				. " LEFT JOIN #__mt_reviews AS rev ON l.user_id = rev.user_id AND rev.rev_approved = 1 "
				. " WHERE l.link_published = 1 AND l.link_approved = 1 "
				. "\n AND ( l.publish_up = ".$this->db->Quote($this->nullDate)." OR l.publish_up <= '$this->nowDate'  ) "
				. "\n AND ( l.publish_down = ".$this->db->Quote($this->nullDate)." OR l.publish_down >= '$this->nowDate' ) "
				. "\n AND l.user_id = u.id ";

		if( $this->isOrderedByReviews() )
		{
			$sql .=  "\n AND rev.link_id = cl.link_id "
				. "\n AND cl.cat_id = cat.cat_id "
				. ( ($this->getImplodedSubcats()) ? "\n AND cl.cat_id IN (" . $this->getImplodedSubcats() . ") " : '');
		}
		else
		{
			$sql .= "\n AND l.link_id = cl.link_id "
			. "\n AND cl.cat_id = cat.cat_id "
			. ( ($this->getImplodedSubcats()) ? "\n AND cl.cat_id IN (" . $this->getImplodedSubcats() . ") " : '');
		}

		$sql .= "\n GROUP BY l.user_id, rev.user_id "
		. "\n ORDER BY " . $this->getSqlOrderBy()
		. "\n LIMIT " . $limitstart . ", " . $limit;

		$this->db->setQuery( $sql );
		$owners = $this->db->loadObjectList();

		array_walk($owners, array('self','buildOwnerUrl') );
		array_walk($owners, array('self','buildUsersListingUrl') );
		array_walk($owners, array('self','buildUsersReviewUrl') );

		return $owners;
	}

	public function setLimit($limit)
	{
		$this->limit = $limit;
	}

	public function limitToCategory( $cat_id )
	{
		require_once( JPATH_ADMINISTRATOR.'/components/com_mtree/admin.mtree.class.php' );

		if( $cat_id == 0 ) return;

		$mtCats = new \mtCats( $this->db );
		$subcats = $mtCats->getSubCats_Recursive( $cat_id, true );
		$subcats[] = $cat_id;

		if ( !empty($subcats) ) {
			$this->subcats = $subcats;
		}
	}

	protected function getSqlOrderBy()
	{
		return $this->orderBy . ' ' . $this->orderByDirection;
	}

	public function setOrderByMostListings()
	{
		$this->orderBy = 'total_listings';
		$this->orderByDirection = 'DESC';
	}

	public function setOrderByMostReviews()
	{
		$this->orderBy = 'total_reviews';
		$this->orderByDirection = 'DESC';
	}

	public function isOrderedByListings()
	{
		return ($this->orderBy == 'total_listings');
	}

	public function isOrderedByReviews()
	{
		return ($this->orderBy == 'total_reviews');
	}

	protected function buildOwnerUrl(&$owner)
	{
		$owner->url = JRoute::_( "index.php?option=com_mtree&task=viewowner&user_id=" . $owner->id );
	}

	protected function buildUsersListingUrl(&$owner)
	{
		$owner->listingsUrl = JRoute::_( "index.php?option=com_mtree&task=viewuserslisting&user_id=" . $owner->id );
	}

	protected function buildUsersReviewUrl(&$owner)
	{
		$owner->reviewsUrl = JRoute::_( "index.php?option=com_mtree&task=viewusersreview&user_id=" . $owner->id );
	}
}
