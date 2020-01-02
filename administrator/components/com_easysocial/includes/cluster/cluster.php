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
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/indexer/indexer');

abstract class SocialCluster
{
	/**
	 * The clusters's unique id.
	 * @var int
	 */
	public $id = null;

	/**
	 * The cluster category id.
	 * @var int
	 */
	public $category_id = null;

	/**
	 * The cluster type.
	 * @var int
	 */
	public $cluster_type = null;

	/**
	 * The creator unique id.
	 * @var int
	 */
	public $creator_uid = null;

	/**
	 * The creator type.
	 * @var int
	 */
	public $creator_type = null;

	/**
	 * The cluster's title.
	 * @var string
	 */
	public $title = null;

	/**
	 * The cluster's description.
	 * @var string
	 */
	public $description = null;

	/**
	 * The cluster's alias.
	 * @var string
	 */
	public $alias = null;

	/**
	 * The cluster's hits.
	 * @var string
	 */
	public $hits = null;

	/**
	 * The cluster's state.
	 * @var boolean
	 */
	public $state = null;

	/**
	 * The cluster's featured state.
	 * @var boolean
	 */
	public $featured = null;

	/**
	 * The cluster's creation date.
	 * @var string
	 */
	public $created = null;

	/**
	 * The cluster's type.
	 * @var string
	 */
	public $type = null;

	/**
	 * The cluster's notification.
	 * @var string
	 */
	public $notification = null;

	/**
	 * Stores the avatar sizes available on a cluster.
	 * @var string
	 */
	public $avatarSizes = array('small', 'medium', 'large', 'square');

	/**
	 * Stores the avatars of a cluster.
	 * @var string
	 */
	public $avatars = array('small' => '', 'medium' => '', 'large' => '', 'square' => '');

	/**
	 * Stores the cover photo of a cluster.
	 * @var string
	 */
	public $cover = null;

	/**
	 * Stores the params of a cluster.
	 * @var string
	 */
	public $params = null;

	/**
	 * The group's secret key.
	 * @var int
	 */
	public $key = null;

	/**
	 * Parent id of this cluster.
	 * @var integer
	 */
	public $parent_id = null;

	/**
	 * Parent type of this cluster.
	 * @var string
	 */
	public $parent_type = null;

	/**
	 * Longitude value of this cluster.
	 * @var float
	 */
	public $longitude = null;

	/**
	 * Latitude value of this cluster.
	 * @var float
	 */
	public $latitude = null;

	/**
	 * Address of this cluster.
	 * @var string
	 */
	public $address = null;

	/**
	 * The group's fields.
	 * @var Array
	 */
	public $fields = array();

	/**
	 * Stores the object mapping.
	 * @var SocialTableCluster
	 */
	protected $table = null;

	/**
	 * Determines the storage type for the avatars
	 * @var string
	 */
	protected $avatarStorage = 'joomla';

	protected $error = '';

	public function __construct()
	{
		$this->config = ES::config();
		$this->my = ES::user();
	}

	/**
	 * Initializes the provided properties into the existing object. Instead of
	 * trying to query to fetch more info about this cluster.
	 *
	 * @since   1.2
	 * @access  public
	 * @param   object  $params     A standard object with key / value binding.
	 */
	public function initParams(&$params)
	{
		// Get all properties of this object
		$properties = get_object_vars($this);

		// Bind parameters to the object
		foreach($properties as $key => $val) {
			if (isset($params->$key)) {
				$this->$key = $params->$key;
			}
		}

		// Bind params json object here
		$this->_params->loadString($this->params);

		// Bind user avatars here.
		foreach($this->avatars as $size => $value) {
			if (isset($params->$size)) {
				$this->avatars[$size] = $params->$size;
			}
		}
	}

	/**
	 * Increments the hit counter.
	 *
	 * @since   1.2
	 * @access  public
	 * @return  True if successful.
	 */
	public function hit()
	{
		return $this->table->hit();
	}

	/**
	 * Retrieves a list of apps for this cluster type.
	 *
	 * @since   1.2
	 * @access  public
	 * @return  array   Array of apps object.
	 */
	public function getApps()
	{
		static $apps = array();

		if (!isset($app[$this->cluster_type])) {
			$model = ES::model('Apps');
			$options = array('group' => $this->cluster_type, 'type' => SOCIAL_APPS_TYPE_APPS, 'state' => SOCIAL_STATE_PUBLISHED);
			$clusterApps = $model->getApps($options);

			$apps[$this->cluster_type] = $clusterApps;
		}

		return $apps[$this->cluster_type];
	}

	/**
	 * Retrieve a single app for the cluster
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function getApp($element)
	{
		static $apps = array();
		$index = $this->cluster_type . $element;

		if (!isset($apps[$index])) {
			$options = array('group' => $this->cluster_type, 'type' => SOCIAL_APPS_TYPE_APPS, 'state' => SOCIAL_STATE_PUBLISHED, 'element' => $element);

			$app = ES::table('App');
			$app->load($options);

			$apps[$index] = $app;
		}

		return $apps[$index];
	}

	/**
	 * Determine if the cluster's app is published
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function isAppPublished($element) {
		$app = $this->getApp($element);

		if (!$app->id) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieve the app's permalink by specific element for the cluster
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getAppPermalink($element, $xhtml = true, $external = false, $layout = 'item', $sef = true)
	{
		static $items = array();

		$key = md5($this->getAlias() . $element);

		if (!isset($items[$key])) {

			// Get the app table
			$table = ES::table('App');
			$table->loadByElement($element, $this->cluster_type, SOCIAL_TYPE_APPS);

			$options = array('id' => $this->getAlias(), 'layout' => $layout, 'appId' => $table->getAlias());

			if ($external) {
				$options['external'] = true;
			}

			$options['sef'] = $sef;

			$params = array($options, $xhtml);

			// Bad implementation here!
			$method = $this->cluster_type . 's';

			$items[$key] = call_user_func_array(array('ESR', $method), $params);
		}

		return $items[$key];
	}

	/**
	 * Determines if this cluster is new or not.
	 *
	 * @since   1.2
	 * @access  public
	 * @return  boolean True if this cluster is a new cluster.
	 */
	public function isNew()
	{
		return empty($this->id);
	}

	/**
	 * Retrieves the join date of a node.
	 *
	 * @since   1.2
	 * @access  public
	 * @param   integer $uid    The node id.
	 * @param   string  $type   The node type.
	 * @return  SocialDate      The date object of the joined date.
	 */
	public function getJoinedDate($uid, $type = SOCIAL_TYPE_USER, $lapsed = false)
	{
		$node = ES::table('ClusterNode');
		$node->load(array('uid' => $uid, 'type' => $type, 'cluster_id' => $this->id));

		$date = ES::date($node->created);

		// If user wants a lapsed type.
		if ($lapsed) {
			return $date->toLapsed();
		}

		return $date;
	}

	/**
	 * Creates a new node object for this cluster.
	 *
	 * @since   1.2
	 * @access  public
	 * @param   integer $nodeId         The node id.
	 * @param   string  $nodeType       The node type.
	 * @param   integer $state          The state to set this node.
	 * @return  SocialTableClusterNode  The cluster node table object.
	 */
	public function createNode($nodeId, $nodeType, $state = SOCIAL_STATE_PUBLISHED)
	{
		$node = ES::table('ClusterNode');

		$node->cluster_id = $this->id;
		$node->uid = $nodeId;
		$node->type = $nodeType;
		$node->state = $state;

		$node->store();

		return $node;
	}

	/**
	 * Determines if this cluster has an avatar.
	 *
	 * @since   1.2
	 * @access  public
	 * @return  boolean True if this cluster has an avatar.
	 */
	public function hasAvatar()
	{
		if (isset($this->avatars['small']) && !empty($this->avatars['small'])) {
			return true;
		}

		if (!empty($this->avatar_id)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the cluster has the ability to create event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function allowEvents()
	{
		$allowed = array(SOCIAL_TYPE_GROUP, SOCIAL_TYPE_PAGE);

		if (!in_array($this->cluster_type, $allowed)) {
			return false;
		}

		$my = ES::user();

		if (!$this->config->get('events.enabled')) {
			return false;
		}

		if ($this->cluster_type == SOCIAL_TYPE_GROUP && !$this->getCategory()->getAcl()->allowed('events.groupevent', true)) {
			return false;
		}

		if ($this->cluster_type == SOCIAL_TYPE_PAGE && !$this->getCategory()->getAcl()->allowed('events.pageevent', true)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the cluster has the ability to create videos
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function allowPhotos()
	{
		$category = $this->getCategory();
		$params = $this->getParams();

		if ($this->config->get('photos.enabled', true) && $category->getAcl()->get('photos.enabled', true) && $params->get('photo.albums', true)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the cluster has the ability to create videos
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function allowVideos()
	{
		if ($this->config->get('video.enabled') && $this->getParams()->get('videos', true) && $this->getCategory()->getAcl()->get('videos.create')) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the cluster has the ability to create audios
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function allowAudios()
	{
		if ($this->config->get('audio.enabled') && $this->getParams()->get('audios', true) && $this->getCategory()->getAcl()->get('audios.create')) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieves the default avatar location as it might have template overrides.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getDefaultAvatar($size, $relative = false)
	{
		static $defaults = null;

		// Construct the cache key
		$key = $size;
		if ($relative) {
			$key .= '-useRelative';
		}

		if (!isset($defaults[$key])) {

			$overriden = 'images/easysocial_override/' . $this->cluster_type . '/avatar/' . $size . '.png';

			// If avatar override exist for this, use it.
			if (JFile::exists(JPATH_ROOT . '/' . $overriden)) {
				$path = $overriden;
			} else {
				$path = ltrim(ES::config()->get('avatars.default.' . $this->cluster_type . '.' . $size), '/');
			}

			if (!$relative) {
				$path = ES::getUrl('/' . $path);
			}

			$defaults[$key] = $path;
		}

		return $defaults[$key];
	}

	/**
	 * Retrieves the user's avatar location
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getAvatar($size = SOCIAL_AVATAR_MEDIUM, $relative = false)
	{
		// If the avatar size that is being requested is invalid, return default avatar.
		$default = $this->getDefaultAvatar($size, $relative);

		if (!$this->avatars[$size] || empty($this->avatars[$size])) {

			// Check if parent exist and call the parent.
			if ($this->hasParent()) {
				return $this->getParent()->getAvatar($size);
			}

			return $default;
		}

		// Get the path to the avatar storage.
		$container = ES::cleanPath(ES::config()->get('avatars.storage.container'));
		$location = ES::cleanPath(ES::config()->get('avatars.storage.' . $this->cluster_type));

		// Build the path now.
		$path = $container . '/' . $location . '/' . $this->id . '/' . $this->avatars[$size];

		if ($this->avatarStorage == SOCIAL_STORAGE_JOOMLA) {
			// Build final storage path.
			$absolutePath = JPATH_ROOT . '/' . $path;

			// Detect if this file really exists.
			if (!JFile::exists($absolutePath)) {
				return $default;
			}

			$uri = $path;

			if (!$relative) {
				$uri = ES::getUrl($uri);
			}
		} else {
			$storage = ES::storage($this->avatarStorage);
			$uri = $storage->getPermalink($path);
		}

		return $uri;
	}


	/**
	 * Retrieves the photo table for the cluster's avatar.
	 *
	 * @since   1.2
	 * @access  public
	 * @return  SocialTablePhoto    The avatar photo table object.
	 */
	public function getAvatarPhoto()
	{
		static $photos = array();

		if (!isset($photos[$this->id])) {
			$model = ES::model('Avatars');
			$photo = $model->getPhoto($this->id, $this->cluster_type);

			$photos[$this->id] = $photo;
		}

		return $photos[$this->id];
	}

	/**
	 * Determines if this cluster has a cover photo.
	 *
	 * @since   1.2
	 * @access  public
	 * @return  boolean True if this cluster has a cover photo.
	 */
	public function hasCover()
	{
		return !(empty($this->cover) || empty($this->cover->id));
	}

	/**
	 * Get the cover table object for this cluster.
	 *
	 * @since   1.2
	 * @access  public
	 * @return  SocialTableCover    The cover table object for this cluster.
	 */
	public static function getCoverObject($cluster = null)
	{
		$cover = ES::table('Cover');

		if (!empty($cluster->cover_id)) {
			$coverData = new stdClass();
			$coverData->id = $cluster->cover_id;
			$coverData->uid = $cluster->cover_uid;
			$coverData->type = $cluster->cover_type;
			$coverData->photo_id = $cluster->cover_photo_id;
			$coverData->cover_id = $cluster->cover_cover_id;
			$coverData->x = $cluster->cover_x;
			$coverData->y = $cluster->cover_y;
			$coverData->modified = $cluster->cover_modified;

			$cover->bind($coverData);
		} else {
			$cover->type = $cluster->cluster_type;
		}

		return $cover;
	}

	/**
	 * Retrieves the group's cover position.
	 *
	 * @since   1.2
	 * @access  public
	 * @return  integer The position of the cover.
	 *
	 */
	public function getCoverPosition()
	{
		if (!$this->cover) {
			if ($this->hasParent()) {
				return $this->getParent()->getCoverPosition();
			}

			return 0;
		}

		return $this->cover->getPosition();
	}

	/**
	 * Retrieves this cluster's cover uri.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getCover($size = SOCIAL_AVATAR_LARGE)
	{

		if (!$this->cover) {
			if ($this->hasParent()) {
				return $this->getParent()->getCover();
			}

			$cover = $this->getDefaultCover();
			return $cover;
		}

		return $this->cover->getSource($size);
	}

	/**
	 * Returns the cover object.
	 *
	 * @since  1.3
	 * @access public
	 * @return SocialTableCover    The cover object.
	 */
	public function getCoverData()
	{
		if ((empty($this->cover) || empty($this->cover->id)) && $this->hasParent()) {
			return $this->getParent()->cover;
		}

		return $this->cover;
	}

	/**
	 * Retrieves the default cover location as it might have template overrides.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getDefaultCover()
	{
		static $default = null;

		if (!$default) {
			$app = JFactory::getApplication();
			$overriden = JPATH_ROOT . '/templates/' . $app->getTemplate() . '/html/com_easysocial/covers/' . $this->cluster_type . '/default.jpg';
			$uri = ES::getUrl('/templates/' . $app->getTemplate() . '/html/com_easysocial/covers/' . $this->cluster_type . '/default.jpg');

			if (JFile::exists($overriden)) {
				$default = $uri;
			} else {
				$default = ES::getUrl($this->config->get('covers.default.' . $this->cluster_type . '.default'));
			}
		}

		return $default;
	}

	/**
	 * Deletes the avatar for the current cluster
	 *
	 * @since	2.0.10
	 * @access	public
	 */
	public function deleteAvatar()
	{
		$avatar = ES::table('Avatar');
		$exists = $avatar->load(array('uid' => $this->id, 'type' => $this->cluster_type));

		if (!$exists) {
			return;
		}

		return $avatar->delete();
	}

	/**
	 * Allows deletion of cover.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function deleteCover()
	{
		$state = $this->cover->delete();

		// Reset this user's cover
		$this->cover = ES::table('Cover');

		return $state;
	}

	/**
	 * Returns the last creation date of the cluster
	 *
	 * @since   1.2
	 * @access  public
	 * @return  SocialDate      The created date object.
	 */
	public function getCreatedDate()
	{
		$date = ES::get('Date', $this->created);

		return $date;
	}

	/**
	 * Retrieves the params for a cluster.
	 *
	 * @since   1.2
	 * @access  public
	 * @return  SocialRegistry  The registry object.
	 */
	public function getParams()
	{
		$params = ES::registry($this->params);

		return $params;
	}

	/**
	 * Method to standardize cluster object to be similar as a JUser object.
	 *
	 * @since  1.3
	 * @access public
	 * @param  string    $key The key to retrieve.
	 * @return Mixed          The value of the key.
	 */
	public function getParam($key)
	{
		return $this->getParams()->get($key);
	}

	/**
	 * Retrieves the user's real name dependent on the system configurations.
	 *
	 * @since   1.2
	 * @access  public
	 * @return  string  The cluster's title.
	 */
	public function getName()
	{
		$title = JText::_($this->title);

		return $title;
	}

	/**
	 * Allows caller to remove the cluster avatar.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function removeAvatar()
	{
		$avatar = ES::table('Avatar');
		$state = $avatar->load(array('uid' => $this->id, 'type' => $this->cluster_type));

		if ($state) {
			$state = $avatar->delete();
		}

		return $state;
	}

	/**
	 * Override parent's delete implementation if necessary.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function deleteCluster()
	{
		// If deletion was successful, we need to remove it from smart search
		$namespace = 'easysocial.' . $this->cluster_type . 's';

		JPluginHelper::importPlugin('finder');
		$dispatcher = JDispatcher::getInstance();

		$dispatcher->trigger('onFinderAfterDelete', array($namespace, &$this->table));

		$state = $this->table->delete();

		return $state;
	}

	/**
	 * Deletes files that are stored for the cluster
	 *
	 * @since	2.0.20
	 * @access	public
	 */
	public function deleteFiles()
	{
		$model = ES::model('Files');
		$files = $model->getFiles($this->id, $this->cluster_type);

		// Nothing to be deleted
		if (!$files) {
			return;
		}

		foreach ($files as $file) {
			$file->delete();
		}

		return;
	}

	/**
	 * Deletes reviews which associated with the clusters
	 *
	 * @since	2.2.3
	 * @access	public
	 */
	public function deleteReviews()
	{
		$model = ES::model('Reviews');
		$reviews = $model->getReviews($this->id, $this->cluster_type);

		// Nothing to be deleted
		if (!$reviews) {
			return;
		}

		foreach ($reviews as $review) {
			$review->delete();
		}

		return;
	}

	/**
	 * Logics for deleting a cluster
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function delete()
	{
		// Load cluster apps.
		ES::apps()->load($this->cluster_type);

		// @trigger onBeforeDelete
		$dispatcher = ES::dispatcher();

		// Deduct points when a cluster is deleted
		$points = ES::points();
		$points->assign($this->cluster_var . '.remove', 'com_easysocial', $this->getCreator()->id);

		// remove the access log for this action
		ES::access()->removeLog($this->cluster_var . '.limit', $this->getCreator()->id, $this->id, $this->cluster_type);

		// Set the arguments
		$args = array(&$this);

		// @trigger onBeforeDelete
		$dispatcher->trigger($this->cluster_type, 'onBeforeDelete', $args);

		// @trigger onClusterBeforeDelete
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'on' . $this->cluster_type . 'BeforeDelete', $args);

		// Delete all members from the cluster nodes.
		$this->deleteNodes();

		// Delete photos albums for this cluster.
		$this->deletePhotoAlbums();

		// Delete videos for this cluster
		$this->deleteVideos();

		// Delete avatar for the cluster
		$this->deleteAvatar();

		// Delete stream items for this cluster
		$this->deleteStream();

		// Delete all cluster news
		$this->deleteNews();

		// Delete all user notification associated with this cluster.
		$this->deleteNotifications();

		// Delete any files that are stored for the cluster
		$this->deleteFiles();

		// Delete any reviews that are stored for the cluster
		$this->deleteReviews();

		// Delete from the cluster table
		$state = $this->deleteCluster();

		$args[] = $state;

		// @trigger onAfterDelete
		$dispatcher->trigger($this->cluster_type , 'onAfterDelete', $args);

		// @trigger onClusterAfterDelete
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'on' . $this->cluster_type . 'AfterDelete', $args);

		return $state;
	}

	/**
	 * Delete notifications related to this cluster
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function deleteNotifications()
	{
		$model = ES::model('Clusters');
		$state = $model->deleteClusterNotifications($this->id, $this->cluster_type, $this->cluster_type);

		return $state;
	}

	/**
	 * Allows caller to remove a member from the cluster
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function deleteMember($userId, $notify = false)
	{
		$state = $this->deleteNode($userId, SOCIAL_TYPE_USER);

		// If the user has been removed, delete all his stream in this cluster
		if ($state) {
			$this->deleteMemberStream($userId);

			// Remove the user from the cache list as well
			if (isset($this->members[$userId])) {
				unset($this->members[$userId]);
			}

			// Remove the user from the cache list as well
			if (isset($this->pending[$userId])) {
				unset($this->pending[$userId]);
			}

			// Notify cluster members
			if ($notify) {
				$this->notifyMembers('user.remove', array('userId' => $userId));
			}

		}

		return $state;
	}

	/**
	 * Determines if the provided user id is a member of this cluster
	 *
	 * @since   2.0
	 * @access  public
	 * @param   int     The user's id to check against.
	 * @return  bool    True if he / she is a member already.
	 */
	public function isMember($userId = null)
	{
		static $_cache = array();

		$userId = ES::user($userId)->id;

		$idx = $userId . '-' . $this->id;

		if (!isset($_cache[$idx])) {

			if (!isset($this->members[$userId])) {
				$model = ES::model('Clusters');
				$this->members[$userId] = $model->isMember($userId, $this->id);
			}

			$_cache[$idx] = $this->members[$userId];

		}

		return $_cache[$idx];
	}

	/**
	 * Determines if the node is invited by another user
	 *
	 * @access  private
	 * @param   null
	 * @return  boolean True on success false otherwise.
	 */
	public function isInvited($uid = null)
	{
		static $invited = array();

		$key = $uid . $this->id;

		if (!isset($invited[$key])) {
			$user = ES::user($uid);

			$node = ES::table('ClusterNode');
			$node->load(array('uid' => $user->id, 'type' => SOCIAL_TYPE_USER, 'cluster_id' => $this->id));

			$invited[$key] = false;

			if ($node->invited_by) {
				$invited[$key] = true;
			}
		}

		return $invited[$key];
	}

	/**
	 * Determines if the provided user id is a pending member of this cluster
	 *
	 * @since   2.0
	 * @access  public
	 * @param   int     The user's id to check against.
	 * @return  bool    True if he / she is a member already.
	 */
	public function isPendingMember($userId = null)
	{
		$userId = ES::user($userId)->id;

		if (isset($this->pending[$userId])) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieve total pending post available
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getTotalPendingPosts()
	{
		static $_cache = array();

		$idx = $this->id . '-' . $this->cluster_type;

		if (!isset($_cache[$idx])) {
			$streamModel = ES::model('Stream');
			$count = $streamModel->getModeratedPostsCount($this->id, $this->cluster_type);
			$_cache[$idx] = $count;
		}

		return $_cache[$idx];
	}

	/**
	 * Retrieve total feeds available
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getTotalFeeds()
	{
		$model = ES::model('RSS');

		$count = count($model->getItems($this->id, $this->cluster_type));

		return $count;
	}

	/**
	 * Return the total number of members in this cluster
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getTotalMembers($options = array())
	{
		static $_cache = array();

		$options = array();

		if (!isset($_cache[$this->id])) {

			// For Page, admin is not considered as member
			if ($this->cluster_type == SOCIAL_TYPE_PAGE) {
				$options['membersOnly'] = true;
			}

			$model = ES::model('Clusters');
			$_cache[$this->id] = $model->getTotalMembers($this->id, $options);
		}

		return $_cache[$this->id];
	}

	/**
	 * Returns the total admins in this event.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getTotalAdmins()
	{
		return count($this->admins);
	}

	/**
	 * Create a stream for any user action in cluster
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function createStream($actorId = null, $verb, $options = array())
	{
		$stream = ES::stream();
		$tpl = $stream->getTemplate();
		$actor = ES::user($actorId);

		// this is a cluster stream and it should be viewable in both cluster and user cluster.
		$tpl->setCluster($this->id, $this->cluster_type, $this->type);

		// Set the actor
		$tpl->setActor($actor->id, SOCIAL_TYPE_USER);

		$postActor = isset($options['postActor']) ? $options['postActor'] : null;

		if ($postActor) {
			$tpl->setPostAs($postActor);
		}

		// Set the context
		$tpl->setContext($this->id, $this->cluster_var);

		// Set the verb
		$tpl->setVerb($verb);

		// Set the params to cache the cluster data
		$registry = ES::registry();
		$registry->set($this->cluster_type, $this);

		// Set the params to cache the cluster data
		$tpl->setParams($registry);

		// since this is a cluster and user stream, we need to call setPublicStream
		// so that this stream will display in unity cluster as well
		// This stream should be visible to the public
		$tpl->setAccess('core.view');

		$stream->add($tpl);
	}

	/**
	 * Rejects the cluster
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function reject($reason = '', $email = false, $delete = false)
	{
		$config = ES::config();
		$my = ES::user();

		// If we need to send email to the user, we need to process this here.
		if ($email) {
			// Push arguments to template variables so users can use these arguments
			$params = array(
							'title' => $this->getName(),
							'name' => $this->getCreator()->getName(),
							'reason' => $reason,
							'manageAlerts' => false
						);

			// Load front end language file.
			ES::language()->loadSite();

			// Get the email title.
			$title = JText::_('COM_EASYSOCIAL_EMAILS_' . $this->cluster_type . '_REJECTED_EMAIL_TITLE');

			// Immediately send out emails
			$mailer = ES::mailer();

			// Get the email template.
			$mailTemplate = $mailer->getTemplate();

			// Set recipient
			$mailTemplate->setRecipient($this->getCreator()->getName(), $this->getCreator()->email);

			// Set title
			$mailTemplate->setTitle($title);

			// Set the contents
			$mailTemplate->setTemplate('site/' . $this->cluster_type . '/rejected', $params);

			// Set the priority. We need it to be sent out immediately since this is user registrations.
			$mailTemplate->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

			// Try to send out email now.
			$mailer->create($mailTemplate);
		}

		// If required, delete the page from the site.
		if ($delete) {
			$this->delete();

			// remove the access log for this action
			ES::access()->removeLog($this->cluster_var . '.limit', $this->getCreator()->id, $this->id, $this->cluster_type);
		} else {
			// we need to log the reason so that the author can review again the cluster details.

			// update the cluster state to draft.
			$this->state = SOCIAL_CLUSTER_DRAFT;
			$state = $this->save();

			if ($state) {
				// lets add the reject reason.
				$rejectTbl = ES::table('ClusterReject');
				$rejectTbl->message = ($reason) ? $reason : JText::_('COM_EASYSOCIAL_CLUSTERS_EMPTY_REJECT_REASON');
				$rejectTbl->cluster_id = $this->id;
				$rejectTbl->created_by = $my->id;
				$rejectTbl->created = ES::date()->toSql();

				$rejectTbl->store();
			}

		}

		return true;
	}

	/**
	 * Copy avatar if the cluster is copied from other cluster
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function copyAvatar($targetClusterId)
	{
		// get avatar from target cluster
		$targetAvatar = ES::table('Avatar');
		$targetAvatar->load(array('uid' => $targetClusterId, 'type' => $this->cluster_type));

		if (!$targetAvatar->id) {
			return false;
		}

		if ($targetAvatar->storage != SOCIAL_STORAGE_JOOMLA) {
			return false;
		}

		$targetPhoto = ES::table('Photo');
		$targetPhoto->load($targetAvatar->photo_id);

		// now lets create album for this new cluster.
		$album = ES::table('Album');
		$album->uid = $this->id;
		$album->type = $this->cluster_type;
		$album->user_id = $this->my->id;

		$album->title = 'COM_EASYSOCIAL_ALBUMS_PROFILE_AVATAR';
		$album->caption = 'COM_EASYSOCIAL_ALBUMS_PROFILE_AVATAR_DESC';
		$album->created = ES::date()->toMySQL();
		$album->core = SOCIAL_ALBUM_PROFILE_PHOTOS;
		$album->store();

		// now we need to create photo
		$photo = ES::table('Photo');

		$photo->uid = $this->id;
		$photo->type = $this->cluster_type;
		$photo->user_id = $this->my->id;
		$photo->album_id = $album->id;
		$photo->title = $targetPhoto->title;
		$photo->caption = $targetPhoto->caption;
		$photo->created = ES::date()->toMySQL();
		$photo->state = 1;
		$photo->storage = SOCIAL_STORAGE_JOOMLA;
		$photo->total_size = $targetPhoto->total_size;
		$photo->store();

		// update cover photo of the album.
		$album->cover_id = $photo->id;
		$album->store();

		$avatar = ES::table('Avatar');
		$avatar->uid = $this->id;
		$avatar->type = $this->cluster_type;
		$avatar->photo_id = $photo->id;
		$avatar->small = $targetAvatar->small;
		$avatar->medium = $targetAvatar->medium;
		$avatar->square = $targetAvatar->square;
		$avatar->large = $targetAvatar->large;
		$avatar->modified = ES::date()->toMySQL();
		$avatar->storage = SOCIAL_STORAGE_JOOMLA;
		$avatar->store();

		// lets copy the avatar images.
		$config = ES::config();
		// Get the avatars storage path.
		$avatarsPath = ES::cleanPath($config->get('avatars.storage.container'));

		// Let's construct the final path.
		$sourcePath = JPATH_ROOT . '/' . $avatarsPath . '/' . $this->cluster_type . '/' . $targetClusterId;
		$targetPath = JPATH_ROOT . '/' . $avatarsPath . '/' . $this->cluster_type . '/' . $this->id;

		if (! JFolder::exists($targetPath)) {
			// now we are save to copy.
			if (JFolder::exists($sourcePath)) {
				JFolder::copy($sourcePath, $targetPath);
			}
		}

		// now we copy the photos
		// Get the avatars storage path.
		$photosPath = ES::cleanPath($config->get('photos.storage.container'));

		// Let's construct the final path.
		$sourcePath = JPATH_ROOT . '/' . $photosPath . '/' . $targetPhoto->album_id . '/' . $targetPhoto->id;
		$targetPath = JPATH_ROOT . '/' . $photosPath . '/' . $photo->album_id . '/' . $photo->id;

		if (!JFolder::exists($targetPath)) {
			// now we are save to copy.
			if (JFolder::exists($sourcePath)) {
				JFolder::copy($sourcePath, $targetPath);

				// now we need to insert into photo meta
				$model = ES::model('Photos');
				$metas = $model->getMeta($targetPhoto->id, SOCIAL_PHOTOS_META_PATH);

				if ($metas) {
					foreach ($metas as $meta) {

						$relative = $photosPath . '/' . $photo->album_id . '/' . $photo->id . '/' . basename($meta->value);

						$photoMeta = ES::table('PhotoMeta');
						$photoMeta->photo_id = $photo->id;
						$photoMeta->group = $meta->group;
						$photoMeta->property = $meta->property;
						$photoMeta->value = $relative;

						$photoMeta->store();
					}
				}

			}
		}

		return true;
	}

	/**
	 * Copy cover if the cluster is copied from other cluster
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function copyCover($targetClusterId)
	{
		// get cover from target cluster
		// create album for current cluster
		// create photos for current cluster
		// duplicate physical files from target cluster
		//
		$my = ES::user();

		$targetCover = ES::table('Cover');
		$targetCover->load(array('uid' => $targetClusterId, 'type' => $this->cluster_type));

		if (!$targetCover->id) {
			return false;
		}

		$targetPhoto = ES::table('Photo');
		$targetPhoto->load($targetCover->photo_id);

		if ($targetPhoto->storage != SOCIAL_STORAGE_JOOMLA) {
			return false;
		}

		// now lets create album for this new cluster.
		$album = ES::table('Album');
		$album->uid = $this->id;
		$album->type = $this->cluster_type;
		$album->user_id = $my->id;

		$album->title = 'COM_EASYSOCIAL_ALBUMS_PROFILE_COVER';
		$album->caption = 'COM_EASYSOCIAL_ALBUMS_PROFILE_COVER_DESC';
		$album->created = ES::date()->toMySQL();
		$album->core = SOCIAL_ALBUM_PROFILE_COVERS;
		$album->store();

		// now we need to create photo
		$photo = ES::table('Photo');

		$photo->uid = $this->id;
		$photo->type = $this->cluster_type;
		$photo->user_id = $my->id;
		$photo->album_id = $album->id;
		$photo->title = $targetPhoto->title;
		$photo->caption = $targetPhoto->caption;
		$photo->created = ES::date()->toMySQL();
		$photo->state = 1;
		$photo->storage = SOCIAL_STORAGE_JOOMLA;
		$photo->total_size = $targetPhoto->total_size;
		$photo->store();

		// update cover photo of the album.
		$album->cover_id = $photo->id;
		$album->store();

		$cover = ES::table('Cover');
		$cover->uid = $this->id;
		$cover->type = $this->cluster_type;
		$cover->photo_id = $photo->id;
		$cover->x = $targetCover->x;
		$cover->y = $targetCover->y;
		$cover->modified = ES::date()->toMySQL();
		$cover->store();

		// now we copy the photos
		$config = ES::config();

		// Get the avatars storage path.
		$photosPath = ES::cleanPath($config->get('photos.storage.container'));

		// Let's construct the final path.
		$sourcePath = JPATH_ROOT . '/' . $photosPath . '/' . $targetPhoto->album_id . '/' . $targetPhoto->id;
		$targetPath = JPATH_ROOT . '/' . $photosPath . '/' . $photo->album_id . '/' . $photo->id;

		if (! JFolder::exists($targetPath)) {
			// now we are save to copy.
			if (JFolder::exists($sourcePath)) {
				JFolder::copy($sourcePath, $targetPath);

				// now we need to insert into photo meta
				$model = ES::model('Photos');
				$metas = $model->getMeta($targetPhoto->id, SOCIAL_PHOTOS_META_PATH);

				if ($metas) {
					foreach ($metas as $meta) {

						$relative = $photosPath . '/' . $photo->album_id . '/' . $photo->id . '/' . basename($meta->value);

						$photoMeta = ES::table('PhotoMeta');
						$photoMeta->photo_id = $photo->id;
						$photoMeta->group = $meta->group;
						$photoMeta->property = $meta->property;
						$photoMeta->value = $relative;

						$photoMeta->store();
					}
				}
			}
		}

		return true;
	}

	/**
	 * Determines if the access to videos is allowed
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function canAccessEvents()
	{
		$registry = $this->getParams();

		if (!$registry->get('events', true)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the access to feeds is allowed
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function canAccessFeeds()
	{
		$registry = $this->getParams();

		if (!$registry->get('feeds', true)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the access to files is allowed
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function canAccessFiles()
	{
		$access = $this->getAccess();

		if (!$access->get('files.enabled')) {
			return false;
		}

		$registry = $this->getParams();

		if (!$registry->get('files', true)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the access to polls is allowed
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function canAccessPolls()
	{
		$access = $this->getAccess();

		if (!$access->get('polls.enabled')) {
			return false;
		}

		$registry = $this->getParams();

		if (!$registry->get('polls', true)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the access to tasks is allowed
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function canAccessTasks()
	{
		$access = $this->getAccess();

		if (!$access->get('tasks.enabled')) {
			return false;
		}

		$registry = $this->getParams();

		if (!$registry->get('tasks', true)) {
			return false;
		}

		return true;
	}


	/**
	 * Determines if the user is allowed to access action in the dropdown menu
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canAccessActionMenu($userId = null)
	{
		if ($this->isAdmin() || $this->my->isSiteAdmin()) {
		   return true;
		}

		if (ES::reports()->canReport()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the access to videos is allowed
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function canAccessVideos()
	{
		$registry = $this->getParams();

		if (!$registry->get('videos', true)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the access to audios is allowed
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function canAccessAudios()
	{
		$registry = $this->getParams();

		if (!$registry->get('audios', true)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the user is allowed to create news in a cluster
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canCreateNews($userId = null)
	{
		if ($this->isAdmin() || $this->my->isSiteAdmin()) {
			return true;
		}

		// Get the ACL type
		$access = $this->getAccess();
		$user = ES::user($userId);

		if ($access->get('announcements.create', 'admins') == 'members' && $this->isMember($user->id)) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if the user is allowed to delete news in a cluster
	 *
	 * @since	2.2.2
	 * @access	public
	 */
	public function canDeleteNews(SocialTableClusterNews $news)
	{
		if ($this->isAdmin() || $this->my->isSiteAdmin()) {
			return true;
		}

		// User must have the permission to create news in order to delete it
		if (!$this->canCreateNews()) {
			return false;
		}

		// Check for owner
		if ($this->my->id == $news->created_by) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user is allowed to create files in a cluster
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canCreateFiles($userId = null)
	{
		$user = ES::user($userId);

		// Guests definitely not allowed
		if (!$user->id) {
			return false;
		}

		// Site admins and cluster admins are always allowed to create videos
		if ($user->isSiteAdmin() || $this->isAdmin($user->id)) {
			return true;
		}

		// Get the ACL type
		$access = $this->getAccess();

		if ($access->get('files.upload', 'members') == 'members' && $this->isMember($user->id)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user is allowed to create news in a cluster
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canCreateFeeds($userId = null)
	{
		if ($this->cluster_type == SOCIAL_TYPE_GROUP && $this->isMember()) {
			return true;
		}

		if ($this->cluster_type == SOCIAL_TYPE_PAGE && ($this->isAdmin() || $this->my->isSiteAdmin())) {
			return true;
		}

		return false;
	}

	/**
	 * Some description
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canCompleteTask($userId = null)
	{
		if ($this->my->isSiteAdmin() || $this->isAdmin()) {
			return true;
		}

		if ($userId == $this->my->id) {
			return true;
		}

		return false;
	}

	/**
	 * Standard way of determining if the user is allowed to post a new task
	 * in the cluster. If the cluster requires a different way of checking, override this method.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canCreateTasks($userId = null)
	{
		$user = ES::user($userId);

		if ($user->isSiteAdmin()) {
			return true;
		}

		if ($this->isAdmin()) {
			return true;
		}

		if ($this->isMember($user->id)) {
			return true;
		}

		return false;
	}

	/**
	 * Standard way of determining if the user is allowed to edit a task
	 * in the cluster. If the cluster requires a different way of checking, override this method.
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function canEditTasks($userId = null)
	{
		$user = ES::user($userId);

		if ($user->isSiteAdmin()) {
			return true;
		}

		if ($this->isAdmin()) {
			return true;
		}

		if ($this->isMember($user->id)) {
			return true;
		}

		return false;
	}

	/**
	 * Standard way of determining if the user is allowed to delete a task
	 * in the cluster. If the cluster requires a different way of checking, override this method.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canDeleteTasks($userId = null)
	{
		$user = ES::user($userId);

		if ($user->isSiteAdmin()) {
			return true;
		}

		if ($this->isMember($user->id)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user is allowed to upload videos
	 *
	 * @since	2.0.14
	 * @access	public
	 */
	public function canCreateVideos($userId = null)
	{
		$user = ES::user($userId);

		// Guests definitely not allowed
		if (!$user->id) {
			return false;
		}

		// Site admins and cluster admins are always allowed to create videos
		if ($user->isSiteAdmin() || $this->isAdmin($user->id)) {
			return true;
		}

		// Get the ACL type
		$access = $this->getAccess();

		if ($access->get('videos.upload', 'members') == 'members' && $this->isMember($user->id)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user is allowed to upload audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function canCreateAudios($userId = null)
	{
		$user = ES::user($userId);

		// Guests definitely not allowed
		if (!$user->id) {
			return false;
		}

		// Site admins and cluster admins are always allowed to create audio
		if ($user->isSiteAdmin() || $this->isAdmin($user->id)) {
			return true;
		}

		// Get the ACL type
		$access = $this->getAccess();

		if ($access->get('audios.upload', 'members') == 'members' && $this->isMember($user->id)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user is allowed to create polls
	 *
	 * @since	2.0.14
	 * @access	public
	 */
	public function canCreatePolls($userId = null)
	{
		// Get the ACL type
		$access = $this->getAccess();

		$user = ES::user($userId);

		// Guests definitely not allowed
		if (!$user->id) {
			return false;
		}

		// If the params has been disabled, we shouldn't show this at all
		$registry = $this->getParams();

		if (!$registry->get('polls', true)) {
			return false;
		}

		// Site admins and cluster admins are always allowed to create videos
		if ($user->isSiteAdmin() || $this->isAdmin($user->id)) {
			return true;
		}

		if ($access->get('polls.create', 'members') == 'members' && $this->isMember($user->id)) {
			return true;
		}



		return false;
	}

	/**
	 * Determines if the user is allowed to upload photos
	 *
	 * @since	2.0.14
	 * @access	public
	 */
	public function canCreatePhotos($userId = null)
	{
		$user = ES::user($userId);

		// Guests definitely not allowed
		if (!$user->id) {
			return false;
		}

		// Global configuration
		if (!$user->getAccess()->allowed('photos.create')) {
			return false;
		}

		if (!$this->config->get('photos.enabled') || !$user->getAccess()->allowed('albums.create')) {
			return false;
		}

		// Check for categories acl as this also represent as global configuration
		if (!$this->getCategory()->getAcl()->get('photos.enabled', true) || !$this->getParams()->get('photo.albums', true)) {
			return false;
		}

		// Site admins and cluster admins are always allowed to upload photos
		if ($user->isSiteAdmin() || $this->isAdmin($user->id)) {
			return true;
		}

		// Get the ACL type
		$access = $this->getAccess();

		if ($access->get('photos.upload', 'members') == 'members' && $this->isMember($user->id)) {
			return true;
		}

		return false;
	}

	/**
	 * Standard method to determine if the user is allowed to create a new discussion
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canCreateDiscussion($userId = null)
	{
		$user = ES::user($userId);

		if ($user->isSiteAdmin() || $this->isAdmin($user->id)) {
			return true;
		}

		// We don't allow a non-member to create a discussion
		if ($this->isMember($user->id)) {
			return true;
		}

		return false;
	}

	/**
	 * Standard way of determining if the user is allowed to post a new milestone
	 * in the cluster. If the cluster requires a different way of checking, override this method.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canCreateMilestones($userId = null)
	{
		// For milestone, we should only allow site admin or cluster admin to create it
		if ($this->isAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current user able to submit a review
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canSubmitReview($userId = null)
	{
		$user = ES::user($userId);

		if ($user->guest) {
			return false;
		}

		if ($user->isSiteAdmin() || $this->isAdmin()) {
			return true;
		}

		// check if the user has submitted review before
		$model = ES::model('Reviews');
		$hasVoted = $model->hasVoted($this->id, $this->cluster_type, $user->id);

		// User can only vote one time.
		if (!$hasVoted) {
			return true;
		}

		return false;
	}

	/**
	 * Standard way of determining if the user is allowed to delete a milestone
	 * in the cluster. If the cluster requires a different way of checking, override this method.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canDeleteMilestones($userId = null)
	{
		return $this->canCreateMilestones();
	}

	/**
	 * Standard way of determining if the user is allowed to resolve a milestone
	 * in the cluster. If the cluster requires a different way of checking, override this method.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canResolveMilestones($userId = null)
	{
		return $this->canCreateMilestones();
	}

	/**
	 * Determines of the user can utilize the story form of a cluster
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canViewStoryForm(SocialUser $user)
	{
		if ($user->isSiteAdmin()) {
			return true;
		}

		if ($this->isMember()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can create/edit filter form
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canCreateStreamFilter($userId = null)
	{
		if (is_null($userId)) {
			$userId = ES::user()->id;
		}

		$user = ES::user($userId);

		// Only site admin and cluster admin can create
		if ($this->isAdmin($user->id)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can feature the event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canFeature($userId = null)
	{
		$user = ES::user($userId);

		if ($user->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can unpublish the cluster
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canUnpublish($userId = null)
	{
		$user = ES::user($userId);

		// Only site admins are allowed to unpublish a cluster
		if ($user->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can delete the cluster
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canDelete($userId = null)
	{
		$user = ES::user($userId);

		if ($user->isSiteAdmin() || $this->isOwner()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if user can promote other user as admin
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canPromoteMember($userId = null)
	{
		if (!$userId) {
			$userId = ES::user()->id;
		}

		if (!$this->isOwner($userId) && !$this->isAdmin($userId) && !$user->isSiteAdmin()) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves the custom field value from this cluster.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getFieldValue($key)
	{
		static $processed = array();

		if (!isset($processed[$this->id])) {
			$processed[$this->id] = array();
		}

		if (!isset($processed[$this->id][$key])) {
			if (!isset($this->fields[$key])) {
				$result = FD::model('Fields')->getCustomFields(array('group' => $this->cluster_type, 'workflow_id' => $this->getWorkflow()->id, 'data' => true , 'dataId' => $this->id , 'dataType' => $this->cluster_type, 'key' => $key));

				$this->fields[$key] = isset($result[0]) ? $result[0] : false;
			}

			$field = $this->fields[$key];

			// Initialize a default property
			$processed[$this->id][$key] = '';

			if ($field) {
				// Trigger the getFieldValue to obtain data from the field.
				$value = FD::fields()->getValue($field, $this->cluster_type);

				$processed[$this->id][$key] = $value;
			}
		}

		return $processed[$this->id][$key];
	}

	/**
	 * Retrieves the custom field data from this cluster.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getFieldData($key)
	{
		static $processed = array();

		if (!isset($processed[$this->id])) {
			$processed[$this->id] = array();
		}

		if (!isset($processed[$this->id][$key])) {
			if (!isset($this->fields[$key])) {
				$result = FD::model('Fields')->getCustomFields(array('group' => $this->cluster_type, 'workflow_id' => $this->getWorkflow()->id, 'data' => true , 'dataId' => $this->id , 'dataType' => $this->cluster_type, 'key' => $key));


				$this->fields[$key] = isset($result[0]) ? $result[0] : false;
			}

			$field = $this->fields[$key];

			// Initialize a default property
			$processed[$this->id][$key] = '';

			if ($field) {
				// Trigger the getFieldValue to obtain data from the field.
				$value = FD::fields()->getData($field, $this->cluster_type);

				$processed[$this->id][$key] = $value;
			}
		}

		return $processed[$this->id][$key];
	}

	/**
	 * Retrieves the category of this cluster.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getCategory()
	{
		static $categories = array();

		if (!isset($categories[$this->category_id])) {
			$category = ES::table('ClusterCategory');
			$category->load($this->category_id);

			$categories[$this->category_id] = $category;
		}

		return $categories[$this->category_id];
	}

	/**
	 * Preprocess before storing data into the table object.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function save()
	{
		// Determine if this record is a new user by identifying the id.
		$isNew = $this->isNew();

		// Request parent to store data.
		$this->table->bind($this);

		// Try to store the item
		$state = $this->table->store();

		if ($isNew) {
			$this->id = $this->table->id;
		}

		if ($this->state == 1) {
			$namespace = 'easysocial.' . $this->cluster_type . 's';

			JPluginHelper::importPlugin('finder');
			$dispatcher = JDispatcher::getInstance();

			if ($this->type == SOCIAL_GROUPS_INVITE_TYPE) {
				// lets remove this item from smart search
				$dispatcher->trigger('onFinderAfterDelete', array($namespace, &$this->table));
			} else {
				$dispatcher->trigger('onFinderAfterSave', array($namespace, &$this->table, $isNew));
			}
		}

		return $state;
	}


	/**
	 * Update stream access column if cluster's access type changed.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function updateStreamClusterAccess()
	{
		$model = ES::model('Clusters');
		$state = $model->updateStreamClusterAccess($this->id, $this->cluster_type, $this->type);

		return $state;
	}


	/**
	 * Delete stream related to this cluster
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function deleteStream()
	{
		$model = ES::model('Clusters');
		$state = $model->deleteClusterStream($this->id, $this->cluster_type);

		return $state;
	}

	/**
	 * Deletes all the news from the cluster
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function deleteNews()
	{
		$model = ES::model('ClusterNews');
		$state = $model->delete($this->id);

		return $state;
	}

	public function getTotalNews()
	{
		$model = ES::model('ClusterNews');
		return $model->getTotalNews($this->id);
	}

	/**
	 * Determines if this cluster is featured.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function isFeatured()
	{
		return (bool) $this->featured;
	}

	/**
	 * Determine if the provided field should be visible on the site
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isFieldVisible(SocialTableField $field)
	{
		// Check for conditional field
		if (!$field->isConditional()) {
			return true;
		}

		// Get user params
		$conditionalFields = $this->getParam('conditionalFields');

		if (!$conditionalFields) {
			return true;
		}

		$conditionalFields = json_decode($conditionalFields, true);

		if (isset($conditionalFields[$field->id]) && $conditionalFields[$field->id]) {
			return true;
		}

		return false;
	}

	/**
	 * Allows caller to log error messages
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function setError($error)
	{
		$this->error = $error;
	}

	/**
	 * Allows caller to set the cluster as a featured item.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function setFeatured()
	{
		$this->table->featured = true;

		$state = $this->table->store();

		// @TODO: Push into the stream that a group is set as featured group.
		if ($state) {
			$this->createStream(null, 'featured');
		}

		return $state;
	}

	/**
	 * Allows caller to remove the cluster from being a featured item.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function removeFeatured()
	{
		$this->table->featured = false;

		$state = $this->table->store();

		return $state;
	}

	/**
	 * Allows caller to switch owners.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function switchOwner($newUserId, $adminRights = true)
	{
		$this->creator_uid = $newUserId;
		$this->creator_type = SOCIAL_TYPE_USER;

		$this->table->bind($this);
		$this->table->store();

		// Check if the member record exists for this table.
		$node = ES::table('ClusterNode');
		$exists = $node->load(array('cluster_id' => $this->id, 'uid' => $this->creator_uid, 'type' => $this->creator_type));

		// Remove other "owners" previously
		$model = ES::model('Clusters');
		$model->removeOwners($this->id, $adminRights);

		if (!$exists) {
			// Insert a new owner record
			$node->cluster_id = $this->id;
			$node->uid = $this->creator_uid;
			$node->type = $this->creator_type;
			$node->state = SOCIAL_STATE_PUBLISHED;
			$node->owner = SOCIAL_STATE_PUBLISHED;
			$node->admin = SOCIAL_STATE_PUBLISHED;
		} else {
			$node->owner = SOCIAL_STATE_PUBLISHED;
			$node->admin = SOCIAL_STATE_PUBLISHED;
		}

		return $node->store();
	}

	/**
	 * Determines if the group is published.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function isPublished()
	{
		return $this->state == SOCIAL_CLUSTER_PUBLISHED;
	}

	public function isPending()
	{
		return $this->state == SOCIAL_CLUSTER_PENDING || $this->state == SOCIAL_CLUSTER_UPDATE_PENDING;
	}

	/**
	 * Determines if the group is under draft status.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function isDraft()
	{
		// groups that being rejected and require user to review their group content before we can re-submit for approval.
		return $this->state == SOCIAL_CLUSTER_DRAFT;
	}

	/**
	 * Retrieves error
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * retrieve cluster's aproval rejected history
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getRejectedReasons()
	{
		$model = ES::model('Clusters');
		$reasons = $model->getRejectedReasons($this->id);

		return $reasons;
	}


	/**
	 * Allows caller to remove a node item.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function deleteNode($nodeId, $nodeType = SOCIAL_TYPE_USER)
	{
		$model = ES::model('Clusters');
		$state = $model->deleteNode($this->id, $nodeId, $nodeType);

		return $state;
	}

	/**
	 * Allows caller to remove all node item associations.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function deleteNodes()
	{
		$model = ES::model('Clusters');
		$state = $model->deleteNodeAssociation($this->id);

		return $state;
	}

	/**
	 * Allows caller to remove videos
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function deleteVideos($pk = null)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Delete cluster albums
		$sql->clear();

		$sql->select('#__social_videos');
		$sql->where('uid', $this->id);
		$sql->where('type', $this->cluster_type);
		$db->setQuery($sql);

		$videos = $db->loadObjectList();

		if (!$videos) {
			return true;
		}

		foreach ($videos as $row) {
			$video = ES::video($row->uid, $row->type, $row->id);
			$video->delete();
		}

		return true;
	}

	/**
	 * Allows caller to remove all photos albums.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function deletePhotoAlbums($pk = null)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Delete cluster albums
		$sql->clear();
		$sql->select('#__social_albums');
		$sql->where('uid', $this->id);
		$sql->where('type', $this->cluster_type);
		$db->setQuery($sql);

		$albums = $db->loadObjectList();

		if ($albums) {
			foreach ($albums as $row) {
				$album = ES::table('Album');
				$album->load($row->id);

				$album->delete();
			}
		}

		return true;
	}


	/**
	 * Allows caller to unpublish this cluster.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function unpublish()
	{
		$this->table->state = SOCIAL_CLUSTER_UNPUBLISHED;

		$state = $this->table->store();

		if ($state) {
			$this->state = SOCIAL_CLUSTER_UNPUBLISHED;

			// need to update from the indexed item as well
			$this->syncChangeState($this->cluster_type, $this->id, $this->state);
		}

		return $state;
	}

	/**
	 * Allows caller to publish this cluster.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function publish()
	{
		$this->table->state = SOCIAL_CLUSTER_PUBLISHED;

		$state = $this->table->store();

		if ($state) {
			$this->state = SOCIAL_CLUSTER_PUBLISHED;

			// need to update from the indexed item as well
			$this->syncChangeState($this->cluster_type, $this->id, $this->state);
		}

		return $state;
	}

	/**
	 * Update the indexed item
	 *
	 * @since   2.1.11
	 * @access  public
	 */
	public function syncChangeState($clusterType, $id, $state)
	{
		$indexer = ES::get('Indexer');
		$context = '';

		if ($clusterType == SOCIAL_TYPE_GROUP) {
			$context = 'easysocial.groups';
		}

		if ($clusterType == SOCIAL_TYPE_EVENT) {
			$context = 'easysocial.events';
		}

		if ($clusterType == SOCIAL_TYPE_PAGE) {
			$context = 'easysocial.pages';
		}

		// need to update from the indexed item as well
		$indexer->itemStateChange($context, $id, $state);
	}

	/**
	 * Get the alias of this cluster.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getAlias()
	{
		$alias = $this->id;

		// Ensure that the name is a safe url.
		if ($this->alias) {
			$alias .= ':' . JFilterOutput::stringURLSafe($this->alias);
		}

		return $alias;
	}


	/**
	 * Gets the SocialAccess object.
	 *
	 * @since   1.2
	 * @access  public
	 *
	 */
	public function getAccess()
	{
		static $data = null;

		if (!isset($data[$this->category_id])) {
			$access = ES::access($this->category_id, SOCIAL_TYPE_CLUSTERS);

			$data[$this->category_id] = $access;
		}

		return $data[$this->category_id];
	}

	/**
	 * Returns the title of the cluster item
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getTitle()
	{
		$title = JText::_($this->title);

		return $title;
	}

	/**
	 * Retrieves the description of the cluster
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getDescription()
	{
		$content = JText::_($this->description);

		// lets detect if the content has html tag or not.
		if (strpos($content, '<p>') === false && strpos($content, '<br />') === false && strpos($content, '<br>') === false) {
			// plain text. lets nl2br
			$content = nl2br($content);
		}

		return $content;
	}

	/**
	 * Returns the cluster type
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function getType()
	{
		return $this->cluster_type;
	}

	/**
	 * Returns the cluster type
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function getTypePlural()
	{
		return $this->cluster_type . 's';
	}

	/**
	 * Returns the total number of videos in this cluster
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function getTotalVideos()
	{
		static $total = array();

		if (!isset($total[$this->id])) {
			$model = ES::model('Videos');
			$options = array('uid' => $this->id, 'type' => $this->cluster_type);

			$total[$this->id] = $model->getTotalVideos($options);
		}

		return $total[$this->id];
	}

	/**
	 * Returns the total number of audio in this cluster
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getTotalAudios()
	{
		static $total = array();

		if (!isset($total[$this->id])) {
			$model = ES::model('Audios');
			$options = array('uid' => $this->id, 'type' => $this->cluster_type);

			$total[$this->id] = $model->getTotalAudios($options);
		}

		return $total[$this->id];
	}

	/**
	 * Return the total number of albums in this cluster.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getTotalAlbums()
	{
		static $total = array();

		$sid = $this->id;

		if (!isset($total[$sid])) {
			$model = FD::model('Albums');
			$options = array('uid' => $this->id, 'type' => $this->cluster_type);

			$total[$sid] = $model->getTotalAlbums($options);
		}

		return $total[$sid];
	}

	/**
	 * Retrieves the total number of events in group and page
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getTotalEvents()
	{
		static $result = array();

		if (!isset($result[$this->id])) {
			$model = ES::model('Events');

			$options = array('group_id' => $this->id);

			if ($this->cluster_type == SOCIAL_TYPE_PAGE) {
				$options = array('page_id' => $this->id);
			}

			// Always get the publish event.
			$options['state'] = SOCIAL_STATE_PUBLISHED;

			$result[$this->id] = $model->getTotalEvents($options);
		}

		return $result[$this->id];
	}

	/**
	 * Return the total number of photos in this cluster.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getTotalPhotos($daily = false, $includeUnpublished = false)
	{
		static $total = array();

		$sid = $this->id . (int) $daily . (int) $includeUnpublished;

		if (!isset($total[$sid])) {
			$model = ES::model('Photos');
			$options = array('uid' => $this->id, 'type' => $this->cluster_type);

			if ($includeUnpublished) {
				$options['state'] = 'all';
			}

			if ($daily) {
				$today = ES::date()->toMySQL();
				$date = explode(' ', $today);

				$options['day'] = $date[0];
			}

			$total[$sid] = $model->getTotalPhotos($options);
		}

		return $total[$sid];
	}

	/**
	 * Binds the user custom fields.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function bindCustomFields($data)
	{
		// Get the registration model.
		$model = ES::model('Fields');

		// Get the field id's that this profile is allowed to store data on.
		$fields = $model->getStorableFields($this->getWorkflow()->id , SOCIAL_TYPE_CLUSTERS);

		// If there's nothing to process, just ignore.
		if (!$fields) {
			return false;
		}

		$availableFields = array();

		// Let's go through all the storable fields and store them.
		foreach ($fields as $fieldId) {
			$availableFields[$fieldId] = $fieldId;

			$key = SOCIAL_FIELDS_PREFIX . $fieldId;

			if (!isset($data[$key])) {
				continue;
			}

			$value = isset($data[$key]) ? $data[$key] : '';

			// Test if field really exists to avoid any unwanted input
			$field = ES::table('Field');

			// If field doesn't exist, just skip this.
			if (!$field->load($fieldId)) {
				continue;
			}

			// Let the table object handle the data storing
			$field->saveData($value, $this->id, $this->cluster_type);
		}

		// Store conditional fields in params so it can be use in other places
		if (isset($data['conditionalRequired']) && $data['conditionalRequired']) {
			$table = ES::table('Cluster');
			$table->load(array('id' => $this->id, 'cluster_type' => $this->cluster_type));

			$params = ES::registry($table->params);

			$conditionalFields = ES::registry($data['conditionalRequired']);
			$storedConditionalFields = ES::registry($params->get('conditionalFields'));

			$storedConditionalFields->mergeObjects($conditionalFields->getRegistry());

			// Remove any unused fields
			$conditionalFieldsArray = $storedConditionalFields->toArray();
			$obj = new stdClass();

			foreach ($conditionalFieldsArray as $key => $value) {
				if (isset($availableFields[$key])) {
					$obj->$key = $value;
				}
			}

			$newConditionalFields = ES::registry($obj);
			$params->set('conditionalFields', $newConditionalFields->toString());

			$table->params = $params->toString();
			$table->store();
		}
	}

	/**
	 * Retrieve the creator of this group.
	 * Need to support creator_type in the future. Assuming user for now.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getCreator()
	{
		$user = ES::user($this->creator_uid);

		return $user;
	}

	/**
	 * Gets cluster's reviews
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getReviews($options = array())
	{
		$model = ES::model('Reviews');
		$reviews = $model->getReviews($this->id, $this->cluster_type, $options);

		return $reviews;
	}

	/**
	 * Get total reviews for the cluster
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getTotalReviews($options = array())
	{
		return count($this->getReviews($options));
	}

	/**
	 * Retrieve a ratings for this cluster
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getAverageRatings()
	{
		$model = ES::model('Reviews');
		$ratings = $model->getAverageRatings($this->id, $this->cluster_type);

		return $ratings;
	}

	/**
	 * Retrieve the rating for this cluster
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getRatings()
	{
		$model = ES::model('Reviews');
		$ratings = $model->preloadRatings(array($this->id));

		if (!$ratings) {
			$ratings = new stdClass();
			$ratings->ratings = 0;
			$ratings->total = 0;
		}

		return $ratings;
	}

	/**
	 * Determines if the provided user id is the owner of this cluster.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function isOwner($userId = null)
	{
		$userId = ES::user($userId)->id;

		// To test for ownership, just test against the uid and type
		if ($this->creator_uid == $userId && $this->creator_type == SOCIAL_TYPE_USER) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the provided user id is an admin of this cluster
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function isAdmin($userId = null)
	{
		$user = ES::user($userId);
		$userId = $user->id;

		if (isset($this->admins[$userId])) {
			return true;
		}

		return false;
	}

	/**
	 * Creates the owner node.
	 *
	 * @since  1.2
	 * @access public
	 */
	public function createOwner($userId = null)
	{
		if (empty($userId)) {
			$userId = ES::user()->id;
		}

		$member = ES::table('clusternode');

		$state = $member->load(array('cluster_id' => $this->id, 'uid' => $userId, 'type' => SOCIAL_TYPE_USER));

		$member->cluster_id = $this->id;
		$member->uid = $userId;
		$member->type = SOCIAL_TYPE_USER;
		$member->state = SOCIAL_STATE_PUBLISHED;
		$member->admin = true;
		$member->owner = true;

		$member->store();

		return $member;
	}

	/**
	 * Returns a maps link based on the address
	 *
	 * @since  1.3
	 * @access public
	 */
	public function getAddressLink()
	{
		if (!empty($this->address)) {
			if ($this->config->get('location.provider') == 'osm') {
				return 'https://www.openstreetmap.org/search?query=' . urlencode($this->address);
			}

			return 'https://maps.google.com/?q=' . urlencode($this->address);
		}

		return 'javascript:void(0);';
	}

	public function getParent()
	{
		if (empty($this->parent_id) || empty($this->parent_type)) {
			return false;
		}

		return ES::cluster($this->parent_type, $this->parent_id);
	}

	public function hasParent()
	{
		return !empty($this->parent_id) && !empty($this->parent_type);
	}

	/**
	 * Determines if the provided user can view the cluster's items
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canViewItem($userId = null)
	{
		$user = ES::user($userId);

		if (($this->isInviteOnly() || $this->isClosed()) && !$this->isMember($user->id) && !$user->isSiteAdmin()) {
			return false;
		}

		if ($this->isPending()) {
			return false;
		}

		return true;
	}

	/**
	 * Render cluster headers
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function renderHeaders()
	{
		$doc = JFactory::getDocument();

		// Render meta headers
		$obj = new stdClass();

		$title = $this->getName();
		$description = strip_tags($this->getDescription());

		if ($this instanceof SocialEvent) {
			$eventStartDate = $this->getStartEndDisplay();

			// lets check if this is a recurring event or not.
			if (!$this->isRecurringEvent()) {
				$title .= ' - ' . $eventStartDate;
			}

			$description = '(' . $eventStartDate . ') ' . $description;
		}

		// Add event id in the title to avoid duplicate meta title
		if (!$this->config->get('seo.clusters.allowduplicatetitle')) {
			$title = $title . ' - ' . $this->id;
		}

		$title = ES::string()->escape($title);

		$obj->type = $this->getType();
		$obj->title = $title;
		$obj->description = $description;
		$obj->image = $this->getCover();
		$obj->url = $this->getPermalink(true, true);

		ES::meta()->setMetaObj($obj);
	}

	/**
	 * Render Event page title
	 *
	 * @since   2.0.20
	 * @access  public
	 */
	public function renderPageTitle($appName = null, $view = null)
	{
		// So far this method is for event page
		if ($this instanceof SocialEvent) {
			$doc = ES::document();
			$event = ES::event($this->id);

			$eventStartDate = $this->getStartEndDisplay();

			// cluster title name
			$title = $this->getTitle();

			$appNameExist = !$appName ? false : true;
			$viewExist = !$view ? false : true;

			$appNameTitle = '';
			$viewTitle = '';

			if ($appNameExist) {
				$appNameTitle = $appName;
			}

			if ($viewExist) {
				$normaliseViewTitle = 'COM_EASYSOCIAL_PAGE_TITLE_' . strtoupper($view);
				$viewTitle = JText::_($normaliseViewTitle);
			}

			// we need to handle those event which have recurring prevent duplicate page title
			if ($view == 'events') {

				if ($event->isRecurringEvent() && $appNameExist) {
					$metaTitle = $appNameTitle . ' - ' . $title;

				} elseif($event->isRecurringEvent() && !$appNameExist) {
					$metaTitle = $title;

				} elseif(!$event->isRecurringEvent() && !$appNameExist) {
					$metaTitle = $title . ' - ' . $eventStartDate;

				} elseif(!$event->isRecurringEvent() && $appNameExist) {
					$metaTitle = $appNameTitle . ' - ' . $title;

				} else {
					$metaTitle = $title;
				}

				// Add event id in the title to avoid duplicate meta title
				if (!$this->config->get('seo.clusters.allowduplicatetitle')) {
					$metaTitle = $metaTitle . ' - ' . $this->id;
				}

				$doc->title($metaTitle);
			}

			// Event video page
			if ($view == 'videos') {

				if ($event->isRecurringEvent()) {
					$recurringTitle = $viewTitle . ' - ' . $title . ' - ' . $eventStartDate;
					$doc->title($recurringTitle);

				} elseif(!$event->isRecurringEvent()) {
					$title = $viewTitle . ' - ' . $title;
					$doc->title($title);

				} else {
					$doc->title($title);
				}
			}

			// Event video page
			if ($view == 'albums') {

				if ($event->isRecurringEvent()) {
					$recurringTitle = $viewTitle . ' - ' . $title . ' - ' . $eventStartDate;
					$doc->title($recurringTitle);

				} elseif(!$event->isRecurringEvent()) {
					$title = $viewTitle . ' - ' . $title;
					$doc->title($title);

				} else {
					$doc->title($title);
				}
			}
		}
	}

	/**
	 * Method to retrieve the workflow for this cluster
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getWorkflow()
	{
		$category = $this->getCategory();
		return $category->getWorkflow();
	}

	/**
	 * Method to determine if user can perform digest subscription or not
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function canSubsribeDigest()
	{
		// onwer should always recieve emails.
		if ($this->isOwner()) {
			return false;
		}

		if ($this->isMember()) {
			return true;
		}

		return false;
	}


	/**
	 * Method to determine if user can perform digest subscription or not
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function subscribe($userId, $interval)
	{
		$mapping = array(
				SOCIAL_DIGEST_DEFAULT => 'default',
				SOCIAL_DIGEST_DAILY => 'daily',
				SOCIAL_DIGEST_WEEKLY => 'weekly',
				SOCIAL_DIGEST_MONTHLY => 'monthly'
			);

		$intervalType = isset($mapping[$interval]) ? $mapping[$interval] : 'default';

		$tbl = ES::table('ClustersSubscriptions');
		$tbl->load(array('cluster_id' => $this->id, 'user_id' => $userId));

		$state = true;

		if ($intervalType == 'default') {
			// if member choose default behavior, mean we do not need to process email digest. we will remove
			// the record from table.
			if ($tbl->id) {
				$state = $tbl->delete();
			}
		} else {
			$tbl->cluster_id = $this->id;
			$tbl->user_id = $userId;
			$tbl->interval = $intervalType;

			if (!$tbl->id) {
				$tbl->count = SOCIAL_DIGEST_MAX_COUNT;
				$tbl->sent = ES::date()->toSQL();
				$tbl->created = ES::date()->toSQL();
			}

			$state = $tbl->store();
		}

		return $state;
	}

	/**
	 * Method to determine if user can perform digest subscription or not
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function hasSubsribeDigest($userId)
	{
		static $cache = array();

		$idx = $this->id . '-' . $userId;

		if (isset($cache[$idx])) {
			return $cache[$idx];
		}

		$mapping = array(
				'daily' => SOCIAL_DIGEST_DAILY,
				'weekly' => SOCIAL_DIGEST_WEEKLY,
				'monthly' => SOCIAL_DIGEST_MONTHLY
			);

		$tbl = ES::table('ClustersSubscriptions');
		$tbl->load(array('cluster_id' => $this->id, 'user_id' => $userId));

		$interval = '1'; // default

		if ($tbl->id) {
			$interval = $mapping[$tbl->interval];
		}

		$cache[$idx] = $interval;

		return $cache[$idx];
	}

	/**
	 * Method to invite the user to join all the events in invite group only
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function inviteToEvents($userId, $inviterId = null)
	{
		if (!$this->isInviteOnly()) {
			return;
		}

		// Get events
		$model = ES::model('Events');
		$options = array($this->getType() . '_id' => $this->id, 'state' => SOCIAL_STATE_PUBLISHED);

		$events = $model->getEvents($options);

		if (!$events) {
			return;
		}

		// Get the inviter
		if (!$inviterId) {
			$namespace = ucfirst($this->getType) . 'Member';
			$member = ES::table($namespace);
			$member->load(array('cluster_id' => $this->id, 'uid' => $userId));

			$inviterId = $member->invited_by;
		}

		foreach ($events as $event) {
			$event->invite($userId, $inviterId);
		}

		return true;
	}

	/**
	 * Converts cluster object into an array that can be exported
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function toExportData(SocialUser $viewer, $includeFields = false)
	{
		static $cache = array();

		$key = $this->id . $viewer->id;

		if (isset($cache[$key])) {
			return $cache[$key];
		}

		$result = array(
			'id' => $this->id,
			'title' => $this->getTitle(),
			'permalink' => $this->getPermalink(false, true),
			'avatar' => array(
				'thumbnail' => $this->getAvatar(),
				'large' => $this->getAvatar(SOCIAL_AVATAR_LARGE)
			),
			'cover' => array(
				'large' => $this->getCover()
			),
			'author' => $this->getCreator()->toExportData($viewer),
			'fields' => array()
		);

		$result = (object) $result;

		$cache[$key] = $result;

		return $cache[$key];
	}
}
