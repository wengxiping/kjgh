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

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class SocialPhoto
{
	/**
	 * The current unique owner of the item
	 * @var	int
	 */
	public $uid = null;

	/**
	 * The current unique string of the item
	 * @var	string
	 */
	public $type = null;

	/**
	 * The adapter for photo
	 * @var	string
	 */
	public $adapter = null;

	/**
	 * The album's library
	 * @var	SocialAlbums
	 */
	public $albumLib = null;

	/**
	 * The table mapping for the photo.
	 * @var	SocialTablePhoto
	 */
	public $data = null;

	/**
	 * The table mapping for exif data.
	 * @var SocialTablePhotoMeta
	 */
	public $exif = null;

	/**
	 * The error message
	 * @var	string
	 */
	public $error = null;

	public function __construct($uid, $type, $id = null)
	{
		if ($id instanceof SocialTablePhoto) {
			$this->data = $id;
		} else {
			$table = ES::table('Photo');
			$table->load($id);

			$this->data = $table;
		}

		// Get exif data
		$this->exif = $this->getExifData();

		$this->uid = $uid;
		$this->type = $type;
		$this->albumLib = $this->album();
		$this->adapter = $this->getAdapter($type);

		// Update renderItemOptions to reflect configurable options
		$config = ES::config();
		$this->renderItemOptions['resizeThreshold'] = $config->get('photos.layout.threshold');
	}

	public static function factory($id = null)
	{
		return new self($id);
	}

	private $renderItemOptions = array(
		'viewer' => null,
		'layout' => 'item',
		'size' => 'thumbnail',
		'template' => 'site/photos/albums/default',
		'showNavigation' => false,
		'showToolbar' => true,
		'showInfo' => true,
		'showStats' => true,
		'showResponse' => true,
		'showTags' => true,
		'showForm' => true,
		'resizeMode' => 'contain',
		'resizeThreshold' => 128,
		'resizeUsingCss' => true,
		'openInPopup' => false
	);

	/**
	 * Wraps the provided album
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function renderItem($options = array())
	{
		// Flag to determine if resize mode was enforced
		$resizeModeEnforced = !empty($options['resizeMode']);

		// Normalize render options
		$options = array_merge($this->renderItemOptions, $options);

		// If resize mode was not enforced
		if (!$resizeModeEnforced) {
			$options['resizeMode'] = $options['layout'] == 'item' ? 'cover' : 'contain';
		}

		$viewer = ES::user($options['viewer']);
		$exif = $this->exif;

		// Get the album library
		$albumLib = $this->album();

		// check the photos is it got cluster type e.g. event or group
		$photoCluster = false;
		$photoClusterId = ($this->type != SOCIAL_APPS_GROUP_USER) ? $this->uid : '0';
		$photoClusterType = ($this->type != SOCIAL_APPS_GROUP_USER) ? $this->type : '';

		if ($photoClusterId && $photoClusterType) {
			$photoCluster = ES::cluster($photoClusterType, $photoClusterId);
		}

		if ($this->data->isFeatured()) {
			$options['size'] = SOCIAL_PHOTOS_LARGE;
		}

		$page = ES::page($this->uid);

		// Build user alias
		$creator = $this->data->getPhotoCreator($page);

		$model = ES::model('Albums');
		$totalPhotos = $model->getTotalPhotos($albumLib->data->id);

		if ($totalPhotos < 2) {
			$options['showNavigation'] = false;
		}

		$theme = ES::themes();
		$theme->set('lib', $this);
		$theme->set('tags', $this->data->getTags());
		$theme->set('comments', $this->comments());
		$theme->set('likes', $this->likes());
		$theme->set('shares', $this->reposts($photoClusterId, $photoClusterType));
		$theme->set('albumLib', $albumLib);
		$theme->set('album', $albumLib->data);
		$theme->set('photo', $this->data);
		$theme->set('creator', $creator);
		$theme->set('privacy', $this->privacy());
		$theme->set('options', $options);
		$theme->set('exif', $exif);
		$theme->set('clusterId', $photoClusterId);
		$theme->set('clusterType', $photoClusterType);
		$theme->set('clusterPrivate', $photoCluster && !$photoCluster->isOpen() ? true : false);

		$namespace = $options['template'];

		return $theme->output($namespace);
	}

	/**
	 * Retrieves the album's library
	 *
	 * @since	1.0
	 * @access	public
	 * @return	SocialAlbums
	 */
	public function album()
	{
		return ES::albums($this->data->uid, $this->data->type, $this->data->album_id);
	}

	public function creator()
	{
		return ES::user($this->data->user_id);
	}

	public function privacy()
	{
		// @TODO: Get proper photo privacy
		return ES::privacy();
	}

	/**
	 * Prepare the likes object for the photo item
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function likes()
	{
		$verb = $this->getPhotoVerb();
		$context = SOCIAL_TYPE_PHOTO;
		$id = $this->data->id;

		$streamId = $this->getPhotoStreamId($id, $verb, false);

		$model = ES::model('Stream');
		$aggregated = $model->isAggregated($this->data->id, 'photos');

		if ($aggregated) {
			$streamId = '0';
		} else if ($verb == 'upload') {
			// Stream upload
			$context = SOCIAL_TYPE_STREAM;
			$id = $streamId;
		}

		$options = array();

		// We have to pass this parameter to tell the likes library, this is come from page
		if ($this->type == SOCIAL_TYPE_PAGE) {
			$options['clusterId'] = $this->uid;
		}

		// NOTE:
		// We do not need to do any checking or fix any relations here since liking a photo should always be liking a photo
		// Nothing needs to be done here.

		return ES::likes($id, $context, $verb, $this->type, $streamId, $options);
	}

	/**
	 * Prepare the comments object for the photo item
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function comments()
	{
		// Get the verb to use.
		$verb = $this->getPhotoVerb();

		// The context should always be photos
		$context = SOCIAL_TYPE_PHOTO;

		// The object id should always be the photo id
		$id = $this->data->id;

		$streamId = $this->getPhotoStreamId($id, $verb, false);

		if ($verb == 'upload') {
			// we now this photo is uploaded via stream's story form.
			$model = ES::model('Stream');
			$aggregated = $model->isAggregated($this->data->id, 'photos');

			if ($aggregated) {
				$streamId = '0';
			} else {
				$context = SOCIAL_TYPE_STREAM;
				$id = $streamId;
			}
		}

		// Get the permalink to the photo
		$permalink = $this->data->getPermalink(true, false, 'item', false);
		$options = array('url' => $permalink);

		// Get the cluster id for this photo to generate the comment form.
		if ($this->type != SOCIAL_TYPE_USER) {
			$options['clusterId'] = $this->uid;
		}

		$privacy = ES::user()->getPrivacy();

		if (!$privacy->validate('story.post.comment', $this->data->user_id, SOCIAL_TYPE_USER)) {
			$options['hideForm'] = true;
		}

		return ES::comments($id, $context, $verb, $this->type, $options, $streamId);
	}

	public function reposts($clusterId = 0, $clusterType = '')
	{
		return ES::repost($this->data->id, SOCIAL_TYPE_PHOTO, $this->type, $clusterId, $clusterType);
	}

	public function getPhotoStreamId($photoId, $verb, $validate = true)
	{
		$model = ES::model('Photos');
		return $model->getPhotoStreamId($photoId, $verb, $validate);
	}

	public function getPhotoVerb()
	{
		static $cache = array();

		if (!isset($cache[$this->data->id])) {

			$album	= ES::table('Album');
			$album->load($this->data->album_id);

			// uploadAvatar
			// updateCover
			// share
			// create

			$core = $album->core;
			$verb = 'add';

			if ($core == SOCIAL_ALBUM_PROFILE_PHOTOS) {
				$verb = 'uploadAvatar';
			}

			if ($core == SOCIAL_ALBUM_PROFILE_COVERS) {
				$verb = 'updateCover';
			}

			// When user upload photos in the story, it gets pushed to the story album
			if ($core == SOCIAL_ALBUM_STORY_ALBUM) {

				// Here we need to check if the photo already have a stream id related with 'add'.
				// If none, we use the 'upload'.
				$verb = 'upload';

				// in 3.0, we need to check whether the stream is exist for 'create' verb. #2575
				$streamId = $this->getPhotoStreamId($this->data->id, 'create');

				if ($streamId) {

					// Check for stream aggregation
					$sModel = ES::model('Stream');
					$totalItem = $sModel->getStreamItemsCount($streamId);

					// Single stream detected. We need to change the verb to 'add'
					if ($totalItem == 1) {
						$verb = 'add';
					}
				}
			}

			$cache[$this->data->id] = $verb;
		}

		// dump($cache[$this->data->id]);

		return $cache[$this->data->id];
	}

	/**
	 * Retrieves the total number of photos in an album
	 *
	 * @since	2.0.20
	 * @access	public
	 */
	public function getTotalAlbumPhotos($options = array())
	{
		$total = (int) $this->albumLib->data->getTotalPhotos($options);

		return $total;
	}

	/**
	 * Retrieves a list of photos from an album
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getAlbumPhotos($options = array())
	{
		$photos = $this->albumLib->data->getPhotos($options);

		return $photos['photos'];
	}

	/**
	 * Sets error messages
	 *
	 * @since	1.2.11
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function setError($message)
	{
		$this->error = $message;
	}

	/**
	 * Retrieves error messages
	 *
	 * @since	1.2.11
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getError()
	{
		return $this->error;
	}


	/**
	 * Retrieves error messages
	 *
	 * @deprecated 1.3
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getErrorMessage()
	{
		return $this->getError();
	}

	/**
	 * Maps back the call method functions to the adapter.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function __call($method, $args)
	{
		$refArray = array();

		if ($args) {
			foreach ($args as &$arg) {
				$refArray[]	=& $arg;
			}
		}

		return call_user_func_array(array($this->adapter, $method), $refArray);
	}

	/**
	 * Retrieves the album's adapter
	 *
	 * @since	1.2
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getAdapter($type)
	{
		$type = empty($type) ? 'user' : $type;

		$file = dirname(__FILE__) . '/adapters/' . strtolower($type) . '.php';

		jimport('joomla.filesystem.file');

		if (!JFile::exists($file)) {
			return false;
		}

		require_once($file);

		$className = 'SocialPhotoAdapter' . ucfirst($type);
		$adapter = new $className($this, $this->albumLib);

		return $adapter;
	}

	/**
	 * Get Exif data of the photos
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return  array
	 */
	public function getExifData()
	{
		$model = ES::model('Photos');
		$data = $model->getMeta($this->data->id, 'exif');

		$exif = array();
		$mapArray = array(
			'aperture' => 'aperture',
			'exposure' => 'shutter-speed',
			'focalLength' => 'focal-length',
			'iso' => 'camera-iso',
			'camera' => 'camera-type'
			);

		foreach ($data as $item) {
			if (!array_key_exists($item->property, $mapArray)) {
				continue;
			}

			if (!$item->value) {
				continue;
			}

			$obj = new stdClass();
			$obj->property = $item->property;
			$obj->value = $item->value;
			$obj->class = $mapArray[$item->property];

			$exif[] = $obj;
		}

		return $exif;
	}

	/**
	 * Method to copy the photo to a different album
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function copyToAlbum($destinationAlbum, $processGif = true)
	{
		// Get the source for this photo
		$originalPhoto = $this->data;
		$originalPath = $originalPhoto->getPath('original');

		// Construct file object
		$file = array('tmp_name' => $originalPath, 'name' => $this->data->title);

		// Load the image object
		$image = ES::image();
		$image->load($file['tmp_name'], $file['name']);

		// Bind the photo data now
		$photo = ES::table('Photo');
		$photo->uid = $originalPhoto->uid;
		$photo->type = $originalPhoto->type;
		$photo->user_id = ES::user()->id;
		$photo->caption = '';
		$photo->ordering = 0;
		$photo->state = SOCIAL_STATE_PUBLISHED;

		// Set the destination album
		$photo->album_id = $destinationAlbum->id;

		// Currently, if admin upload a photo in Page's album
		// The actor always be the Page since only page admin able to upload photo in album
		$photo->post_as = $originalPhoto->type == SOCIAL_TYPE_PAGE ? $originalPhoto->type : SOCIAL_TYPE_USER;

		// Generate a proper name for the file rather than using the file name
		$photo->title = $file['name'];

		// Set the creation date alias
		$photo->assigned_date = ES::date()->toMySQL();

		// Cleanup photo title.
		$photo->cleanupTitle();

		// Trigger rules that should occur before a photo is stored
		$photo->beforeStore($file, $image);

		// Try to store the photo.
		$state = $photo->store();

		if (!$state) {
			return false;
		}

		// If destinationAlbum doesn't have a cover, set the current photo as the cover.
		if (!$destinationAlbum->hasCover()) {
			$destinationAlbum->cover_id = $photo->id;

			// Store the destinationAlbum
			$destinationAlbum->store();
		}

		// Get the photos library
		$photoLib = ES::photos($image);

		// Get the storage path for this photo
		$storageContainer = ES::cleanPath(ES::config()->get('photos.storage.container'));
		$storage = $photoLib->getStoragePath($destinationAlbum->id, $photo->id);
		$paths = $photoLib->create($storage, array(), '', $processGif);

		// We need to calculate the total size used in each photo (including all the variants)
		$totalSize = 0;

		// Create metadata about the photos
		if ($paths) {

			foreach ($paths as $type => $fileName) {
				$meta = ES::table('PhotoMeta');
				$meta->photo_id = $photo->id;
				$meta->group = SOCIAL_PHOTOS_META_PATH;
				$meta->property = $type;
				// do not store the container path as this path might changed from time to time
				$tmpStorage = str_replace('/' . $storageContainer . '/', '/', $storage);
				$meta->value = $tmpStorage . '/' . $fileName;
				$meta->store();

				// We need to store the photos dimension here
				list($width, $height, $imageType, $attr) = getimagesize(JPATH_ROOT . $storage . '/' . $fileName);

				// Set the photo size
				$totalSize += filesize(JPATH_ROOT . $storage . '/' . $fileName);

				// Set the photo dimensions
				$meta = ES::table('PhotoMeta');
				$meta->photo_id = $photo->id;
				$meta->group = SOCIAL_PHOTOS_META_WIDTH;
				$meta->property = $type;
				$meta->value = $width;
				$meta->store();

				$meta = ES::table('PhotoMeta');
				$meta->photo_id = $photo->id;
				$meta->group = SOCIAL_PHOTOS_META_HEIGHT;
				$meta->property = $type;
				$meta->value = $height;
				$meta->store();
			}
		}

		// Set the total photo size
		$photo->total_size = $totalSize;
		$photo->store();

		// After storing the photo, trigger rules that should occur after a photo is stored
		$photo->afterStore($file, $image);

		return $photo;
	}
}
