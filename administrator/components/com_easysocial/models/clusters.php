<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

jimport('joomla.application.component.model');

FD::import( 'admin:/includes/model' );

class EasySocialModelClusters extends EasySocialModel
{
	public function __construct( $config = array() )
	{
		parent::__construct( 'clusters' , $config );
	}

	/**
	 * Initializes all the generic states from the form
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function initStates()
	{
		$filter 	= $this->getUserStateFromRequest( 'state' , 'all' );
		$ordering 	= $this->getUserStateFromRequest( 'ordering' , 'ordering' );
		$direction	= $this->getUserStateFromRequest( 'direction' , 'ASC' );

		$this->setState( 'state' , $filter );


		parent::initStates();

		// Override the ordering behavior
		$this->setState( 'ordering' , $ordering );
		$this->setState( 'direction' , $direction );
	}

	/**
	 * Saves the ordering of profiles
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function saveOrder( $ids , $ordering )
	{
		$table 	= FD::table( 'Profile' );
		$table->reorder();
	}

	/**
	 * Removes all owners from the nodes
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function removeOwners($clusterId, $adminRights = true)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->update('#__social_clusters_nodes');
		$sql->set('owner', 0);

		if (!$adminRights) {
			$sql->set('admin', 0);
		}

		$sql->where('cluster_id', $clusterId);

		$db->setQuery( $sql );

		return $db->Query();
	}

	/**
	 * Given the cluster id, get the type of the cluster
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getType($id)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters');
		$sql->column('cluster_type', 'type');
		$sql->where('id', $id);

		$db->setQuery($sql);
		$type = $db->loadResult();

		return $type;
	}

	/**
	 * Retrieves the total number of clusters created by a user given the cluster type and the user id
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The user's id.
	 * @param	string	The cluster type
	 * @return
	 */
	public function getTotalCreated( $creatorId , $creatorType , $clusterType )
	{
		$db 	= FD::db();
		$sql	= $db->sql();

		$sql->select( '#__social_clusters' );
		$sql->column( 'COUNT(1)' );
		$sql->where( 'creator_uid' , $creatorId );
		$sql->where( 'creator_type' , $creatorType );
		$sql->where( 'cluster_type' , $clusterType );

		$sql->where( 'state' , SOCIAL_CLUSTER_PUBLISHED );
		$db->setQuery( $sql );

		$total	= $db->loadResult();

		return $total;
	}

	/**
	 * Deletes all node associations between the cluster and the node item
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function deleteNodeAssociation( $clusterId )
	{
		$db 	= FD::db();
		$sql 	= $db->sql();

		$sql->delete( '#__social_clusters_nodes' );
		$sql->where( 'cluster_id' , $clusterId );

		$db->setQuery( $sql );

		$state 	= $db->Query();

		return $state;
	}

	/**
	 * Deletes node
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function deleteNode($clusterId, $nodeId, $nodeType)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->delete('#__social_clusters_nodes');
		$sql->where('cluster_id', $clusterId);
		$sql->where('uid', $nodeId);
		$sql->where('type', $nodeType);

		$db->setQuery($sql);

		$state = $db->Query();

		return $state;
	}

	/**
	 * Gets the total number of nodes in a cluster category
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The cluster's category id.
	 * @return
	 */
	public function getTotalNodes( $categoryId , $options = array() )
	{
		$db 	= FD::db();
		$sql 	= $db->sql();

		$sql->select( '#__social_clusters' , 'a' );
		$sql->column( 'COUNT(1)' );

		$excludeBlocked 	= isset( $options[ 'excludeblocked' ] ) ? $options[ 'excludeblocked' ] : 0;

		if (FD::config()->get('users.blocking.enabled') && $excludeBlocked && !JFactory::getUser()->guest) {
			$sql->leftjoin( '#__social_block_users' , 'bus');

			$sql->on('(');
			$sql->on( 'a.created_by' , 'bus.user_id' );
			$sql->on( 'bus.target_id', JFactory::getUser()->id);
			$sql->on(')');

			$sql->on('(', '', '', 'OR');
			$sql->on( 'a.created_by' , 'bus.target_id' );
			$sql->on( 'bus.user_id', JFactory::getUser()->id );
			$sql->on(')');

			$sql->isnull('bus.id');
		}

		$sql->where( 'a.category_id' , $categoryId );
		$sql->where( 'a.state' , SOCIAL_STATE_PUBLISHED );

		// Determines if the type is provided
		$types 	= isset( $options[ 'types' ] ) ? $options[ 'types' ] : '';

		if( $types )
		{
			$types 	= FD::makeArray( $types );

			$sql->where( 'a.type' , $types , 'IN' );
		}

		$db->setQuery( $sql );

		$total 	= $db->loadResult();

		return $total;
	}

	/**
	 * Check if the cluster alias exist
	 *
	 * @since  1.2
	 * @access public
	 */
	public function clusterAliasExists($alias, $exclude = null, $type = SOCIAL_TYPE_GROUP)
	{
		$db = FD::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters');
		$sql->where('alias', $alias);
		$sql->where('cluster_type', $type);

		if (!empty($exclude)) {
			$sql->where('id', $exclude, '!=');
		}

		$db->setQuery($sql->getTotalSql());

		$result = $db->loadResult();

		return !empty($result);
	}

	/**
	 * Check if cluster title exist
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function clusterTitleExists($title, $type, $clusterId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters');
		$sql->where('title', $title);
		$sql->where('cluster_type', $type);

		if ($clusterId) {
			$sql->where('id', $clusterId, '!=');
		}

		$db->setQuery($sql->getTotalSql());
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * Generates a unique title
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getUniqueTitle($title, $type, $clusterId = false)
	{
		$config = ES::config();

		if ($config->get('seo.clusters.allowduplicatetitle')) {
			return $title;
		}

		$i = 2;

		$tmp = $title;

		do {
			$exists = $this->clusterTitleExists($title, $type, $clusterId);

			if ($exists) {
				$title = $tmp . ' ' . $i++;
			}
		} while ($exists);

		return $title;
	}

	/**
	 * Check if the cluster category alias exist
	 *
	 * @since  1.2
	 * @access public
	 */
	public function clusterCategoryAliasExists($alias, $exclude = null)
	{
		$db = FD::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters_categories');
		$sql->where('alias', $alias);

		if (!empty($exclude))
		{
			$sql->where('id', $exclude, '!=');
		}

		$db->setQuery($sql->getTotalSql());

		$result = $db->loadResult();

		return !empty($result);
	}

	/**
	 * Delete activity streams from the cluster
	 *
	 * @since  3.0.0
	 * @access public
	 */
	public function deleteClusterStream($clusterId, $clusterType)
	{
		$db = ES::db();

		$query = "delete a, b, c, d";
		$query .= " from `#__social_stream` as a";
		$query .= " left join `#__social_stream_item` as d on a.`id` = d.`uid`";
		// remove all associated comments;
		$query .= " left join `#__social_comments` as b on a.`id` = b.`stream_id`";
		// remove all associated reaction;
		$query .= " left join `#__social_likes` as c on a.`id` = c.`stream_id`";
		$query .= " where a.`cluster_id` = " . $db->Quote($clusterId);
		$query .= " and a.`cluster_type` = " . $db->Quote($clusterType);

		$db->setQuery($query);
		$db->query();

		// now we delete any 'left overs' reactions on those deleted comments
		$query = "delete a";
		$query .= " from `#__social_likes` as a";
		$query .= "	left join `#__social_comments` as b on a.`uid` = b.`id`";
		$query .= " where a.`type` like " . $db->Quote('comments.%');
		$query .= " and b.`id` is null";

		$db->setQuery($query);
		$db->query();
	}

	/**
	 * delete notifications from this cluster.
	 *
	 * @since  1.2
	 * @access public
	 */
	public function deleteClusterNotifications($clusterId, $clusterType, $clusterContextType)
	{
		$db = FD::db();
		$sql = $db->sql();

		$query = 'delete from `#__social_notifications`';
		$query .= ' where (`uid` = ' . $db->Quote($clusterId) . ' and `type` = ' . $db->Quote($clusterType) .')';
		$query .= ' OR (`type` = ' . $db->Quote($clusterContextType) . ' and `context_ids` = ' . $db->Quote($clusterId) . ')';

		$sql->raw($query);
		$db->setQuery($sql);

		$state = $db->query();
		return $state;
	}

	public function preloadClusters($clusters)
	{
		$db = FD::db();
		$sql = $db->sql();

		$query = "select * from `#__social_clusters` where id in (" . implode(",", $clusters) . ")";

		$sql->raw($query);

		$db->setQuery($sql);

		$results = $db->loadObjectList();
		return $results;
	}

	/**
	 * Determines if the user is a member of the cluster
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isMember($userId, $clusterId)
	{
		$db 	= FD::db();

		$sql	= $db->sql();

		$sql->select('#__social_clusters_nodes');
		$sql->column('COUNT(1)');
		$sql->where('uid', $userId);
		$sql->where('type', SOCIAL_TYPE_USER);
		$sql->where('cluster_id', $clusterId);
		$sql->where('state', SOCIAL_GROUPS_MEMBER_PUBLISHED);

		$db->setQuery($sql);

		$isMember 	= $db->loadResult() > 0;

		return $isMember;
	}

	/**
	 * Determines if the user is a member of the cluster
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalMembers($clusterId, $options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters_nodes', 'a');
		$sql->column('COUNT(1)');

		if (ES::config()->get('users.blocking.enabled') && !JFactory::getUser()->guest) {
			$sql->leftjoin('#__social_block_users', 'bus');

			$sql->on('(');
			$sql->on( 'a.uid' , 'bus.user_id' );
			$sql->on( 'bus.target_id', JFactory::getUser()->id);
			$sql->on(')');

			$sql->on('(', '', '', 'OR');
			$sql->on( 'a.uid' , 'bus.target_id' );
			$sql->on( 'bus.user_id', JFactory::getUser()->id );
			$sql->on(')');

			$sql->isnull('bus.id');
		}

		// We should not fetch banned users
		$sql->join('#__users', 'u');
		$sql->on('a.uid', 'u.id');

		// exclude esad users
		$sql->innerjoin('#__social_profiles_maps', 'upm');
		$sql->on('a.uid', 'upm.user_id');

		$sql->innerjoin('#__social_profiles', 'up');
		$sql->on('upm.profile_id', 'up.id');
		$sql->on('up.community_access', '1');

		$sql->where('a.cluster_id', $clusterId);
		$sql->where('a.state', SOCIAL_STATE_PUBLISHED);

		// Whe the user isn't blocked
		$sql->where('u.block', 0);

		$membersOnly = isset($options['membersOnly']) ? $options['membersOnly'] : false;

		if ($membersOnly) {
			$sql->where('a.admin', '0');
		}

		$db->setQuery($sql);
		$count = (int) $db->loadResult();

		return $count;
	}


	/**
	 * get number of cluster a user need to review.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalPendingReview($userId, $clusterType)
	{
		$db = FD::db();
		$sql = $db->sql();

		$query = "select count(1) from `#__social_clusters` as a";
		// $query .= "		inner join `#__social_clusters_reject` as b on a.`id` = b.`cluster_id`";
		$query .= " where a.`creator_uid` = " . $db->Quote($userId);
		$query .= " and a.`creator_type` = " . $db->Quote(SOCIAL_TYPE_USER);
		$query .= " and a.`cluster_type` = " . $db->Quote($clusterType);
		$query .= " and a.`state` = " . $db->Quote(SOCIAL_CLUSTER_DRAFT);

		$sql->raw($query);

		$db->setQuery($sql);
		$count = (int) $db->loadResult();

		return $count;
	}

	/**
	 * get number of cluster that pending moderation.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getTotalPendingModeration($options)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "select count(1) from `#__social_clusters` as a";
		$query .= " where a.`creator_type` = " . $db->Quote(SOCIAL_TYPE_USER);

		if (isset($options['filter']) && $options['filter'] != 'all') {
			$query .= " and a.`cluster_type` = " . $db->Quote($options['filter']);
		}

		$query .= " and a.`state` IN(" . $db->Quote(SOCIAL_CLUSTER_PENDING) . ", " . $db->Quote(SOCIAL_CLUSTER_UPDATE_PENDING) . ")";

		$sql->raw($query);

		$db->setQuery($sql);
		$count = (int) $db->loadResult();

		return $count;
	}

	/**
	 * Get cluster that in pending moderation.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getPendingModeration($options)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "select * from `#__social_clusters` as a";
		$query .= " where a.`creator_type` = " . $db->Quote(SOCIAL_TYPE_USER);

		if (isset($options['filter']) && $options['filter'] != 'all') {
			$query .= " and a.`cluster_type` = " . $db->Quote($options['filter']);
		}

		$query .= " and a.`state` IN(" . $db->Quote(SOCIAL_CLUSTER_PENDING) . ", " . $db->Quote(SOCIAL_CLUSTER_UPDATE_PENDING) . ")";

		$sql->raw($query);

		$db->setQuery($sql);
		$results = $db->loadObjectList();

		$clusters = array();
		// Load the cluster library
		foreach ($results as $result) {
			$clusters[] = ES::cluster($result->cluster_type, $result->id);
		}

		return $clusters;
	}

	/**
	 * retrieve the cluster's rejected reason.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getRejectedReasons($clusterId)
	{
		$db = FD::db();
		$sql = $db->sql();

		$query = "select b.* from `#__social_clusters` as a";
		$query .= "		inner join `#__social_clusters_reject` as b on a.`id` = b.`cluster_id`";
		$query .= " where a.`id` = " . $db->Quote($clusterId);
		$query .= " and a.`state` = " . $db->Quote(SOCIAL_CLUSTER_DRAFT);
		$query .= " order by b.`id` desc";

		$sql->raw($query);
		$db->setQuery($sql);

		$results = $db->loadObjectList();
		return $results;
	}


	/**
	 * Searches for a user's friend.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function searchNodes($clusterId, $term, $type, $options = array())
	{
		$config = FD::config();
		$db	 = FD::db();

		$query = "select a." . $db->nameQuote('id') . " from " . $db->nameQuote('#__users') . " as a";
		$query .= " inner join " . $db->nameQuote('#__social_clusters_nodes') . " as b on a.`id` = b.`uid` and b.`type` = 'user'";

		if ($config->get('users.blocking.enabled') && !JFactory::getUser()->guest) {
			// user block
			$query .= " LEFT JOIN " . $db->nameQuote( '#__social_block_users' ) . " as bus";

			$query .= ' ON (';
			$query .= ' a.' . $db->nameQuote( 'id' ) . ' = bus.' . $db->nameQuote( 'user_id' ) ;
			$query .= ' AND bus.' . $db->nameQuote( 'target_id' ) . ' = ' . $db->Quote( JFactory::getUser()->id );
			$query .= ') OR (';
			$query .= ' a.' . $db->nameQuote( 'id' ) . ' = bus.' . $db->nameQuote( 'target_id' ) ;
			$query .= ' AND bus.' . $db->nameQuote( 'user_id' ) . ' = ' . $db->Quote( JFactory::getUser()->id ) ;
			$query .= ')';
		}

		$query .= " where a." . $db->nameQuote('block') ." = " . $db->Quote('0');
		$query .= "	and b." . $db->nameQuote('state') . " = " . $db->Quote(SOCIAL_USER_STATE_ENABLED);
		$query .= "	and b." . $db->nameQuote('cluster_id') . " = " . $db->Quote($clusterId);


		if ($config->get('users.blocking.enabled') && !JFactory::getUser()->guest) {
			// user block continue here
			$query .= " AND bus." . $db->nameQuote('id') . " IS NULL";
		}

		if ($type == SOCIAL_FRIENDS_SEARCH_NAME || $type == SOCIAL_FRIENDS_SEARCH_REALNAME) {
			$query .= " AND a." . $db->nameQuote('name') . " LIKE " . $db->Quote('%' . $term . '%');
		}

		if ($type == SOCIAL_FRIENDS_SEARCH_USERNAME) {
			$query .= " AND a." . $db->nameQuote('username') . " LIKE " . $db->Quote('%' . $term . '%');
		}

		if (isset($options['exclude'] ) && $options['exclude']) {
			$excludeIds = '';

			if (!is_array($options['exclude'])) {
				$options['exclude'] = explode(',', $options['exclude']);
			}

			foreach ($options['exclude']  as $id) {
				$excludeIds .= ( empty( $excludeIds ) ) ? $db->Quote( $id ) : ', ' . $db->Quote( $id );
			}

			$query .= " AND a." . $db->nameQuote('id') . " NOT IN (" . $excludeIds . ")";
		}

		$limit = isset($options['limit']) ? $options['limit'] : false;
		$limitstart = isset($options['limitstart']) ? $options['limitstart'] : '10';

		if ($limit) {

			// get the total count.
			$replaceStr = "SELECT a." . $db->nameQuote('id') . " FROM ";
			$totalSQL = str_replace($replaceStr, "SELECT COUNT(1) FROM ", $query);

			$db->setQuery($totalSQL);
			$this->total = $db->loadResult();

			// now we append the limit
			$query .= " LIMIT $limitstart, $limit";
		}

		$db->setQuery($query);
		$result = $db->loadColumn();

		if (!$result) {
			return false;
		}

		$members = FD::user($result);

		return $members;
	}

	public function deleteUserStreams($clusterId, $clusterType, $userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "delete a, b from `#__social_stream` as a";
		$query .= "		inner join `#__social_stream_item` as b on a.`id` = b.`uid`";
		$query .= " where a.`actor_id` = " . $db->Quote($userId);
		$query .= " and a.`cluster_id` = " . $db->Quote($clusterId);
		$query .= " and a.`cluster_type` = " . $db->Quote($clusterType);

		$sql->raw($query);
		$db->setQuery($sql);

		$db->query();

		return true;
	}

	public function getFilters($clusterId, $clusterType, $userId = '')
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select * from `#__social_stream_filter`';
		$query .= ' where `uid` = ' . $db->Quote($clusterId);
		$query .= ' and `utype` = ' . $db->Quote($clusterType);

		// Always search for global
		$query .= ' and `global` = ' . $db->Quote(1);

		if ($userId) {
			$query .= ' and `user_id` = ' . $db->Quote($userId);
		}

		$sql->raw($query);
		$db->setQuery($sql);

		$result = $db->loadObjectList();

		$items = array();

		if ($result) {
			foreach ($result as $row) {
				$streamFilter = ES::table('StreamFilter');
				$streamFilter->bind($row);

				$items[] = $streamFilter;
			}
		}

		return $items;
	}


	/**
	 * update stream's cluster access
	 *
	 * @since  2.1
	 * @access public
	 */
	public function updateStreamClusterAccess($clusterId, $clusterType, $accessType)
	{
		$db = FD::db();
		$sql = $db->sql();

		$query = 'update `#__social_stream` set `cluster_access` = ' . $db->Quote($accessType);
		$query .= " where `cluster_id` = " . $db->Quote($clusterId);
		$query .= " and `cluster_type` = " . $db->Quote($clusterType);

		$sql->raw($query);
		$db->setQuery($sql);

		$state = $db->query();
		return $state;
	}

	/**
	 * Determine if this user was invited to join cluster
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function isInvited($userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_friends_invitations');
		$sql->column('uid', '', 'distinct');
		$sql->where('registered_id', $userId);
		$sql->where('utype', array(SOCIAL_TYPE_GROUP, SOCIAL_TYPE_PAGE, SOCIAL_TYPE_EVENT), 'IN');

		$db->setQuery($sql);
		$total = $db->loadColumn();

		return $total;
	}

}
