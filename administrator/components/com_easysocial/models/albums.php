<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.application.component.model');

ES::import('admin:/includes/model');

class EasySocialModelAlbums extends EasySocialModel
{
	function __construct($config = array())
	{
		parent::__construct('albums' , $config);
	}

	/**
	 * Populates the state
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function initStates()
	{
		$profile 	= $this->getUserStateFromRequest('profile');
		$group 		= $this->getUserStateFromRequest('group');
		$published	= $this->getUserStateFromRequest('published' , 'all');

		$this->setState('published' , $published);
		$this->setState('group'	, $group);
		$this->setState('profile'	, $profile);

		parent::initStates();
	}

	/**
	 * Retrieves list of albums for admin area
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getDataWithState()
	{
		$db = ES::db();

		// Get the query object
		$sql = $db->sql();

		$sql->select('#__social_albums' , 'a');
		$sql->column('a.*');
		$sql->column('COUNT(b.id)' , 'totalphotos');

		$sql->join('#__social_photos' , 'b');
		$sql->on('a.id' , 'b.album_id');

		$sql->group('a.id');

		// Determines if we should search for the title
		$search = $this->getState('search');

		if ($search) {
			$sql->where('a.title' , '%' . $search . '%' , 'LIKE' , 'OR');
			$sql->where('a.caption' , '%' . $search . '%' , 'LIKE' , 'OR');
		}

		// Determine the ordering
		$ordering = $this->getState('ordering');

		if ($ordering) {
			$direction = $this->getState('direction');
			$sql->order($ordering , $direction);
		}

		// We should only be picking up photos which are valid
		$sql->where('b.state' , SOCIAL_STATE_PUBLISHED);

		// Determine the pagination limit
		$limit = $this->getState('limit');

		if ($limit) {
			// Set the total number of items.
			$this->setTotal($sql->getSql() , true);

			// Get the list of users
			$result = parent::getData($sql->getSql() , true);
		} else {
			$db->setQuery($sql);
			$result = $db->loadObjectList();
		}

		$albums = array();

		foreach($result as $row) {
			$album = ES::table('Album');
			$album->bind($row);

			// Set custom attributes
			$album->totalphotos = $row->totalphotos;

			// Set album core name type
			$coreType = array(
					'COM_EASYSOCIAL_ALBUMS_CORE_ALBUM', // 0
					'COM_EASYSOCIAL_ALBUMS_CORE_AVATAR', // 1
					'COM_EASYSOCIAL_ALBUMS_CORE_COVER', // 2
					'COM_EASYSOCIAL_ALBUMS_CORE_STORY' // 3
				);

			$album->coreName = JText::_($coreType[$album->core]);

			$albums[] = $album;
		}

		return $albums;
	}

	/**
	 * Retrieves list of albums
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAlbums($uid = '', $type = '', $options = array())
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();
		$my = ES::user();
		$streamLib = ES::stream();

		$sort = isset($options['order']) ? $options['order'] : '';
		$privacy = isset($options['privacy']) ? $options['privacy'] : false;

		if (!$config->get('privacy.enabled')) {
			$privacy = false;
		}

		$query = 'select a.*';

		if ($sort == 'likes') {
			$likeCountColumn = "(select count(1) from `#__social_likes` as exb where exb.`uid` = a.`id` and exb.`type` = 'albums.user.create') as likes";
			$query .= ", $likeCountColumn";
		}

		$query .= ' from `#__social_albums` as a';

		$withCoversOnly	= isset($options['withCovers']) ? $options['withCovers'] : '';
		if ($withCoversOnly) {
			$query .= ' INNER JOIN ' . $db->nameQuote('#__social_photos') . ' as b';
			$query .= ' ON a.' . $db->nameQuote('cover_id') . ' = b.' . $db->nameQuote('id') ;
		}

		$favourite = isset($options['favourite']) ? $options['favourite'] : '';
		if ($favourite) {
			$query .= ' INNER JOIN ' . $db->nameQuote('#__social_albums_favourite') . ' as fa';
			$query .= ' ON a.' . $db->nameQuote('id') . ' = fa.' . $db->nameQuote('album_id') ;
			$query .= ' AND fa.' . $db->nameQuote('user_id') . ' = ' . $db->Quote($options['userFavourite']) ;
		}

		$excludeblocked = isset($options['excludeblocked']) ? $options['excludeblocked'] : 0;
		if (ES::config()->get('users.blocking.enabled') && $excludeblocked && !JFactory::getUser()->guest) {
			// user block
			$query .= ' LEFT JOIN ' . $db->nameQuote('#__social_block_users') . ' as bus';

			$query .= ' ON (';
			$query .= ' a.' . $db->nameQuote( 'user_id' ) . ' = bus.' . $db->nameQuote( 'user_id' ) ;
			$query .= ' AND bus.' . $db->nameQuote( 'target_id' ) . ' = ' . $db->Quote( JFactory::getUser()->id );
			$query .= ') OR (';
			$query .= ' a.' . $db->nameQuote( 'user_id' ) . ' = bus.' . $db->nameQuote( 'target_id' ) ;
			$query .= ' AND bus.' . $db->nameQuote( 'user_id' ) . ' = ' . $db->Quote( JFactory::getUser()->id ) ;
			$query .= ')';

		}

		if (ES::config()->get('users.blocking.enabled') && $excludeblocked && !JFactory::getUser()->guest) {
			$query .= ' AND bus.' . $db->nameQuote('id') . ' IS NULL';
		}

		// We don't want albums that belongs to blocked user being displayed
		if (isset($options['excludedisabled']) && $options['excludedisabled']) {
			$query .= ' INNER JOIN `#__users` AS uu ON uu.`id` = a.' . $db->nameQuote('user_id') . ' AND uu.`block` = ' . $db->Quote('0');
		}

		$wheres = array();

		if ($uid) {
			$wheres[] = ' a.`uid` = ' . $db->Quote($uid);
		}

		if ($type) {
			$wheres[] = ' a.`type` = ' . $db->Quote($type);
		}

		$exclusion 	= isset($options['exclusion']) ? $options['exclusion'] : '';

		if ($exclusion) {
			$exclusion 	= FD::makeArray($exclusion);
			$wheres[] = ' a.id not in (' . implode(',', $exclusion) . ')';
		}

		// if present, we want to filter albums for this particular user only
		$userId	= isset($options['userId']) ? $options['userId'] : false;
		if ($userId) {
			$wheres[] = ' a.`user_id` = ' . $db->Quote($userId);
		}

		// If present, we want to retrieve album that is not belong to this user
		$othersAlbum = isset($options['othersAlbum']) ? $options['othersAlbum'] : '';
		if ($othersAlbum) {
			$wheres[] = ' a.`user_id` != ' . $db->Quote($my->id);
		}

		// if present, we want to filter this particular album only
		$albumId = isset($options['albumId']) ? $options['albumId'] : false;
		if ($albumId) {
			$wheres[] = ' a.`id` = ' . $db->Quote($albumId);
		}

		// Determine if we should include the core albums
		$coreAlbums = isset($options['core']) ? $options['core'] : true;
		if (!$coreAlbums) {
			$wheres[] = ' a.`core` = ' . $db->Quote('0');
		}

		$coreAlbumsOnly	= isset($options['coreAlbumsOnly']) ? $options['coreAlbumsOnly'] : '';
		if ($coreAlbumsOnly) {
			$wheres[] = ' a.`core` > 0';
		}

		$coverAlbumsOnly = isset($options['coverAlbumsOnly']) ? $options['coverAlbumsOnly'] : '';
		if ($coverAlbumsOnly) {
			$wheres[] = ' a.`core` = ' . $db->Quote(SOCIAL_ALBUM_PROFILE_COVERS);
		}

		$avatarAlbumsOnly = isset($options['avatarAlbumsOnly']) ? $options['avatarAlbumsOnly'] : '';
		if ($avatarAlbumsOnly) {
			$wheres[] = ' a.`core` = ' . $db->Quote(SOCIAL_ALBUM_PROFILE_PHOTOS);
		}

		// Check for albums privacy
		if ($privacy && !$my->isSiteAdmin()) {

			// privacy start here.
			$privacyQuery = ' (';

			// public
			$privacyQuery .= ' (a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_PUBLIC) . ') OR (a.`access` IS NULL) OR ';

			// member
			$privacyQuery .= ' ((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_MEMBER) . ') AND (' . $my->id . ' > 0)) OR ';

			if ($config->get('friends.enabled')) {
				// friends of friends
				$privacyQuery .= ' ((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ') AND ((' . $streamLib->generateMutualFriendSQL($my->id, 'a.`user_id`') . ') > 0)) OR ';

				// friends of friends
				$privacyQuery .= ' ((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ') AND ((' . $streamLib->generateIsFriendSQL('a.`user_id`', $my->id) . ') > 0)) OR ';

				// friends
				$privacyQuery .= ' ((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND ((' . $streamLib->generateIsFriendSQL('a.`user_id`', $my->id) . ') > 0)) OR ';
			} else {
				// fall back to member
				$privacyQuery .= ' ((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ') AND (' . $my->id . ' > 0)) OR ';
				$privacyQuery .= ' ((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND (' . $my->id . ' > 0)) OR ';
			}

			// only me
			$privacyQuery .= ' ((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_ONLY_ME) . ') AND (a.`user_id` = ' . $my->id . ')) OR ';

			// custom
			$privacyQuery .= "( (a.`access` = " . $db->Quote(SOCIAL_PRIVACY_CUSTOM) . ") AND ( a.`custom_access` LIKE " . $db->Quote( '%,' . $my->id . ',%' ) . " ) ) OR ";

			// field
			if ($config->get('users.privacy.field')) {
				// field
				$fieldPrivacyQuery = '(select count(1) from `#__social_privacy_items_field` as fa';
				$fieldPrivacyQuery .= ' inner join `#__social_fields` as ff on fa.`unique_key` = ff.`unique_key`';
				$fieldPrivacyQuery .= ' inner join `#__social_fields_data` as fd on ff.`id` = fd.`field_id`';
				$fieldPrivacyQuery .= ' where fa.`uid` = a.`id`';
				$fieldPrivacyQuery .= ' and fd.`uid` = ' . $db->Quote($my->id);
				$fieldPrivacyQuery .= ' and fd.`type` = ' . $db->Quote('user');
				$fieldPrivacyQuery .= ' and fd.`raw` LIKE concat(' . $db->Quote('%') . ',fa.`value`,' . $db->Quote('%') . '))';

				$privacyQuery .= ' ((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FIELD) . ') AND (a.`field_access` <= ' . $fieldPrivacyQuery . ')) OR ';
			} else {
				$privacyQuery .= ' ((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FIELD) . ') AND (' . $my->id . ' > 0)) OR ';
			}

			// viewer items
			$privacyQuery .= ' (a.`user_id` = ' . $my->id . ')';

			// privacy ended here
			$privacyQuery .= ')';

			// Additional privacy query for clusters privacy
			$clusterPrivacyQuery = ' (';

			// Check if the cluster is private and current viewer is a member of this cluster
			$clusterPrivacyQuery .= ' a.`uid` NOT IN(';
			$clusterPrivacyQuery .= ' select sc.`id` from `#__social_clusters` as sc';
			$clusterPrivacyQuery .= ' WHERE (';
			$clusterPrivacyQuery .= ' sc.`type` NOT IN(' . $db->Quote(1) . ', ' . $db->Quote(4) . ')';
			$clusterPrivacyQuery .= ' AND ' . $db->Quote($my->id) . ' NOT IN(';
			$clusterPrivacyQuery .= ' select scn.`uid` from `#__social_clusters_nodes` as scn where scn.`cluster_id` = sc.`id` and scn.`state` = ' . $db->Quote(1) . ')))';

			// End of cluster privacy
			$clusterPrivacyQuery .= ')';

			$wheres[] = $privacyQuery;
			$wheres[] = $clusterPrivacyQuery;
		}

		$where = '';

		if (count($wheres) > 0) {
			$where = ' where ';
			$where .= (count($wheres) == 1) ? $wheres[0] : implode(' and ', $wheres);
		}

		$query .= $where;

		// prepare sql for counter
		$counterSQL = $query;

		// now we add ordering if there is any
		$orderby = '';
		$ordering = isset($options['order']) ? $options['order'] : '';

		if ($ordering) {
			$direction = isset($options['direction']) ? $options['direction'] : 'desc';

			if ($ordering == 'likes') {
				$orderby .= ' order by `likes` ' . $direction;
			} else {
				$orderby .= ' order by ' . $ordering . ' ' . $direction;
			}
		}

		$query .= $orderby;

		// echo $query;

		$pagination = isset($options['pagination']) ? $options['pagination'] : false;

		if ($pagination) {

			$this->setTotal($counterSQL, true);

			$result = $this->getData($query);
		} else {
			// Check for limit
			$limit = isset($options['limit']) ? $options['limit'] : '';
			$startlimit = isset($options['startlimit']) ? $options['startlimit'] : '';
			$endlimit = isset($options['endlimit']) ? $options['endlimit'] : '';

			if (!$startlimit && $limit) {
				$query .= ' LIMIT ' . $limit;

			} elseif ($startlimit && $endlimit) {
				$query .= ' LIMIT ' . $startlimit . ', ' . $endlimit;
			}

			$db->setQuery($query);

			$result = $db->loadObjectList();
		}

		if (!$result) {
			return $result;
		}

		$albums = array();

		$privacyLib = FD::privacy(FD::user()->id);

		foreach ($result as $row) {
			$album = ES::table('Album');
			$album->bind($row);

			$albums[] = $album;
		}

		return $albums;
	}

	/**
	 * Creates a default album
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function createDefaultAlbum($uid, $type, $defaultType)
	{
		$album = ES::table('Album');

		if ($defaultType == SOCIAL_ALBUM_PROFILE_PHOTOS) {
			$album->title = 'COM_EASYSOCIAL_ALBUMS_PROFILE_AVATAR';
			$album->caption	= 'COM_EASYSOCIAL_ALBUMS_PROFILE_AVATAR_DESC';
		}

		if ($defaultType == SOCIAL_ALBUM_PROFILE_COVERS) {
			$album->title = 'COM_EASYSOCIAL_ALBUMS_PROFILE_COVER';
			$album->caption	= 'COM_EASYSOCIAL_ALBUMS_PROFILE_COVER_DESC';
		}

		if ($defaultType == SOCIAL_ALBUM_STORY_ALBUM) {
			$album->title = 'COM_EASYSOCIAL_ALBUMS_STORY_PHOTOS';
			$album->caption	= 'COM_EASYSOCIAL_ALBUMS_STORY_PHOTOS_DESC';
		}

		$album->uid = $uid;
		// This might not work if admin creates default album for another user
		$album->user_id = ES::user()->id;
		$album->type = $type;
		$album->core = $defaultType;

		$album->store();

		return $album;
	}

	/**
	 * Retrieves the default album for a particular node
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The node id.
	 * @param	string	The node type.
	 * @param	string	The album type.
	 * @return	SocialTableAlbum
	 */
	public function getDefaultAlbum($uid, $type, $albumType)
	{
		$exists = $this->hasDefaultAlbum($uid, $type, $albumType);

		if (!$exists) {
			return $this->createDefaultAlbum($uid, $type, $albumType);
		}

		$album = ES::table('Album');
		$album->load(array('uid' => $uid, 'type' => $type, 'core' => $albumType));

		return $album;
	}

	/**
	 * Determines if there is a default album created for a given user
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function hasDefaultAlbum($uid, $type, $defaultType)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_albums');
		$sql->column('COUNT(1)', 'total');
		$sql->where('core', $defaultType);
		$sql->where('uid', $uid);
		$sql->where('type', $type);

		$db->setQuery($sql);

		$exists = $db->loadResult() >= 1;

		return $exists;
	}

	/**
	 * Determines if this album is already favourite
	 *
	 * @since	1.4
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function isFavourite($albumId, $userId)
	{
		$db = FD::db();
		$sql = $db->sql();

		$sql->select('#__social_albums_favourite');
		$sql->column('COUNT(1)', 'total');
		$sql->where('album_id', $albumId);
		$sql->where('user_id', $userId);

		$db->setQuery($sql);

		$exists = $db->loadResult() >= 1;

		return $exists;
	}

	/**
	 * Remove album from favourite
	 *
	 * @since	1.4
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function removeFavourite($albumId, $userId)
	{
		$db = FD::db();
		$sql = $db->sql();

		$sql->delete('#__social_albums_favourite');
		$sql->where('album_id', $albumId);
		$sql->where('user_id', $userId);

		$db->setQuery($sql);
		return $db->Query();
	}

	/**
	 * Add album as favourite
	 *
	 * @since	1.4
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function addFavourite($albumId, $userId)
	{
		$db = FD::db();
		$sql = $db->sql();

		$sql->insert('#__social_albums_favourite');
		$sql->values('album_id' , $albumId);
		$sql->values('user_id' , $userId);

		$db->setQuery($sql);
		return $db->Query();
	}

	/**
	 * Retrieve the number of tags in this album
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getTotalTags($id , $userOnly = false)
	{
		$db		= FD::db();

		$sql	= $db->sql();

		$sql->select('#__social_photos_tag' , 'a');
		$sql->column('COUNT(1)');
		$sql->join('#__social_photos' , 'b' , 'INNER');
		$sql->on('a.photo_id' , 'b.id');
		$sql->where('b.album_id' , $id);

		// Determines if we need to fetch tags that are associated with real users only.
		if($userOnly)
		{
			$sql->where('a.type' , 'person');
			$sql->where('a.uid' , '0' , '!=');
		}

		$db->setQuery($sql);

		$result 	= $db->loadResult();

		return $result;
	}

	/**
	 * Retrieve a list of tags that are used in a particular album
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getTags($id , $userOnly = false, $max = 0)
	{
		$db		= FD::db();

		$sql	= $db->sql();

		$sql->select('#__social_photos_tag' , 'a');
		$sql->column('a.*');
		$sql->join('#__social_photos' , 'b' , 'INNER');
		$sql->on('a.photo_id' , 'b.id');
		$sql->where('b.album_id' , $id);

		// Determines if we need to fetch tags that are associated with real users only.
		if($userOnly)
		{
			$sql->where('a.type' , 'person');
			$sql->where('a.uid' , '0' , '!=');

			$sql->group('a.type');
			$sql->group('a.uid');
		}

		if ($max) {
			$sql->limit($max);
		}

		$db->setQuery($sql);
		$result 	= $db->loadObjectList();

		if(!$result)
		{
			return $result;
		}

		$tags 	= array();

		foreach($result as $row)
		{
			$tag 	= FD::table('PhotoTag');
			$tag->bind($row);

			$tags[]	= $tag;
		}

		return $tags;
	}

	/**
	 * Retrieves the total number of photos created within an album
	 *
	 * @since	1.0
	 * @access	public
	 * @return	int
	 */
	public function getTotalPhotos($albumId)
	{
		$db = ES::db();

		$privacy = true;
		$config = ES::config();

		$app = JFactory::getApplication();
		$type = $app->input->get('type', '', 'default');

		$my = ES::user();

		if (!$config->get('privacy.enabled') || $my->isSiteAdmin()) {
			$privacy = false;
		}

		$query = "select count(1)";
		$query .= " from `#__social_photos` as a";
		$query .= " where a.`state` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);
		$query .= " and a.`album_id` = " . $db->Quote($albumId);

		// access
		if ($privacy && (!$type || $type == SOCIAL_TYPE_USER)) {

			$viewer = $my->id;

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

			// attache privacy query into main query.
			$query .= "and " . $tmp;
		}

		$db->setQuery($query);
		$total = $db->loadResult();

		return $total;
	}


	/**
	 * Generate query used in friends privacy
	 *
	 * @since	3.1
	 * @access	public
	 * @return	int
	 */
	public function generateIsFriendSQL($source, $target)
	{
		$query = "select count(1) from `#__social_friends` where ( `actor_id` = $source and `target_id` = $target) OR (`target_id` = $source and `actor_id` = $target) and `state` = 1";
		return $query;
	}

	/**
	 * Retrieves the latest photos created within an album
	 *
	 * @since	2.0
	 * @access	public
	 * @return	int
	 */
	public function getLastPhoto($albumId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_photos');
		$sql->where('state' , SOCIAL_STATE_PUBLISHED);
		$sql->where('album_id' , $albumId);
		$sql->order('created', 'DESC');
		$sql->limit(1);

		$db->setQuery($sql);

		$photo = $db->loadObject();

		return $photo;
	}

	/**
	 * Retrieves the total number of albums created on the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalAlbums($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_albums');
		$sql->column('COUNT(1)', 'total');

		$uid = $this->normalize($options, 'uid', '');
		$type = $this->normalize($options, 'type', '');

		if ($uid && $type) {
			$sql->where('uid', $uid);
			$sql->where('type', $type);
		}

		// Determines if we should exclude core albums
		$excludeCore = $this->normalize($options, 'excludeCore', false);

		if ($excludeCore) {
			$sql->where('core', 0);
		}

		$db->setQuery($sql);
		$total = $db->loadResult();

		return $total;
	}

	public function getStreamId($albumId)
	{
		$db		= FD::db();

		// Get a list of items from the item table first.
		$sql	= $db->sql();

		$query = "select a.`id` from `#__social_stream` as a";
		$query .= " where a.`context_type` = " . $db->Quote('photos');
		$query .= " and a.`verb` IN ('create', 'add')";
		$query .= " and a.`target_id` = " . $db->Quote($albumId);
		$query .= " order by a.`id` desc limit 1";

		$sql->raw($query);
		$db->setQuery($sql);

		$id = $db->loadResult();

		return $id;
	}

	public function getFavouriteParticipants($albumId)
	{
		$db		= FD::db();

		// Get a list of items from the item table first.
		$sql	= $db->sql();

		$sql->select('#__social_albums_favourite');
		$sql->column('user_id');
		$sql->where('album_id' , $albumId);

		$db->setQuery($sql);

		$item = $db->loadColumn();

		return $item;
	}

	/**
	 * Method to check if album's title already exists or not
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function isTitleExists($title, $userId, $ignoreAlbumId = 0)
	{
		$db = ES::db();

		$query = "select id from `#__social_albums`";
		$query .= " where `user_id` = " . $db->Quote($userId);
		// we only check albums created by user
		$query .= " and `core` = 0";
		$query .= " and `title` = " . $db->Quote($title);

		if ($ignoreAlbumId) {
			$query .= " and `id` != " . $db->Quote($ignoreAlbumId);
		}

		$db->setQuery($query);
		$found = $db->loadResult();

		return $found ? true : false;
	}
}
