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

class EasySocialControllerCover extends EasySocialController
{
	/**
	 * Allows caller to create a cover photo
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function create()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the coordinates
		$x = $this->input->get('x', 0, 'default');
		$y = $this->input->get('y', 0, 'default');

		// Get photo id from request.
		$id = $this->input->get('id', 0, 'int');

		// Load the photo
		$photo = ES::table('Photo');
		$photo->load($id);

		// Get the unique item id
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', '', 'cmd');

		// Check for required variables
		if (!$id || !$photo->id || !$uid || !$type) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_PHOTOS_INVALID_PHOTO_ID_PROVIDED'), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Load up the photo library
		$lib = ES::photo($uid, $type, $photo);

		// Check if the user is allowed to use this photo as a cover.
		if (!$lib->allowUseCover()) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_PHOTOS_NO_PERMISSION_TO_USE_PHOTO_AS_COVER'), ES_ERROR);

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

		// We need to copy this image and put into the cover album, #2746
		if (!$album->isCover()) {

			// Check if there's a profile photos album that already exists.
			$model = ES::model('Albums');

			// Retrieve the user's default album
			$coverAlbum = $model->getDefaultAlbum($uid, $type, SOCIAL_ALBUM_PROFILE_COVERS);

			// Copy the photo now
			$photo = $lib->copyToAlbum($coverAlbum, false);
		}

		// Load the cover
		$cover = ES::table('Cover');
		$state = $cover->load(array('uid' => $uid, 'type' => $type));

		// User does not have a cover.
		if (!$state) {
			$cover->uid = $uid;
			$cover->type = $type;
		}

		// Set the cover to pull from photo
		$cover->setPhotoAsCover($photo->id, $x , $y);

		// Save the cover.
		$cover->store();

		// Check if user is uploading new cover
		$uploadNew = $this->input->get('uploadNew', false, 'bool');

		// @Add stream item when a new profile cover is uploaded
		if ($uploadNew) {
			$photo->addPhotosStream('updateCover');
		} else {
			// First check whether stream exists or not
			$streamId = $lib->getPhotoStreamId($photo->id, 'updateCover');

			// If exists, update the date of existing stream
			if ($streamId) {
				$stream = ES::stream();
				$stream->updateCreated($streamId, null, 'updateCover');

				// Need to unhide the stream if the stream is hidden
				$model = ES::model('Stream');
				$state = $model->unhide($streamId, ES::user()->id);
			} else {

				// If not exists, just create a new one
				$photo->addPhotosStream('updateCover');
			}
		}

		// Set the photo state to 1 since the user has already confirmed to set it as cover
		$photo->state = SOCIAL_STATE_PUBLISHED;
		$photo->store();

		return $this->view->call(__FUNCTION__, $cover);
	}

	/**
	 * Allows caller to upload a photo
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function upload()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the unique item stuffs
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', '', 'cmd');

		if (!$uid && !$type) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Load the photo library now since we have the unique keys
		$lib = ES::photo($uid, $type);

		// Check if the user is allowed to upload cover photos
		if (!$lib->canUploadCovers()) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_NO_PERMISSION_TO_UPLOAD_COVER', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		// Set uploader options
		$options = array('name' => 'cover_file', 'maxsize' => $lib->getUploadFileSizeLimit());

		// Get uploaded file
		$uploader = ES::uploader($options);
		$file = $uploader->getFile(null, 'image');

		// If there was an error getting uploaded file, stop.
		if ($file instanceof SocialException) {
			$this->view->setMessage($file);
			return $this->view->call(__FUNCTION__);
		}

		// Load the image
		$image = ES::image();
		$image->load($file['tmp_name'], $file['name']);

		// Check if there's a profile photos album that already exists.
		$model = ES::model('Albums');

		// Retrieve the user's default album
		$album = $model->getDefaultAlbum($uid, $type, SOCIAL_ALBUM_PROFILE_COVERS);

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
		$photo->assigned_date = ES::date()->toMySQL();

		// Trigger rules that should occur before a photo is stored
		$photo->beforeStore($file, $image);

		// Try to store the photo.
		$state = $photo->store();

		if (!$state) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_ERROR_CREATING_IMAGE_FILES', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// If album doesn't have a cover, set the current photo as the cover.
		if (!$album->hasCover()) {
			$album->cover_id = $photo->id;
			$album->store();
		}

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

		// Trigger rules that should occur after a photo is stored
		$photo->afterStore($file, $image);

		return $this->view->call(__FUNCTION__, $photo);
	}

	/**
	 * Allows caller to remove a photo
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function remove()
	{
		ES::requireLogin();
		ES::checkToken();

		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', '', 'cmd');

		if (!$uid && !$type) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$cover = ES::table('Cover');
		$state = $cover->load(array('uid' => $uid, 'type' => $type));

		if (!$state) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$lib = ES::photo($uid, $type, $cover->photo_id);

		if (!$lib->canDeleteCover($uid)) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_NO_PERMISSION_TO_DELETE_COVER', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$cover->delete();

		return $this->view->call(__FUNCTION__);
	}
}
