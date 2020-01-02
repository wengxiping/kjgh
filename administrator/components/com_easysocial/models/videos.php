<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

jimport('joomla.application.component.model');

FD::import('admin:/includes/model');

class EasySocialModelVideos extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('videos', $config);
	}

	/**
	 * Initializes all the generic states from the form
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function initStates()
	{
		$filter = $this->getUserStateFromRequest('filter', 'all');
		$ordering = $this->getUserStateFromRequest('ordering', 'id');
		$direction = $this->getUserStateFromRequest('direction', 'ASC');

		$this->setState('filter', $filter);

		parent::initStates();

		// Override the ordering behavior
		$this->setState('ordering', $ordering);
		$this->setState('direction', $direction);
	}

	/**
	 * Retrieves a list of profiles that has access to a category
	 *
	 * @since   1.4
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getCategoryAccess($categoryId, $type = 'create')
	{
		$db = ES::db();

		$sql = $db->sql();
		$sql->select('#__social_videos_categories_access');
		$sql->column('profile_id');
		$sql->where('category_id', $categoryId);
		$sql->where('type', $type);

		$db->setQuery($sql);

		$ids = $db->loadColumn();

		return $ids;
	}

	/**
	 * Inserts new access for a cluster category
	 *
	 * @since   1.4
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function insertCategoryAccess($categoryId, $type = 'create', $profiles = array())
	{
		$db = FD::db();

		// Delete all existing access type first
		$sql = $db->sql();
		$sql->delete('#__social_videos_categories_access');
		$sql->where('category_id', $categoryId);
		$sql->where('type', $type);

		$db->setQuery($sql);
		$db->Query();

		if (!$profiles) {
			return;
		}

		foreach ($profiles as $id) {
			$sql->clear();
			$sql->insert('#__social_videos_categories_access');
			$sql->values('category_id', $categoryId);
			$sql->values('type', $type);
			$sql->values('profile_id', $id);

			$db->setQuery($sql);
			$db->Query();
		}

		return true;
	}

	/**
	 * Retrieves the total featured videos available on site
	 *
	 * @since   1.4
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getTotalUserVideos($userId = null)
	{
		$user = ES::user($userId);
		$userId = $user->id;

		$sql = $this->db->sql();

		$query = "select count(1) from `#__social_videos` as a";
		$query .= " where a.state = " . $this->db->Quote(SOCIAL_VIDEO_PUBLISHED);
		$query .= " and a.user_id = " . $this->db->Quote($userId);
		$query .= " and a.`type` = " . $this->db->Quote('user');

		$sql->raw($query);
		$this->db->setQuery($sql);
		$total = (int) $this->db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the total featured videos available on site
	 *
	 * @since   1.4
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getTotalPendingVideos($userId = null)
	{
		$user = ES::user($userId);
		$userId = $user->id;

		$sql = $this->db->sql();

		$query = "select count(1) from `#__social_videos` as a";
		$query .= " where a.state = " . $this->db->Quote(SOCIAL_VIDEO_PENDING);
		$query .= " and a.user_id = " . $this->db->Quote($userId);
		$query .= " and a.`type` = " . $this->db->Quote('user');

		$sql->raw($query);
		$this->db->setQuery($sql);
		$total = (int) $this->db->loadResult();

		return $total;
	}


	/**
	 * Retrieves the total featured videos available on site
	 *
	 * @since   1.4
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getTotalFeaturedVideos($options = array())
	{
		$db = $this->db;
		$sql = $this->db->sql();
		$config = ES::config();

		$uid = $this->normalize($options, 'uid', null);
		$type = $this->normalize($options, 'type', null);
		$userid = $this->normalize($options, 'userid', null);
		$privacy = $this->normalize($options, 'privacy', true);


		$query = "select count(1) from `#__social_videos` as a";

		if ($config->get('users.blocking.enabled') && !JFactory::getUser()->guest) {
			$query .= ' LEFT JOIN `#__social_block_users` AS `bus`';
			$query .= ' ON (a.`user_id` = bus.`user_id`';
			$query .= ' AND bus.`target_id` = ' . $db->Quote(JFactory::getUser()->id);
			$query .= ' OR a.`user_id` = bus.`target_id`';
			$query .= ' AND bus.`user_id` = ' . $db->Quote(JFactory::getUser()->id) . ')';
		}

		$query .= " where a.state = " . $this->db->Quote(SOCIAL_VIDEO_PUBLISHED);
		$query .= " and a.featured = " . $this->db->Quote(SOCIAL_VIDEO_FEATURED);

		if ($userid) {
			$query .= " and a.user_id = " . $this->db->Quote($userid);
		}

		if ($uid && $type) {
			$query .= " and a.uid = " . $this->db->Quote($uid);
			$query .= " and a.type = " . $this->db->Quote($type);

			if ($type != 'user') {
				$privacy = false; // cluster do not use privacy access column
			}
		} else {

			$query .= " and a.`type` = " . $this->db->Quote('user');
		}

		if ($config->get('users.blocking.enabled') && !JFactory::getUser()->guest) {
			$query .= " and `bus`.`id` IS NULL";
		}

		if ($privacy) {
			$viewer = ES::user()->id;

			// privacy here.
			$query .= " AND (";

			//public
			$query .= " (a.`access` = " . $db->Quote( SOCIAL_PRIVACY_PUBLIC ) . ")";

			if ($viewer) {

				// if user if logged in, we need to append OR here for the 1st condition.
				$query .= " OR";

				//member
				$query .= " ( (a.`access` = " . $db->Quote(SOCIAL_PRIVACY_MEMBER) . ") AND (" . $viewer . " > 0 ) ) OR ";

				if ($config->get('friends.enabled')) {
					//friends
					$query .= " ( (a.`access` = " . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ") AND ( (" . $this->generateIsFriendSQL( 'a.`user_id`', $viewer ) . ") > 0 ) ) OR ";
				} else {
					// fall back to member
					$query .= " ( (a.`access` = " . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ") AND (" . $viewer . " > 0 ) ) OR ";
				}

				//only me
				$query .= " ( (a.`access` = " . $db->Quote(SOCIAL_PRIVACY_ONLY_ME) . ") AND ( a.`user_id` = " . $viewer . " ) ) OR ";

				// custom
				$query .= " ( (a.`access` = " . $db->Quote(SOCIAL_PRIVACY_CUSTOM) . ") AND ( a.`custom_access` LIKE " . $db->Quote( '%,' . $viewer . ',%' ) . "    ) ) OR ";

				// field
				if ($config->get('users.privacy.field')) {
					// field
					$fieldPrivacyQuery = '(select count(1) from `#__social_privacy_items_field` as fa';
					$fieldPrivacyQuery .= ' inner join `#__social_privacy_items` as fi on fi.`id` = fa.`uid` and fa.utype = ' . $db->Quote('item');
					$fieldPrivacyQuery .= ' inner join `#__social_fields` as ff on fa.`unique_key` = ff.`unique_key`';
					$fieldPrivacyQuery .= ' inner join `#__social_fields_data` as fd on ff.`id` = fd.`field_id`';
					$fieldPrivacyQuery .= ' where fi.`uid` = a.`id`';
					$fieldPrivacyQuery .= ' and fi.`type` = ' . $db->Quote('videos');
					$fieldPrivacyQuery .= ' and fd.`uid` = ' . $db->Quote($viewer);
					$fieldPrivacyQuery .= ' and fd.`type` = ' . $db->Quote('user');
					$fieldPrivacyQuery .= ' and fd.`raw` LIKE concat(' . $db->Quote('%') . ',fa.`value`,' . $db->Quote('%') . '))';

					$query .= " ((a.`access` = " . $db->Quote(SOCIAL_PRIVACY_FIELD) . ") AND (a.`field_access` <= " . $fieldPrivacyQuery . ")) OR ";
				} else {
					$query .= " ((a.`access` = " . $db->Quote(SOCIAL_PRIVACY_FIELD) . ") AND (" . $viewer . " > 0)) OR ";
				}

				// my own items.
				$query .= " (a.`user_id` = " . $viewer . ")";

			}

			// privacy checking end here.
			$query .= " )";

		}

		$sql->raw($query);
		$this->db->setQuery($sql);
		$total = (int) $this->db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the total videos available on site
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function getTotalVideos($options = array())
	{
		$db = $this->db;
		$sql = $this->db->sql();
		$config = ES::config();

		$uid = $this->normalize($options, 'uid', null);
		$type = $this->normalize($options, 'type', null);
		$userid = $this->normalize($options, 'userid', null);
		$state = $this->normalize($options, 'state', SOCIAL_VIDEO_PUBLISHED);
		$privacy = $this->normalize($options, 'privacy', true);
		$day = $this->normalize($options, 'day', false);

		$viewer = ES::user()->id;

		$cond = array();

		$query = "select count(1) from `#__social_videos` as a";

		if (!ES::user()->isSiteAdmin() && $privacy && $type != 'user' && !is_null($type)) {
			$query .= " inner join `#__social_clusters` as cls on a.`uid` = cls.`id` and a.`type` = cls.`cluster_type`";
		}

		if ($config->get('users.blocking.enabled') && !JFactory::getUser()->guest) {
			$query .= ' LEFT JOIN `#__social_block_users` AS `bus`';
			$query .= ' ON (a.`user_id` = bus.`user_id`';
			$query .= ' AND bus.`target_id` = ' . $db->Quote(JFactory::getUser()->id);
			$query .= ' OR a.`user_id` = bus.`target_id`';
			$query .= ' AND bus.`user_id` = ' . $db->Quote(JFactory::getUser()->id) . ')';
		}

		if ($state != 'all') {
			$cond[] = "a.state = " . $this->db->Quote($state);
		}


		if ($userid) {
			$cond[] = "a.user_id = " . $this->db->Quote($userid);
		}

		if ($uid && $type) {
			$cond[] = "a.uid = " . $this->db->Quote($uid);
			$cond[] = " a.type = " . $this->db->Quote($type);
		} else {
			$cond[] = "a.`type` = " . $this->db->Quote('user');
		}

		if ($config->get('users.blocking.enabled') && !JFactory::getUser()->guest) {
			$cond[] = "`bus`.`id` IS NULL";
		}

		if ($day) {
			$start 	= $day . ' 00:00:01';
			$end 	= $day . ' 23:59:59';
			$cond[] = '(a.`created` >= ' . $this->db->Quote( $start ) . ' and a.`created` <= ' . $this->db->Quote( $end ) . ')';
		}

		if (!ES::user()->isSiteAdmin() && $privacy && !is_null($type) && $type != 'user') {
			$tmp = "(";
			$tmp .= " (cls.`type` IN (1,4)) OR";
			$tmp .= " (cls.`type` > 1) AND " . $this->db->Quote($viewer) . " IN ( select scn.`uid` from `#__social_clusters_nodes` as scn where scn.`cluster_id` = a.`uid` and scn.`type` = " . $this->db->Quote(SOCIAL_TYPE_USER) . " and scn.`state` = 1)";
			$tmp .= ")";

			$cond[] = $tmp;
		}


		if (!ES::user()->isSiteAdmin() && $privacy && ($type == 'user' || is_null($type))) {
		
			$viewer = ES::user()->id;

			// privacy here.
			$tmp = "(";

			//public
			$tmp .= " (a.`access` = " . $db->Quote( SOCIAL_PRIVACY_PUBLIC ) . ")";

			if ($viewer) {

				// if user if logged in, we need to append OR here for the 1st condition.
				$tmp .= " OR";

				//member
				$tmp .= " ( (a.`access` = " . $db->Quote(SOCIAL_PRIVACY_MEMBER) . ") AND (" . $viewer . " > 0 ) ) OR ";

				if ($config->get('friends.enabled')) {
					//friends
					$tmp .= " ( (a.`access` = " . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ") AND ( (" . $this->generateIsFriendSQL( 'a.`user_id`', $viewer ) . ") > 0 ) ) OR ";
				} else {
					// fall back to member
					$tmp .= " ( (a.`access` = " . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ") AND (" . $viewer . " > 0 ) ) OR ";
				}

				//only me
				$tmp .= " ( (a.`access` = " . $db->Quote(SOCIAL_PRIVACY_ONLY_ME) . ") AND ( a.`user_id` = " . $viewer . " ) ) OR ";

				// custom
				$tmp .= " ( (a.`access` = " . $db->Quote(SOCIAL_PRIVACY_CUSTOM) . ") AND ( a.`custom_access` LIKE " . $db->Quote( '%,' . $viewer . ',%' ) . "    ) ) OR ";

				// field
				if ($config->get('users.privacy.field')) {
					// field
					$fieldPrivacyQuery = '(select count(1) from `#__social_privacy_items_field` as fa';
					$fieldPrivacyQuery .= ' inner join `#__social_privacy_items` as fi on fi.`id` = fa.`uid` and fa.utype = ' . $db->Quote('item');
					$fieldPrivacyQuery .= ' inner join `#__social_fields` as ff on fa.`unique_key` = ff.`unique_key`';
					$fieldPrivacyQuery .= ' inner join `#__social_fields_data` as fd on ff.`id` = fd.`field_id`';
					$fieldPrivacyQuery .= ' where fi.`uid` = a.`id`';
					$fieldPrivacyQuery .= ' and fi.`type` = ' . $db->Quote('videos');
					$fieldPrivacyQuery .= ' and fd.`uid` = ' . $db->Quote($viewer);
					$fieldPrivacyQuery .= ' and fd.`type` = ' . $db->Quote('user');
					$fieldPrivacyQuery .= ' and fd.`raw` LIKE concat(' . $db->Quote('%') . ',fa.`value`,' . $db->Quote('%') . '))';

					$tmp .= " ((a.`access` = " . $db->Quote(SOCIAL_PRIVACY_FIELD) . ") AND (a.`field_access` <= " . $fieldPrivacyQuery . ")) OR ";
				} else {
					$tmp .= " ((a.`access` = " . $db->Quote(SOCIAL_PRIVACY_FIELD) . ") AND (" . $viewer . " > 0)) OR ";
				}

				// my own items.
				$tmp .= " (a.`user_id` = " . $viewer . ")";

			}

			// privacy checking end here.
			$tmp .= ")";

			$cond[] = $tmp;
		}


		if ($cond) {
			if (count($cond) == 1) {
				$query .= " where " . $cond[0];
			} else if (count($cond) > 1) {

				$whereCond = array_shift($cond);

				$query .= " where " . $whereCond;
				$query .= " and " . implode(" and ", $cond);
			}
		}

		$sql->raw($query);

		$this->db->setQuery($sql);
		$total = (int) $this->db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the list of videos for the back end
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function getItems()
	{
		$sql = $this->db->sql();

		$filter = $this->getState('filter');
		$search = $this->getState('search');

		$sql->select('#__social_videos');

		if ($filter != 'all') {
			$sql->where('state', $filter);
		}

		if ($search) {
			$sql->where('title', '%' . $search . '%', 'LIKE');
		}

		// Determine the ordering
		$ordering = $this->getState('ordering');

		if ($ordering) {
			$direction = $this->getState('direction');
			$sql->order($ordering , $direction);
		}

		// Set the total records for pagination.
		$this->setTotal($sql->getTotalSql());

		$result = $this->getData($sql);

		if (!$result) {
			return $result;
		}

		$videos = array();

		foreach ($result as $row) {

			$tmp = (array) $row;

			$row = ES::table('Video');
			$row->bind($tmp);

			$video = ES::video($row);

			$videos[] = $video;
		}

		return $videos;
	}

	/**
	 * Retrieves a list of videos from the site
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function getVideosForCron($options = array())
	{
		// search criteria
		$filter = $this->normalize($options, 'filter', '');
		$sort = $this->normalize($options, 'sort', 'latest');
		$limit = $this->normalize($options, 'limit', false);

		$db = ES::db();
		$sql = $db->sql();

		$query[] = "select a.* from `#__social_videos` as a";

		if ($filter == 'processing') {
			$query[] = 'WHERE a.`state`=' . $db->Quote(SOCIAL_VIDEO_PROCESSING);
		} else {
			$query[] = "where a.`state` = " . $db->Quote(SOCIAL_VIDEO_PENDING);
		}

		if ($sort) {
			switch ($sort) {
				case 'popular':
					$query[] = "order by a.hits desc";
					break;

				case 'alphabetical':
					$query[] = "order by a.title asc";
					break;

				case 'random':
					$query[] = "order by RAND()";
					break;

				case 'likes':
					$query[] = "order by likes desc";
					break;

				case 'commented':
					$query[] = "order by totalcomments desc";
					break;

				case 'latest':
				default:
					$query[] = "order by a.created desc";
					break;
			}
		}

		if ($limit) {
			$query[] = "limit $limit";
		}

		$query = implode(' ', $query);
		$sql->raw($query);

		$db->setQuery($sql);
		$results = $db->loadObjectList();

		$videos = array();

		if ($results) {
			foreach ($results as $row) {
				$video = ES::video($row->uid, $row->type);
				$video->load($row);

				$videos[] = $video;
			}
		}

		return $videos;
	}

	/**
	 * Retrieves the list of items which stored in Amazon
	 *
	 * @since   1.4.6
	 * @access  public
	 */
	public function getVideosStoredExternally($storageType = 'amazon')
	{
		// Get the number of files to process at a time
		$config = ES::config();
		$limit = $config->get('storage.amazon.limit', 10);

		$db = FD::db();
		$sql = $db->sql();
		$sql->select('#__social_videos');
		$sql->where('storage', $storageType);
		$sql->limit($limit);

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Retrieves the list of items which stored in joomla and ready to sync to remote storage
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function getVideosForRemoteUpload($exclusion = array(), $limit = 10)
	{

		// Get the number of files to process at a time
		$config = ES::config();

		$db = ES::db();

		$query = "SELECT a.* FROM `#__social_videos` as a";
		$query .= " WHERE a.`state` = " . $db->Quote(SOCIAL_VIDEO_PUBLISHED);
		$query .= " AND a.`storage` = " . $db->Quote(SOCIAL_STORAGE_JOOMLA);

		if ($exclusion) {

			$exclusion = ES::makeArray($exclusion);
			$exclusionIds = array();

			foreach ($exclusion as $exclusionId) {
				$exclusionIds[] = $db->Quote($exclusionId);
			}

			$exclusionIds = implode(',', $exclusionIds);

			$query .= 'AND a.`id` NOT IN (' . $exclusionIds . ')';
		}

		$query .= " ORDER BY a.`created` asc";
		$query .= " LIMIT $limit";

		$db->setQuery($query);

		$result = $db->loadObjectList();

		if (!$result) {
			return $result;
		}

		$videos = array();

		foreach ($result as $row) {
			$video = ES::video($row->uid, $row->type);
			$video->load($row);

			$cluster = $video->getCluster();

			$video->creator = $video->getVideoCreator($cluster);

			$videos[] = $video;
		}

		return $videos;
	}

	/**
	 * Retrieves a list of videos from the site
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function getVideos($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();
		$config = ES::config();

		$likeCountColumn = "(select count(1) from `#__social_likes` as exb where exb.uid = a.id and exb.type = 'videos.user.create') as likes";
		$commentCountColumn = "(select count(1) from `#__social_comments` as exb where exb.uid = a.id and exb.element = 'videos.user.create') as totalcomments";

		// search criteria
		$privacy = $this->normalize($options, 'privacy', true);

		// If privacy has been disabled, we should always disable privacy checks
		// if (!$config->get('privacy.enabled')) {
		// 	$privacy = false;
		// }

		$filter = $this->normalize($options, 'filter', '');
		$featured = $this->normalize($options, 'featured', null);
		$category = $this->normalize($options, 'category', '');
		$sort = $this->normalize($options, 'sort', 'latest');
		$maxlimit = $this->normalize($options, 'maxlimit', 0);
		$limit = $this->normalize($options, 'limit', false);
		$includeFeatured = $this->normalize($options, 'includeFeatured', null);

		$storage = $this->normalize($options, 'storage', false);
		$uid = $this->normalize($options, 'uid', null);
		$type = $this->normalize($options, 'type', null);
		$source = $this->normalize($options, 'source', false);

		$userid = $this->normalize($options, 'userid', null);

		$hashtags = $this->normalize($options, 'hashtags', null);

		$useLimit = true;

		$query = array();

		$isSiteAdmin = ES::user()->isSiteAdmin();

		$query[] = "select a.*";

		if ($sort == 'likes') {
			$query[] = ", $likeCountColumn";
		}

		if ($sort == 'commented') {
			$query[] = ", $commentCountColumn";
		}

		$query[] = "from `#__social_videos` as a";

		if ($config->get('users.blocking.enabled') && !JFactory::getUser()->guest) {
			$query[] = 'LEFT JOIN `#__social_block_users` AS `bus`';
			$query[] = 'ON (a.`user_id` = bus.`user_id`';
			$query[] = 'AND bus.`target_id` = ' . $db->Quote(JFactory::getUser()->id);
			$query[] = 'OR a.`user_id` = bus.`target_id`';
			$query[] = 'AND bus.`user_id` = ' . $db->Quote(JFactory::getUser()->id) . ')';
		}


		if (!is_null($type) && $type != 'user') {
			$query[] = " inner join `#__social_clusters` as cls on a.`uid` = cls.`id` and a.`type` = cls.`cluster_type`";
		}

		if ($hashtags) {
			$query[] = " inner join`#__social_tags` as hashtag on a.`id` = hashtag.`target_id`";
		}

		if ($filter == 'pending') {
			$query[] = "where a.`state` = " . $db->Quote(SOCIAL_VIDEO_PENDING);
		} else if ($filter == 'processing') {
			$query[] = 'WHERE a.`state`=' . $db->Quote(SOCIAL_VIDEO_PROCESSING);
		} else {
			$query[] = "where a.`state` = " . $db->Quote(SOCIAL_VIDEO_PUBLISHED);
		}

		if ($uid && $type) {
			$query[] = 'AND a.`uid`=' . $db->Quote($uid);
			$query[] = 'AND a.`type`=' . $db->Quote($type);
		} else {
			$query[] = 'and a.`type` = ' . $db->Quote('user');
		}

		if ($filter == 'mine') {
			$my = ES::user();
			$query[] = "and a.`user_id` = " . $db->Quote($my->id);
		}

		if ($filter == 'pending' && $userid) {
			$query[] = "and a.`user_id` = " . $db->Quote($userid);
		}

		if ($filter == SOCIAL_TYPE_USER) {
			$query[] = "and a.`user_id` = " . $db->Quote($userid);
		}

		if ($category) {
			$query[] = "and a.`category_id` = " . $db->Quote($category);
		}

		if ($hashtags) {
			$hashtagQuery = array();

			$tags = explode(',', $hashtags);

			if ($tags) {
				if (count($tags) == 1) {
					$query[] = 'AND hashtag.`title` =' . $db->Quote($tags[0]);
				} else {
					$totalTags = count($tags);
					$tagQuery = '';

					for ($t = 0; $t < $totalTags; $t++) {
						$tagQuery .= ($t < $totalTags - 1) ? ' ( hashtag.`title` = ' . $db->Quote($tags[$t]) . ') OR ' : ' ( hashtag.`title` = ' . $db->Quote($tags[$t]) . ')';
					}

					$query[] = 'AND ( ' . $tagQuery . ' )';
				}

				$query[] = 'AND hashtag.`target_type` = ' . $db->Quote('video');
			}
		}

		$exclusion = $this->normalize($options, 'exclusion', null);

		if ($exclusion) {

			$exclusion = ES::makeArray($exclusion);
			$exclusionIds = array();

			foreach ($exclusion as $exclusionId) {
				$exclusionIds[] = $db->Quote($exclusionId);
			}

			$exclusionIds = implode(',', $exclusionIds);

			$query[] = 'AND a.' . $db->qn('id') . ' NOT IN (' . $exclusionIds . ')';
		}

		// featured filtering
		if ($filter == 'featured') {
			$query[] = "and a.`featured` = " . $db->Quote(SOCIAL_VIDEO_FEATURED);
		}

		// featured video only
		if (!$includeFeatured && !is_null($featured) && $featured !== '') {
			$query[] = "and a.`featured` = " . $db->Quote((int) $featured);
		}

		if ($storage !== false) {
			$query[] = 'AND a.`storage` = ' . $db->Quote($storage);
		}

		if ($source !== false) {
			$query[] = 'AND a.`source`=' . $db->Quote($source);
		}

		if ($config->get('users.blocking.enabled') && !JFactory::getUser()->guest) {
			$query[] = "AND `bus`.`id` IS NULL";
		}


		if (!$isSiteAdmin && $privacy) {

			$viewer = ES::user()->id;

			if ($type != 'user' && !is_null($type)) {
				// cluster privacy
				$query[] = " AND (";
				$query[] = " (cls.`type` IN (1,4)) OR";
				$query[] = " ((cls.`type` > 1) AND " . $db->Quote($viewer) . " IN ( select scn.`uid` from `#__social_clusters_nodes` as scn where scn.`cluster_id` = a.`uid` and scn.`type` = " . $db->Quote(SOCIAL_TYPE_USER) . " and scn.`state` = 1))";
				$query[] = ")";

			} else if ($config->get('privacy.enabled')) {

				// privacy here.
				$query[] = " AND (";

				//public
				$query[] = "(a.`access` = " . $db->Quote( SOCIAL_PRIVACY_PUBLIC ) . ")";

				if ($viewer) {
					// if this is logged in user.

					// this is the OR from the 1st condition. need to append here if user is logged in.
					$query[] = " OR";

					//member
					$query[] = "( (a.`access` = " . $db->Quote(SOCIAL_PRIVACY_MEMBER) . ") AND (" . $viewer . " > 0 ) ) OR ";

					if ($config->get('friends.enabled')) {
						//friends
						$query[] = "( (a.`access` = " . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ") AND ( (" . $this->generateIsFriendSQL( 'a.`user_id`', $viewer ) . ") > 0 ) ) OR ";
					} else {
						// fall back to member
						$query[] = "( (a.`access` = " . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ") AND (" . $viewer . " > 0 ) ) OR ";
					}


					//only me
					$query[] = "( (a.`access` = " . $db->Quote(SOCIAL_PRIVACY_ONLY_ME) . ") AND ( a.`user_id` = " . $viewer . " ) ) OR ";

					// custom
					$query[] = "( (a.`access` = " . $db->Quote(SOCIAL_PRIVACY_CUSTOM) . ") AND ( a.`custom_access` LIKE " . $db->Quote( '%,' . $viewer . ',%' ) . "    ) ) OR ";

					// field
					if ($config->get('users.privacy.field')) {
						// field
						$fieldPrivacyQuery = '(select count(1) from `#__social_privacy_items_field` as fa';
						$fieldPrivacyQuery .= ' inner join `#__social_privacy_items` as fi on fi.`id` = fa.`uid` and fa.utype = ' . $db->Quote('item');
						$fieldPrivacyQuery .= ' inner join `#__social_fields` as ff on fa.`unique_key` = ff.`unique_key`';
						$fieldPrivacyQuery .= ' inner join `#__social_fields_data` as fd on ff.`id` = fd.`field_id`';
						$fieldPrivacyQuery .= ' where fi.`uid` = a.`id`';
						$fieldPrivacyQuery .= ' and fi.`type` = ' . $db->Quote('videos');
						$fieldPrivacyQuery .= ' and fd.`uid` = ' . $db->Quote($viewer);
						$fieldPrivacyQuery .= ' and fd.`type` = ' . $db->Quote('user');
						$fieldPrivacyQuery .= ' and fd.`raw` LIKE concat(' . $db->Quote('%') . ',fa.`value`,' . $db->Quote('%') . '))';

						$query[] = "((a.`access` = " . $db->Quote(SOCIAL_PRIVACY_FIELD) . ") AND (a.`field_access` <= " . $fieldPrivacyQuery . ")) OR ";
					} else {
						$query[] = " ((a.`access` = " . $db->Quote(SOCIAL_PRIVACY_FIELD) . ") AND (" . $viewer . " > 0)) OR ";
					}

					// my own items.
					$query[] = "(a.`user_id` = " . $viewer . ")";
				}

				// privacy checking end here.
				$query[] = ")";

			}
		}

		// var_dump($maxlimit, $limit);

		if (!$maxlimit && $limit) {

			$totalQuery = implode(' ', $query);

			// Set the total number of items.
			$this->setTotal( $totalQuery , true);
		}

		// the sorting must come after the privacy checking to have better sql performance.
		if ($sort) {
			switch ($sort) {
				case 'popular':
					$query[] = "order by a.`hits` desc";
					break;

				case 'alphabetical':
					$query[] = "order by a.`title` asc";
					break;

				case 'random':

					$rndColumns = array('a.id', 'a.title', 'a.hits', 'a.featured', 'a.title');
					$rndSorts = array('asc', 'desc', 'desc', 'asc', 'asc', 'desc');

					$rndColumn = $rndColumns[array_rand($rndColumns)];
					$rndSort = $rndSorts[array_rand($rndSorts)];

					$query[] = "order by $rndColumn $rndSort";

					break;

				case 'likes':
					$query[] = "order by likes desc";
					break;

				case 'commented':
					$query[] = "order by totalcomments desc";
					break;

				case 'latest':
				default:
					$query[] = "order by a.`created` desc";
					break;
			}
		}

		if ($maxlimit) {
			$useLimit = false;
			$query[] = "limit $maxlimit";
		} else if ($limit) {

			// Get the limitstart.
			$limitstart = JFactory::getApplication()->input->get('limitstart', 0, 'int');
			$limitstart = ( $limit != 0 ? ( floor( $limitstart / $limit ) * $limit ) : 0 );

			$this->setState('limit', $limit);
			$this->setState('limitstart', $limitstart);

			$query[] = "limit $limitstart, $limit";
		}


		$query = implode(' ', $query);

		// echo $query;exit;

		$sql->clear();
		$sql->raw($query);

		// echo $sql;exit;


		$this->db->setQuery($sql);
		$result = $this->db->loadObjectList();


		if (!$result) {
			return $result;
		}

		$videos = array();

		foreach ($result as $row) {
			$video = ES::video($row->uid, $row->type);
			$video->load($row);

			$cluster = $video->getCluster();

			$video->creator = $video->getVideoCreator($cluster);

			$videos[] = $video;
		}

		return $videos;
	}

	/**
	 * Retrieves a list of videos from a particular user for GDPR
	 *
	 * @since   2.2
	 * @access  public
	 */
	public function getVideosGDPR($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();
		$config = ES::config();

		$filter = $this->normalize($options, 'filter', '');
		$limit = $this->normalize($options, 'limit', false);
		$userid = $this->normalize($options, 'userid', null);
		$useLimit = true;

		$query = array();

		$query[] = "select a.*";
		$query[] = "from `#__social_videos` as a";
		$query[] = "where a.`user_id` = " . $db->Quote($userid);


		$exclusion = $this->normalize($options, 'exclusion', null);

		if ($exclusion) {

			$exclusion = ES::makeArray($exclusion);
			$exclusionIds = array();

			foreach ($exclusion as $exclusionId) {
				$exclusionIds[] = $db->Quote($exclusionId);
			}

			$exclusionIds = implode(',', $exclusionIds);

			$query[] = 'AND a.' . $db->qn('id') . ' NOT IN (' . $exclusionIds . ')';
		}

		if ($limit) {
			$totalQuery = implode(' ', $query);

			// Set the total number of items.
			$this->setTotal($totalQuery, true);
		}

		// Get the limitstart.
		$limitstart = JFactory::getApplication()->input->get('limitstart', 0, 'int');
		$limitstart = ( $limit != 0 ? ( floor( $limitstart / $limit ) * $limit ) : 0 );

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		$query[] = "limit $limitstart, $limit";

		$query = implode(' ', $query);

		$sql->clear();
		$sql->raw($query);

		$this->db->setQuery($sql);
		$result = $this->db->loadObjectList();


		if (!$result) {
			return $result;
		}

		$videos = array();

		foreach ($result as $row) {
			$video = ES::video($row->uid, $row->type);
			$video->load($row);

			$cluster = $video->getCluster();

			$video->creator = $video->getVideoCreator($cluster);

			$videos[] = $video;
		}

		return $videos;
	}

	/**
	 * Overriding parent getData method so that we can specify if we need the limit or not.
	 *
	 * If using the pagination query, child needs to use this method.
	 *
	 * @since   1.4
	 * @access  public
	 */
	protected function getData($query , $useLimit = true)
	{
		if ($useLimit) {
			return parent::getData($query);
		} else {
			$this->db->setQuery($query);
		}

		return $this->db->loadObjectList();
	}


	public function generateIsFriendSQL( $source, $target )
	{
		$query = "select count(1) from `#__social_friends` where ( `actor_id` = $source and `target_id` = $target) OR (`target_id` = $source and `actor_id` = $target) and `state` = 1";
		return $query;
	}


	/**
	 * Retrieves the default category
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function getDefaultCategory()
	{
		$db = $this->db;
		$sql = $db->sql();

		$sql->select('#__social_videos_categories');
		$sql->where('default', 1);

		$db->setQuery($sql);

		$result = $db->loadObject();

		if (!$result) {
			return false;
		}

		$category = ES::table('VideoCategory');
		$category->bind($result);

		return $category;
	}

	/**
	 * Retrieves a list of video categories from the site
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function getCategories($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = array();
		$query[] = 'SELECT a.* FROM ' . $db->qn('#__social_videos_categories') . ' AS a';

		// Filter for respecting creation access
		$respectAccess = $this->normalize($options, 'respectAccess', false);
		$profileId = $this->normalize($options, 'profileId', 0);

		if ($respectAccess && $profileId) {
			$query[] = 'LEFT JOIN ' . $db->qn('#__social_videos_categories_access') . ' AS b';
			$query[] = 'ON a.id = b.category_id';
		}

		$query[] = 'WHERE 1 ';

		// Filter for searching categories
		$search = $this->normalize($options, 'search', '');

		if ($search) {
			$query[] = 'AND ';
			$query[] = $db->qn('title') . ' LIKE ' . $db->Quote('%' . $search . '%');
		}

		// Respect category creation access
		if ($respectAccess && $profileId) {
			$query[] = 'AND (';
			$query[] = '(b.`profile_id`=' . $db->Quote($profileId) . ')';
			$query[] = 'OR';
			$query[] = '(a.' . $db->qn('id') . ' NOT IN (SELECT `category_id` FROM `#__social_videos_categories_access`))';
			$query[] = ')';
		}

		// Ensure that the videos are published
		$state = $this->normalize($options, 'state', true);

		// Ensure that all the categories are listed in backend
		$adminView = $this->normalize($options, 'administrator', false);

		if (!$adminView) {
			$query[] = 'AND ' . $db->qn('state') . '=' . $db->Quote($state);
		}

		$ordering = $this->normalize($options, 'ordering', '');
		$direction = $this->normalize($options, 'direction', '');

		if ($ordering) {
			$query[] = ' ORDER BY ' . $db->qn($ordering) . ' ' . $direction;
		}

		$query = implode(' ', $query);

		// Determines if we need to paginate the result
		$paginate = $this->normalize($options, 'pagination', true);

		if ($paginate) {
			// Set the total records for pagination.
			$totalSql = str_ireplace('a.*', 'COUNT(1)', $query);
			$this->setTotal($totalSql);
		}

		// We need to go through our paginated library
		$result = $this->getData($query, $paginate);

		if (!$result) {
			return $result;
		}

		$categories = array();

		foreach ($result as $row) {
			$category = ES::table('VideoCategory');
			$category->bind($row);

			$categories[] = $category;
		}

		return $categories;
	}


	/**
	 * Retrieves the total number of videos from a category
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function getTotalVideosFromCategory($categoryId, $cluster = false, $uid = null, $type = null)
	{
		$db = $this->db;
		$sql = $this->db->sql();
		$config = ES::config();
		$viewer = ES::user()->id;

		$idx = '';
		static $_cache = array();

		if ($cluster) {
			$idx = $cluster->id . '-' . $cluster->getType();
		} else {
			$idx = $uid . '-' . $type;
		}

		$privacy = !ES::user()->isSiteAdmin() && $config->get('privacy.enabled');

		if (!isset($_cache[$idx])) {

			$query = "select a.`category_id`, count(a.`id`) as `total`";
			$query .= " FROM #__social_videos as a";


			if ($config->get('users.blocking.enabled') && !JFactory::getUser()->guest) {
				$query .= ' LEFT JOIN `#__social_block_users` AS `bus`';
				$query .= ' ON (a.`user_id` = bus.`user_id`';
				$query .= ' AND bus.`target_id` = ' . $db->Quote(JFactory::getUser()->id);
				$query .= ' OR a.`user_id` = bus.`target_id`';
				$query .= ' AND bus.`user_id` = ' . $db->Quote(JFactory::getUser()->id) . ')';
			}

			$query .= " where a.state = " . $this->db->Quote(SOCIAL_VIDEO_PUBLISHED);

			if (!is_null($uid) && !is_null($type)) {
				if ($type == SOCIAL_TYPE_USER) {
					$query .= " and a.user_id = " . $db->Quote($uid);
				}

				if ($cluster && !($cluster instanceof SocialUser)) {
					$query .= " and a.uid = " . $db->Quote($cluster->id);
					$query .= " and a.type = " . $db->Quote($cluster->getType());

					$privacy = false; // cluster do not use privacy access column
				} else {

					$query .= " and a.`type` = " . $this->db->Quote('user');
				}
			}

			if ($config->get('users.blocking.enabled') && !JFactory::getUser()->guest) {
				$query .= " and `bus`.`id` IS NULL";
			}

			if ($privacy) {

				// privacy here.
				$query .= " AND (";

				//public
				$query .= " (a.`access` = " . $db->Quote( SOCIAL_PRIVACY_PUBLIC ) . ")";

				if ($viewer) {

					// this is the OR from 1st condition and we need to append it here if user is logged in.
					$query .= " OR";

					//member
					$query .= " ( (a.`access` = " . $db->Quote(SOCIAL_PRIVACY_MEMBER) . ") AND (" . $viewer . " > 0 ) ) OR ";

					if ($config->get('friends.enabled')) {
						//friends
						$query .= " ( (a.`access` = " . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ") AND ( (" . $this->generateIsFriendSQL( 'a.`user_id`', $viewer ) . ") > 0 ) ) OR ";
					} else {
						// fall back to member
						$query .= " ( (a.`access` = " . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ") AND (" . $viewer . " > 0 ) ) OR ";
					}

					//only me
					$query .= " ( (a.`access` = " . $db->Quote(SOCIAL_PRIVACY_ONLY_ME) . ") AND ( a.`user_id` = " . $viewer . " ) ) OR ";

					// custom
					$query .= " ( (a.`access` = " . $db->Quote(SOCIAL_PRIVACY_CUSTOM) . ") AND ( a.`custom_access` LIKE " . $db->Quote( '%,' . $viewer . ',%' ) . "    ) ) OR ";

					// field
					if ($config->get('users.privacy.field')) {
						// field
						$fieldPrivacyQuery = '(select count(1) from `#__social_privacy_items_field` as fa';
						$fieldPrivacyQuery .= ' inner join `#__social_privacy_items` as fi on fi.`id` = fa.`uid` and fa.utype = ' . $db->Quote('item');
						$fieldPrivacyQuery .= ' inner join `#__social_fields` as ff on fa.`unique_key` = ff.`unique_key`';
						$fieldPrivacyQuery .= ' inner join `#__social_fields_data` as fd on ff.`id` = fd.`field_id`';
						$fieldPrivacyQuery .= ' where fi.`uid` = a.`id`';
						$fieldPrivacyQuery .= ' and fi.`type` = ' . $db->Quote('videos');
						$fieldPrivacyQuery .= ' and fd.`uid` = ' . $db->Quote($viewer);
						$fieldPrivacyQuery .= ' and fd.`type` = ' . $db->Quote('user');
						$fieldPrivacyQuery .= ' and fd.`raw` LIKE concat(' . $db->Quote('%') . ',fa.`value`,' . $db->Quote('%') . '))';

						$query .= " ((a.`access` = " . $db->Quote(SOCIAL_PRIVACY_FIELD) . ") AND (a.`field_access` <= " . $fieldPrivacyQuery . ")) OR ";
					} else {
						$query .= " ((a.`access` = " . $db->Quote(SOCIAL_PRIVACY_FIELD) . ") AND (" . $viewer . " > 0)) OR ";
					}

					// my own items.
					$query .= " (a.`user_id` = " . $viewer . ")";
				}

				// privacy checking end here.
				$query .= ")";
			} 

			$query .= " group by a.`category_id`";

			$sql->raw($query);
			$this->db->setQuery($sql);
			$results = $this->db->loadObjectList();

			$tmp = array();

			if ($results) {
				foreach ($results as $item) {
					$tmp[$item->category_id] = $item->total;
				}
			}

			$_cache[$idx] = $tmp;
		}

		$data = $_cache[$idx];
		$total = 0;

		if (isset($data[$categoryId]) && $data[$categoryId]) {
			$total = $data[$categoryId];
		}

		return $total;
	}

	/**
	 * Determines if the video should be associated with the stream item
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function getStreamId($videoId, $verb)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_stream_item', 'a');
		$sql->column('a.uid');
		$sql->where('a.context_type', SOCIAL_TYPE_VIDEOS);
		$sql->where('a.context_id', $videoId);
		$sql->where('a.verb', $verb);

		$db->setQuery($sql);

		$uid    = (int) $db->loadResult();

		return $uid;
	}

	/**
	 * Update videos categories ordering
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function updateCategoriesOrdering($id, $order)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "update `#__social_videos_categories` set ordering = " . $db->Quote($order);
		$query .= " where id = " . $db->Quote($id);

		$sql->raw($query);

		$db->setQuery($sql);
		$state = $db->query();

		return $state;
	}

	/**
	 * Get video from stream
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getStreamVideo($streamId)
	{
		$db = ES::db();

		$query = "select a.* from `#__social_videos` as a";
		$query .= " inner join `#__social_stream_item` as b on a.`id` = b.`context_id`";
		$query .= " where b.`uid` = " . $db->Quote($streamId);
		$query .= " and a.`state` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);

		$db->setQuery($query);
		$row = $db->loadObject();

		$video = ES::video($row->uid, $row->type);
		$video->load($row);

		return $video;
	}

	/**
	 * update video from stream
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function updateStreamVideo($streamId, $data)
	{
		$db = ES::db();
		$config = ES::config();

		$streamItem = ES::table('StreamItem');
		$streamItem->load(array('uid' => $streamId));

		// video id before we update
		$existingId = $streamItem->context_id;
		$oldVideo = ES::table('video');
		$oldVideo->load($existingId);

		// determine if this is a new video
		$isNewVideo = false;
		if ($oldVideo->source == $data['source']) {
			$isNewVideo = $oldVideo->source == 'link' ? $oldVideo->path != $data['link'] : $existingId != $data['id'];
		} else {
			$isNewVideo = true;
		}

		if ($isNewVideo) {

			// 1. update new video state.
			// 2. update existing stream item with new video id.
			// 3. remove old video.

			$video = ES::video();

			// Save options for the video library
			$saveOptions = array();

			// If this is a link source, we just load up a new video library
			if ($data['source'] == 'link') {
				$data['link'] = $video->format($data['link']);
			}

			// If this is a video upload, the id should be provided because videos are created first.
			if ($data['source'] == 'upload') {
				$id = $data['id'];

				$video = ES::video();
				$video->load($id);

				// Video library needs to know that we're storing this from the story
				$saveOptions['story'] = true;

				// We cannot publish the video if auto encoding is disabled
				if ($config->get('video.autoencode')) {
					$data['state'] = SOCIAL_VIDEO_PUBLISHED;
				}
			}

			$data['uid'] = $oldVideo->uid;
			$data['type'] = $oldVideo->type;
			$data['user_id'] = $oldVideo->user_id;

			// update isnew flag
			$video->table->isnew = 0;

			// saving new video
			unset($data['id']);
			$video->save($data, array(), $saveOptions);

			// update existing stream item
			$streamItem->context_id = $video->id;
			$streamItem->store();

			// delete existing video
			$oldVideo->delete();

		} else {
			// 1. update title, description and category for existing video.

			$oldVideo->title = $data['title'];
			$oldVideo->description = $data['description'];
			$oldVideo->category_id = $data['category_id'];

			// make sure title has no leading / ending space
			$oldVideo->title = JString::trim($oldVideo->title);

			// replace two or more spacing in between words into one spacing only.
			$oldVideo->title = preg_replace('#\s{2,}#',' ',$oldVideo->title);

			// check for video title uniqueness accross user.
			// since title in audio also used as permalink alias,
			// we need to unsure the uniqueness of the title from a user.
			$check = true;
			$i = 0;

			do {
				if ($this->isTitleExists($oldVideo->title, $oldVideo->user_id, $oldVideo->id)) {
					$oldVideo->title = $oldVideo->title . '-' . ++$i;
					$check = true;
				} else {
					$check = false;
				}
			} while ($check);

			$oldVideo->store();
		}

		return true;
	}

	/**
	 * Method to check if video's title already exists or not
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function isTitleExists($title, $userId, $ignoreVideoId = 0)
	{
		$db = ES::db();

		$query = "select id from `#__social_videos`";
		$query .= " where `user_id` = " . $db->Quote($userId);
		$query .= " and `title` = " . $db->Quote($title);

		if ($ignoreVideoId) {
			$query .= " and `id` != " . $db->Quote($ignoreVideoId);
		}

		$db->setQuery($query);
		$found = $db->loadResult();

		return $found ? true : false;
	}

}
