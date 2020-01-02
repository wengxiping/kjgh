<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.filesystem.file');

class EasySocialControllerPhotos extends EasySocialController
{
	/**
	 * Allows caller to upload photos
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function upload($isAvatar = false)
	{
		ES::requireLogin();
		ES::checkToken();

		// Check if the photos is enabled
		if (!$this->config->get('photos.enabled')) {
			return $this->view->exception('COM_EASYSOCIAL_ALBUMS_PHOTOS_DISABLED');
		}

		// Load the album table
		$albumId = $this->input->get('albumId', 0, 'int');

		$album = ES::table('Album');
		$album->load($albumId);

		// Check if the album id provided is valid
		if (!$albumId || !$album->id) {
			return $this->view->exception(JText::_('COM_EASYSOCIAL_ALBUMS_INVALID_ALBUM_ID_PROVIDED'));
		}

		// check if the album belong to the user or not. #2759
		if ($album->type == SOCIAL_TYPE_USER && !$album->isMine($this->my->id) && !$this->my->isSiteAdmin()) {
			// throw 500 exception.
			return $this->view->exception(JText::_('COM_EASYSOCIAL_ALBUMS_INVALID_ALBUM_ID_PROVIDED'));
		}

		if ($album->type != SOCIAL_TYPE_USER) {
			$albumLib = ES::albums($album->uid, $album->type, $album->id);
			$cluster = $albumLib->getCluster();

			if (!$this->my->isSiteAdmin() && !$cluster->isMember()) {
				return $this->view->exception(JText::_('COM_EASYSOCIAL_ALBUMS_INVALID_ALBUM_ID_PROVIDED'));
			}
		}

		$isAlbumFinalized = $album->finalized;

		// Get the uid and the type
		$uid = $album->uid;
		$type = $album->type;

		// if this is an user album and the current login is not the onwer of the album but the current logged in user is a SA, then we need to
		// use the album->uid as this will be the same as user_id column.
		$userId = ($album->type == SOCIAL_TYPE_USER && !$album->isMine($this->my->id) && $this->my->isSiteAdmin()) ? $album->uid : $this->my->id;

		// Load the photo library
		$lib = ES::photo($uid, $type);

		// Check if the upload is for profile pictures
		if (!$isAvatar) {

			// Check if the person exceeded the upload limit
			if ($lib->exceededUploadLimit()) {
				$this->view->setMessage($lib->getErrorMessage('upload.exceeded'), ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			// Check if the person exceeded the upload limit
			if ($lib->exceededDiskStorage()) {
				$this->view->setMessage($lib->getErrorMessage(), ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			// Check if the person exceeded their daily upload limit
			if ($lib->exceededDailyUploadLimit()) {
				$this->view->setMessage($lib->getErrorMessage('upload.daily.exceeded'), ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}
		}

		// Set uploader options
		$options = array('name' => 'file', 'maxsize' => $lib->getUploadFileSizeLimit());

		// Get uploaded file
		$uploader = ES::uploader($options);
		$file = $uploader->getFile(null, 'image');

		// If there was an error getting uploaded file, stop.
		if ($file instanceof SocialException) {
			$this->view->setMessage($file);
			return $this->view->call(__FUNCTION__);
		}

		// Load the image object
		$image = ES::image();
		$image->load($file['tmp_name'], $file['name']);

		// Detect if this is a really valid image file.
		if (!$image->isValid()) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_FILE_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Bind the photo data now
		$photo = ES::table('Photo');
		$photo->uid = $uid;
		$photo->type = $type;
		$photo->user_id = $userId;
		$photo->album_id = $album->id;
		$photo->caption = '';
		$photo->ordering = 0;
		$photo->state = SOCIAL_STATE_PUBLISHED;

		// Currently, if admin upload a photo in Page's album
		// The actor always be the Page since only page admin able to upload photo in album
		$photo->post_as = $type == SOCIAL_TYPE_PAGE ? $type : SOCIAL_TYPE_USER;

		// Generate a proper name for the file rather than using the file name
		$photo->title = $photo->generateTitle();

		// Set the creation date alias
		$photo->assigned_date = ES::date()->toMySQL();

		// Cleanup photo title.
		$photo->cleanupTitle();

		// Trigger rules that should occur before a photo is stored
		$photo->beforeStore($file, $image);

		// Try to store the photo.
		$state = $photo->store();

		if (!$state) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_UPLOAD_ERROR_STORING_DB', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// If album doesn't have a cover, set the current photo as the cover.
		if (!$album->hasCover()) {
			$album->cover_id = $photo->id;

			// Store the album
			$album->store();
		}

		// Get the photos library
		$photoLib = ES::photos($image);

		// Get the storage path for this photo
		$storageContainer = ES::cleanPath($this->config->get('photos.storage.container'));
		$storage = $photoLib->getStoragePath($album->id, $photo->id);
		$paths = $photoLib->create($storage);

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

		// Determine if we should create a stream item for this upload
		$createStream = JRequest::getBool('createStream');

		// Add Stream when a new photo is uploaded
		if ($isAlbumFinalized && $createStream) {
			$photo->addPhotosStream('create');
		}

		// if albums is not finalized yet, let set the photo to only_me
		if (! $isAlbumFinalized) {
			$lib = ES::privacy();
			$lib->add('photos.view', $photo->id, 'photos', 'only_me', null);
		}


		// Assign badge to user
		$photo->assignBadge('photos.create', $this->my->id);

		if ($isAvatar) {
			return $photo;
		}

		return $this->view->call(__FUNCTION__, $photo, $paths);
	}


	/**
	 * Posting photos via story
	 *
	 * @since   2.0.14
	 * @access  public
	 */
	public function uploadStory()
	{
		ES::requireLogin();

		// Check for request forgeries
		ES::checkToken();

		// Get user access
		$access = $this->my->getAccess();

		// Get the uid and type
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', '', 'cmd');

		// Load up the photo library
		$lib = ES::photo($uid, $type);

		// Check if the photos is enabled
		if (!$this->config->get('photos.enabled')) {
			return $this->view->exception('COM_EASYSOCIAL_ALBUMS_PHOTOS_DISABLED');
		}

		// Determines if the person can upload photos
		if (!$lib->canUploadPhotos()) {
			return $this->view->exception('COM_EASYSOCIAL_ALBUMS_PHOTOS_DISABLED');
		}

		// Determines if the person exceeded their upload limit
		if ($lib->exceededUploadLimit()) {
			$this->view->setMessage($lib->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Determines if the person exceeded their daily upload limit
		if ($lib->exceededDailyUploadLimit()) {
			$this->view->setMessage($lib->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Define uploader options
		$options = array('name' => 'file', 'maxsize' => $lib->getUploadFileSizeLimit());

		// Get uploaded file
		$uploader = ES::uploader($options);
		$file = $uploader->getFile(null, 'image');

		// If there was an error getting uploaded file, stop.
		if ($file instanceof SocialException) {
			$this->view->setMessage($file, ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Load the iamge object
		$image = ES::image();
		$image->load($file['tmp_name'], $file['name']);

		// Detect if this is a really valid image file.
		if (!$image->isValid()) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_PHOTOS_INVALID_FILE_PROVIDED'), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Load up the album's model.
		$albumsModel = ES::model('Albums');

		// Create the default album if necessary
		$album = $albumsModel->getDefaultAlbum($uid, $type, SOCIAL_ALBUM_STORY_ALBUM);

		// Bind photo data
		$photo = ES::table('Photo');
		$photo->uid = $uid;
		$photo->type = $type;
		$photo->user_id = $this->my->id;
		$photo->album_id = $album->id;

		$photo->title = $file['name'];
		$photo->cleanupTitle();

		$photo->caption = '';
		$photo->ordering = 0;

		// Set the creation date alias
		$photo->assigned_date = ES::date()->toMySQL();

		// Trigger rules that should occur before a photo is stored
		$photo->beforeStore($file, $image);

		// Try to store the photo.
		$state = $photo->store();

		if (!$state) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_PHOTOS_UPLOAD_ERROR_STORING_DB'), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Load the photos model
		$photosModel = ES::model('Photos');

		// Get the storage path for this photo
		$storage = ES::call('Photos', 'getStoragePath', array($album->id, $photo->id));

		// Get the photos library
		$photoLib = ES::get('Photos', $image);
		$paths = $photoLib->create($storage);

		// Create metadata about the photos
		if ($paths) {

			foreach ($paths as $type => $fileName) {

				$meta = ES::table('PhotoMeta');
				$meta->photo_id = $photo->id;
				$meta->group = SOCIAL_PHOTOS_META_PATH;
				$meta->property = $type;
				$meta->value = $storage . '/' . $fileName;
				$meta->store();

				// We need to store the photos dimension here
				list($width, $height, $imageType, $attr) = getimagesize(JPATH_ROOT . $storage . '/' . $fileName);

				// Set the photo dimensions
				$meta = ES::table('PhotoMeta');
				$meta->photo_id = $photo->id;
				$meta->group = SOCIAL_PHOTOS_META_WIDTH;
				$meta->property = $type;
				$meta->value = $width;
				$meta->store();

				// Set the photo height
				$meta = ES::table('PhotoMeta');
				$meta->photo_id = $photo->id;
				$meta->group = SOCIAL_PHOTOS_META_HEIGHT;
				$meta->property = $type;
				$meta->value = $height;
				$meta->store();
			}
		}

		// Assign badge to user
		$photo->assignBadge('photos.create', $this->my->id);

		// After storing the photo, trigger rules that should occur after a photo is stored
		$photo->afterStore($file, $image);

		return $this->view->call(__FUNCTION__, $photo, $paths, $width, $height);
	}


	/**
	 * Allows caller to update a photo
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function update()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$photo = ES::table('Photo');
		$photo->load($id);

		if (!$id || !$photo->id) {
			return $this->view->exception('COM_EASYSOCIAL_PHOTOS_NOT_FOUND');
		}

		$lib = ES::photo($photo->uid, $photo->type, $photo);

		// Test if the user is really allowed to edit the photo
		if (!$lib->editable()) {
			return $this->view->exception('COM_EASYSOCIAL_PHOTOS_NOT_ALLOWED_TO_EDIT_PHOTO');
		}

		// Get the posted data
		$post = JRequest::get('post');

		// Should we allow the change of the album?
		$photo->title = $this->input->get('title', '', 'string');
		$photo->caption = $this->input->get('caption', '', 'raw');

		// Set the assigned_date if necessary
		$photoDate = $this->input->get('date', '', 'default');

		if ($photoDate) {
			$date = ES::date($photoDate);
			$photo->assigned_date = $date->toMySQL();
		}

		// Try to store the photo now
		$state = $photo->store();

		if (!$state) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_ERROR_SAVING_PHOTO', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Bind the location for the photo if necessary
		if ($this->config->get('photos.location')) {
			$address = $this->input->get('address', '', 'string');
			$latitude = $this->input->get('latitude', '', 'default');
			$longitude = $this->input->get('longitude', '', 'default');

			if ($address && $latitude && $longitude) {
				$photo->bindLocation($address, $latitude, $longitude);
			}
		}

		return $this->view->call(__FUNCTION__, $photo);
	}

	/**
	 * Allows caller to delete an album
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function delete()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get id from request
		$ids = $this->input->get('ids', array(), 'array');

		if (!$ids) {
			$id = $this->input->get('id', 0, 'int');

			if ($id) {
				$ids[] = $id;
			}
		}

		if (!$ids) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		foreach ($ids as $id) {

			$photo = ES::table('Photo');
			$photo->load($id);

			if (!$photo->id) {
				$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_ID_PROVIDED', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			// Load the photo library
			$lib = ES::photo($photo->uid, $photo->type, $photo);

			// Test if the user is allowed to delete the photo
			if (!$lib->deleteable()) {
				$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_NO_PERMISSION_TO_DELETE_PHOTO', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			// Try to delete the photo
			$state = $photo->delete();

			if (!$state) {
				$this->view->setMessage($photo->getError(), ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}
		}

		// Get the new cover
		$newCover = $photo->getAlbum()->getCoverObject();

		return $this->view->call(__FUNCTION__, $newCover);
	}

	/**
	 * Allows caller to rotate a photo
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function rotate()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		// Get photo
		$photo = ES::table('Photo');
		$photo->load($id);

		if (!$id || !$photo->id) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_PHOTO_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Determine if the user has access to rotate the photo
		$lib = ES::photo($photo->uid, $photo->type, $photo);

		if (!$lib->canRotatePhoto()) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_NOT_ALLOWED_TO_ROTATE_THIS_PHOTO', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// here we need to check if the current photo are stored in amazon. if yes, lets download back to server
		// for further processing.
		if ($photo->storage != SOCIAL_STORAGE_JOOMLA) {

			// Get the relative path to the photo
			$photoFolder = $photo->getFolder(true, false);

			// call the api to retrieve back the data
			$storage = ES::storage($photo->storage);
			$storage->pull($photoFolder);
		}


		// Rotate photo
		$tmpAngle = $this->input->get('angle', 0, 'int');

		// Get the real angle now.
		$angle = $photo->getAngle() + $tmpAngle;

		// Update the angle
		$photo->updateAngle($angle);

		// Rotate the photo
		$image = ES::image();
		$image->load($photo->getPath('original'));

		// Rotate the new image
		$image->rotate($tmpAngle);

		// Save photo
		$photoLib = ES::get('Photos', $image);

		// Get the storage path
		$storage = $photoLib->getStoragePath($photo->album_id, $photo->id);

		// Prevent stock photo from being override
		$exclude = array('stock');

		// Rename the photo to avoid browser cache
		$paths = $photoLib->create($storage, $exclude, $photo->title . '_rotated_' . $angle);

		// Delete the previous images that are generated except the stock version
		$photo->deletePhotos(array('thumbnail', 'large', 'original'));

		// When a photo is rotated, we would also need to rotate the tags as well
		$photo->rotateTags($tmpAngle);

		// Create metadata about the photos
		foreach ($paths as $type => $fileName) {

			$meta = ES::table('PhotoMeta');
			$meta->photo_id = $photo->id;
			$meta->group = SOCIAL_PHOTOS_META_PATH;
			$meta->property = $type;
			$meta->value = '/' . $photo->album_id . '/' . $photo->id . '/' . $fileName;
			$meta->store();

			// We need to store the photos dimension here
			list($width, $height, $imageType, $attr) = getimagesize(JPATH_ROOT . $storage . '/' . $fileName);

			// Delete previous meta data first
			$photo->updateMeta(SOCIAL_PHOTOS_META_WIDTH, $type, $width);
			$photo->updateMeta(SOCIAL_PHOTOS_META_HEIGHT, $type, $height);
		}

		// Reload photo
		$newPhoto = ES::table('Photo');
		$newPhoto->load($id);

		// Once image is rotated, we'll need to update the photo source back to "joomla" because
		// we will need to re-upload the image again to remote server when synchroinization happens.
		$newPhoto->storage = SOCIAL_STORAGE_JOOMLA;
		$newPhoto->store();

		return $this->view->call(__FUNCTION__, $newPhoto, $paths);
	}

	/**
	 * Allows caller to feature a photo
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function feature()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$photo = ES::table('Photo');
		$photo->load($id);

		if (!$id || !$photo->id) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Load up photo library
		$lib = ES::photo($photo->uid, $photo->type, $photo);

		// Test if the person is allowed to feature the photo
		if (!$lib->featureable()) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_NOT_ALLOWED_TO_FEATURE_PHOTO', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// If photo is previously not featured, it is being featured now.
		$isFeatured = !$photo->featured ? true : false;

		// Toggle the featured state
		$photo->toggleFeatured();

		return $this->view->call(__FUNCTION__, $isFeatured);
	}

	/**
	 * Allows caller to move a photo over to album
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function move()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$ids = $this->input->get('ids', array(), 'array');
		$albumId = $this->input->get('value', '', 'default');

		if ($id) {
			$ids[] = $id;
		}

		foreach ($ids as $id) {

			$photo = ES::table('Photo');
			$photo->load($id);

			// Only allow valid photos
			if (!$id || !$photo->id) {
				$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_ID_PROVIDED', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			// Get the target album id to move this photo to.
			if (!$albumId) {
				$albumId = $this->input->get('albumId', 0, 'int');
			}

			$album = ES::table('Album');
			$album->load($albumId);

			if (!$albumId || !$album->id) {
				$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_ALBUM_ID_PROVIDED', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			// Load the library
			$lib = ES::photo($photo->uid, $photo->type, $photo);

			// Check if the user can actually manage this photo
			if (!$lib->canMovePhoto()) {
				$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_NO_PERMISSION_TO_MOVE_PHOTO', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			// Load up the target album
			$albumLib = ES::albums($album->uid, $album->type, $album);

			// Check if the target album is owned by the user
			if (!$albumLib->isOwner()) {
				$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_NO_PERMISSION_TO_MOVE_PHOTO', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			// Try to move the photo to the new album now
			if (!$photo->move($albumId)) {
				$this->view->setMessage($photo->getError(), ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}
		}

		$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_PHOTO_MOVED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Deletes a tag
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function deleteTag()
	{
		ES::requireLogin();
		ES::checkToken();

		// Load the tag object
		$id = $this->input->get('tag_id', 0, 'int');
		$tag = ES::table('PhotoTag');
		$tag->load($id);

		// Get posted data from request
		$post = JRequest::get('POST');

		// Get the person that created the tag
		$creator = ES::user($tag->created_by);

		// Determines if the tag can be deleted
		if (!$tag->deleteable()) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_NOT_ALLOWED_TO_DELETE_TAG', ES_ERROR);
			$this->view->call(__FUNCTION__);
		}

		// Try to delete the tag
		if (!$tag->delete()) {
			$this->view->setMessage($tag->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Deduct points from the user that created the tag since the tag has been deleted.
		$photo->assignPoints('photos.untag', $creator->id);

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Creates a new tag
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function createTag()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the photo id from the request.
		$id = $this->input->get('photo_id', 0, 'int');

		// Load up the photo table
		$photo = ES::table('Photo');
		$photo->load($id);

		// Check if the photo id is valid
		if (!$id || !$photo->id) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_PHOTO_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__, null, $photo);
		}

		// Load up the photo library
		$lib = ES::photo($photo->uid, $photo->type, $photo);

		// Test if the user is really allowed to tag this photo
		if (!$lib->taggable()) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_NOT_ALLOWED_TO_TAG_PHOTO', ES_ERROR);
			return $this->view->call(__FUNCTION__, null, $photo);
		}

		// Get posted data from request
		$post = JRequest::get('POST');

		// Bind the new data on the post
		$tag = ES::table('PhotoTag');
		$tag->bind($post);

		// If there's empty label and the uid is not supplied, we need to throw an error
		if (empty($tag->label) && !$tag->uid) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_EMPTY_TAG_NOT_ALLOWED', ES_ERROR);
			return $this->view->call(__FUNCTION__, null, $photo);
		}

		// Reset the id of the tag since this is a new tag, it should never contain an id
		$tag->id = null;
		$tag->photo_id = $photo->id;
		$tag->created_by = $this->my->id;

		// Try to save the tag now
		$state  = $tag->store();

		// Try to store the new tag.
		if (!$state) {
			$this->view->setMessage($tag->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__, null, $photo);
		}

		// @points: photos.tag
		// Assign points to the current user for tagging items
		$photo->assignPoints('photos.tag', $this->my->id);

		// Only notify persons if the photo is tagging a person
		if ($tag->uid && $tag->type == 'person' && $tag->uid != $this->my->id) {

			// need to check if user that being tag allow to access the photo / albums or not.
			$notify = true;

			if ($photo->type != SOCIAL_TYPE_USER) {
				$cluster = ES::cluster($photo->type, $photo->uid);
				if ($cluster->isInviteOnly() && !$cluster->canViewItem($tag->uid)) {
					$notify = false;
				}
			}

			if ($notify) {
				// Set the email options
				$emailOptions = array(
					'title' => 'COM_EASYSOCIAL_EMAILS_TAGGED_IN_PHOTO_SUBJECT',
					'template' => 'site/photos/tagged',
					'photoTitle' => $photo->get('title'),
					'photoPermalink' => $photo->getPermalink(true, true),
					'photoThumbnail' => $photo->getSource('thumbnail'),
					'actor' => $this->my->getName(),
					'actorAvatar' => $this->my->getAvatar(SOCIAL_AVATAR_SQUARE),
					'actorLink' => $this->my->getPermalink(true, true)
				);

				$systemOptions = array(
					'context_type' => 'tagging',
					'context_ids' => $photo->id,
					'uid' => $tag->id,
					'url' => $photo->getPermalink(false, false, 'item', false),
					'actor_id' => $this->my->id,
					'target_id' => $tag->uid,
					'aggregate' => false
				);

				// Notify user
				ES::notify('photos.tagged', array($tag->uid), $emailOptions, $systemOptions);
			}

			// Assign a badge to the user
			$photo->assignBadge('photos.tag', $this->my->id);

			// Assign a badge to the user that is being tagged
			if ($this->my->id != $tag->uid) {
				$photo->assignBadge('photos.superstar', $tag->uid);
			}
		}

		return $this->view->call(__FUNCTION__, $tag, $photo);
	}

	/**
	 * Allows caller to retrieve a list of tags
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getTags()
	{
		ES::checkToken();

		// Get the photo object.
		$id = $this->input->get('photo_id', 0, 'int');
		$photo = ES::table('Photo');
		$photo->load($id);

		if (!$id || !$photo->id) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_PHOTO_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Retrieve the list of tags for this photo
		$tags = $photo->getTags();

		return $this->view->call(__FUNCTION__, $tags);
	}

	/**
	 * Allows caller to remove a tag
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function removeTag()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the tag object
		$id = $this->input->get('id', 0, 'int');

		$tag = ES::table('PhotoTag');
		$tag->load($id);

		if (!$id || !$tag->id) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_TAG_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// If user is not allowed to delete the tag, throw an error
		if (!$tag->deleteable()) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_NOT_ALLOWED_TO_DELETE_TAG', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Try to delete the tag.
		$state = $tag->delete();

		if (!$state) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_ERROR_REMOVING_TAG', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allows caller to set profile photo based on the photo that they have.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function createAvatar()
	{
		ES::checkToken();
		ES::requireLogin();

		// Get the photo id
		$id = $this->input->get('id', 0, 'int');

		// Try to load the photo.
		$photo = ES::table('Photo');
		$photo->load($id);

		// Try to load the photo with the provided id.
		if (!$id || !$photo->id) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_ID_PROVIDED', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		// Get the photos lib
		$lib = ES::photo($photo->uid, $photo->type, $photo);

		if (!$lib->canUseAvatar()) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_NO_PERMISSION_TO_USE_PHOTO_AS_AVATAR', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// here we need to check if the current photo are stored in amazon. if yes, lets download back to server
		// for further processing.
		if ($photo->storage != SOCIAL_STORAGE_JOOMLA) {

			// Get the relative path to the photo
			$photoFolder = $photo->getFolder(true, false);

			// call the api to retrieve back the data
			$storage = ES::storage($photo->storage);
			$storage->pull($photoFolder);
		}

		$album = $photo->getAlbum();

		// We need to copy this image and put into the avatar album, #2746
		if (!$album->isAvatar()) {

			// Retrieve the default avatar album for this node.
			$coverAlbum = $lib->getDefaultAlbum();

			// Copy the photo now
			$photo = $lib->copyToAlbum($coverAlbum, false);
		}

		// Get the image object for the photo
		// Use "original" not "stock" because it might be rotated before this.
		$image = $photo->getImageObject('original');

		// Need to rotate as necessary here because we're loading up using the stock photo and the stock photo
		// is as is when the user initially uploaded.
		$image->rotate($photo->getAngle());

		// Store the image temporarily
		$tmp = $this->jconfig->getValue('tmp_path');
		$tmpPath = $tmp . '/' . md5($photo->id) . $image->getExtension();

		// If the temporary file exists, we need to delete it first
		if (JFile::exists($tmpPath)) {
			JFile::delete($tmpPath);
		}

		$image->save($tmpPath);
		unset($image);

		// if this photo is stored remotely, we will need these downloaded files for later
		// cron action to sync the avatar. the clean up will be perform at avatar sync. @20170413 #681

		// If photo was stored remotely, we need to delete the downloaded files
		// if ($photo->isStoredRemotely()) {
		// 	$photo->deletePhotoFolder();
		// }

		$image = ES::image();
		$image->load($tmpPath);

		// Load up the avatar library
		$avatar = ES::avatar($image, $photo->uid, $photo->type);

		// Crop the image to follow the avatar format. Get the dimensions from the request.
		$width = JRequest::getVar('width');
		$height = JRequest::getVar('height');
		$top = JRequest::getVar('top');
		$left = JRequest::getVar('left');

		// We need to get the temporary path so that we can delete it later once everything is done.
		$avatar->crop($top, $left, $width, $height);

		// Create the avatars now
		$avatar->store($photo);

		// Delete the temporary file.
		JFile::delete($tmpPath);

		return $this->view->call(__FUNCTION__, $photo);
	}

	/**
	 * Allows caller to create an avatar by posted the $_FILE data
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function createAvatarFromFile()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the unique item id
		$uid = $this->input->get('id', 0, 'int');
		$type = $this->input->get('type', '', 'cmd');

		if (!$uid && !$type) {
			return $this->view->exception('COM_EASYSOCIAL_PHOTOS_INVALID_ID_PROVIDED');
		}

		// Load up the photo library
		$lib = ES::photo($uid, $type);

		// Set uploader options
		$options = array('name' => 'avatar_file', 'maxsize' => $lib->getUploadFileSizeLimit());

		// Get uploaded file
		$uploader = ES::uploader($options);
		$file = $uploader->getFile(null, 'image');

		// If there was an error getting uploaded file, stop.
		if ($file instanceof SocialException) {
			$this->view->setMessage($file, ES_ERROR);
			return $this->view->call('createAvatar');
		}

		// Load the image
		$image = ES::image();
		$image->load($file['tmp_name'], $file['name']);

		// Check if there's a profile photos album that already exists.
		$albumModel = ES::model('Albums');

		// Retrieve the default album for this node.
		$album = $lib->getDefaultAlbum();

		$photo = ES::table('Photo');
		$photo->uid = $uid;
		$photo->type = $type;

		// Unable to store the current logged in user id now
		// since we allowed site admin to edit other user avatar and cover on frontend
		$photo->user_id = $uid;

		// if this photo upload from clusters page, then need to store the cluster owner user id
		if (in_array($type, array(SOCIAL_TYPE_GROUP, SOCIAL_TYPE_EVENT, SOCIAL_TYPE_PAGE))) {

			$photo->user_id = $this->my->id;

			$cluster = ES::cluster($type, $uid);

			if ($cluster->creator_uid) {
				$photo->user_id = $cluster->creator_uid;
			}
		}

		$photo->album_id = $album->id;

		$photo->title = $file['name'];
		$photo->cleanupTitle();

		$photo->caption = '';
		$photo->ordering = 0;

		// Set the creation date alias
		$photo->assigned_date = ES::date()->toMySQL();

		// We need to set the photo state to "SOCIAL_PHOTOS_STATE_TMP"
		$photo->state = SOCIAL_PHOTOS_STATE_TMP;

		// Currently, if admin upload a photo for Page's avatar
		// The actor always be the Page since only page admin able to change avatar
		$photo->post_as = $type == SOCIAL_TYPE_PAGE ? $type : SOCIAL_TYPE_USER;

		// Try to store the photo first
		$state = $photo->store();

		// Bind any exif data if there are any.
		// Only bind exif data for jpg files (if want to add tiff, then do add it here)
		if ($image->hasExifSupport()) {
			$photo->mapExif($file);
		}

		if (!$state) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_ERROR_CREATING_IMAGE_FILES', ES_ERROR);
			return $this->view->call('createAvatar');
		}

		// Push all the ordering of the photo down
		$photosModel = ES::model('photos');
		$photosModel->pushPhotosOrdering($album->id, $photo->id);

		// Render photos library
		$photoLib = ES::get('Photos', $image);
		$storage = $photoLib->getStoragePath($album->id, $photo->id);
		$paths = $photoLib->create($storage, array(), '', false);

		// Create metadata about the photos
		foreach ($paths as $type => $fileName) {
			$meta = ES::table('PhotoMeta');
			$meta->photo_id = $photo->id;
			$meta->group = SOCIAL_PHOTOS_META_PATH;
			$meta->property = $type;
			$meta->value = $storage . '/' . $fileName;

			$meta->store();
		}

		// Retrieve the original photo again.
		$image = $photo->getImageObject('original');

		return $this->view->call('createAvatar', $photo);
	}

	/**
	 * Allows caller to create an avatar by posted the $_FILE data
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function createAvatarFromWebcam()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the unique item id
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', '', 'cmd');

		$filename = JRequest::getVar('file');

		if (!$uid && !$type) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call('createAvatar');
		}

		$tmp = $this->jconfig->getValue('tmp_path');
		$filePath = $tmp . '/' . $filename;

		// Load the image
		$image = ES::image();
		$image->load($filePath);

		$avatar = ES::avatar($image, $uid, $type);

		// Check if there's a profile photos album that already exists.
		$albumModel = ES::model('Albums');

		// Retrieve the default album for this node.
		$album = $albumModel->getDefaultAlbum($uid, $type, SOCIAL_ALBUM_PROFILE_PHOTOS);

		// we need to update the album user_id to this current user.
		$album->user_id = $uid;
		$album->store();

		$photo = ES::table('Photo');
		$photo->uid = $uid;
		$photo->type = $type;
		$photo->user_id = $uid;
		$photo->album_id = $album->id;
		$photo->title = $filename;
		$photo->caption = '';
		$photo->ordering = 0;

		// Set the creation date alias
		$photo->assigned_date = ES::date()->toMySQL();

		// We need to set the photo state to "SOCIAL_PHOTOS_STATE_TMP"
		$photo->state = SOCIAL_PHOTOS_STATE_TMP;

		// Try to store the photo first
		$state = $photo->store();

		if (!$state) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_ERROR_CREATING_IMAGE_FILES', ES_ERROR);
			return $this->view->call('createAvatar');
		}

		// Push all the ordering of the photo down
		$photosModel = ES::model('photos');
		$photosModel->pushPhotosOrdering($album->id, $photo->id);

		// Render photos library
		$photoLib = ES::get('Photos', $image);
		$storage = $photoLib->getStoragePath($album->id, $photo->id);
		$paths = $photoLib->create($storage);

		// Create metadata about the photos
		foreach ($paths as $type => $fileName) {
			$meta = ES::table('PhotoMeta');
			$meta->photo_id = $photo->id;
			$meta->group = SOCIAL_PHOTOS_META_PATH;
			$meta->property = $type;
			$meta->value = $storage . '/' . $fileName;

			$meta->store();
		}

		// Save as avatar
		$options = array('addstream' => false);
		$avatar->store($photo, $options);

		// Add stream item
		$photo->addPhotosStream('uploadAvatar', $photo->assigned_date);

		// Retrieve the original photo again.
		$image = $photo->getImageObject('original');

		return $this->view->call('createAvatar', $photo);
	}
}
