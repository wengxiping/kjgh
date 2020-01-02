<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/tables/table');

class SocialTableFile extends SocialTable
{
	/**
	 * The unique id of the file.
	 * @var int
	 */
	public $id = null;

	/**
	 * Determines if the file is stored in a collection
	 * @var int
	 */
	public $collection_id = null;

	/**
	 * The name for the file.
	 * @var string
	 */
	public $name = null;

	/**
	 * The hit count for the file.
	 * @var string
	 */
	public $hits = null;

	/**
	 * The unique file name for the file.
	 * @var string
	 */
	public $hash = null;

	/**
	 * Sub part of this file path if available.
	 * /sub/uid/xxxx.jpg
	 * @var string
	 */
	public $sub = null;

	/**
	 * The unique id for this file.
	 * @var int
	 */
	public $uid = null;

	/**
	 * The unique type for this file.
	 * @var string
	 */
	public $type = null;

	/**
	 * The date time the file has been created.
	 * @var datetime
	 */
	public $created = null;

	/**
	 * The owner of this file.
	 * @var int
	 */
	public $user_id = null;

	/**
	 * The size of this file.
	 * @var string
	 */
	public $size = null;

	/**
	 * The mime type of this file.
	 * @var string
	 */
	public $mime = null;

	/**
	 * The state of the uploaded file.
	 * @var int
	 */
	public $state = null;

	/**
	 * The storage type of the uploaded file.
	 * @var int
	 */
	public $storage = null;

	public function __construct($db)
	{
		parent::__construct('#__social_files', 'id', $db);
	}

	/**
	 * Override parent's implementation.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function loadByType($uid, $type)
	{
		$db = ES::db();

		$query = 'SELECT * FROM ' . $db->nameQuote($this->_tbl)
				. ' WHERE ' . $db->nameQuote('uid') . '=' . $db->Quote($uid)
				. ' AND ' . $db->nameQuote('type') . '=' . $db->Quote($type);

		$db->setQuery($query);
		$obj = $db->loadObject();

		return parent::bind($obj);
	}

	/**
	 * Returns the formatted file size
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getSize($format = 'kb')
	{
		$size = $this->size;

		switch ($format) {
			case 'kb':
			default:
				$size = round($this->size / 1024);
				break;
		}

		return $size;
	}

	/**
	 * Retrieves the icon type.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getIconClass()
	{
		// Image files
		if ($this->mime == 'image/jpeg') {
			return 'album';
		}

		// Zip files
		if ($this->mime == 'application/zip') {
			return 'zip';
		}

		// Txt files
		if ($this->mime == 'text/plain') {
			return 'text';
		}

		// SQL files
		if ($this->mime == 'text/x-sql') {
			return 'sql';
		}

		// Php files
		if ($this->mime == 'text/x-php') {
			return 'php';
		}

		if ($this->mime == 'text/x-sql') {
			return 'sql';
		}

		if ($this->mime == 'application/pdf') {
			return 'pdf';
		}

		return 'unknown';
	}

	/**
	 * Determines if this file is preview-able.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function hasPreview()
	{
		$allowed = array('image/jpeg', 'image/png', 'image/gif');

		if (in_array($this->mime, $allowed)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current user is the owner of this item.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function isOwner($userId)
	{
		if ($this->user_id == $userId) {
			return true;
		}

		return false;
	}

	/**
	 * Resizes an image file
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function resize($width, $height)
	{
		$config = ES::config();

		$width = (int) $width;
		$height = (int) $height;

		// Get the storage path to this image
		$imageName = $this->hash;
		$path = $this->getStoragePath() . '/' . $imageName;

		$image = ES::image();
		$image->load($path);

		// Determine if this image is a gif and if we should process it.
		if ($config->get('photos.gif.enabled') && $image->isAnimated()) {

			// Image in comments only contain one variation
			$sizes = array(
				'resized' => array(
					'width'  => $width,
					'height' => $height
				)
			);

			// Try to process the gif now
			$gif = $image->saveGif($this->getStoragePath(true), $imageName, $sizes);

			// Post processing for the gif image
			if ($gif) {
				$md5 = md5(ES::date()->toSql());
				$storage = SOCIAL_TMP . '/' . $md5 . '.zip';
				JFile::write($storage, $gif);

				// Extract the zip file
				jimport('joomla.filesystem.archive');
				$zipAdapter = JArchive::getAdapter('zip');
				$zipAdapter->extract($storage, $this->getStoragePath());

				// cleanup tmp storage
				JFile::delete($storage);

				// Delete original image
				JFile::delete($path);

				// Rename the process file with the original name
				$gifFileName = $image->generateFileName('resized', $imageName, '.gif');

				JFile::move($this->getStoragePath() . '/' . $gifFileName, $this->getStoragePath() . '/' . $imageName);

				return true;
			}
		}

		// Perform a normal resizing
		$image->fit($width, $height);

		$tmp = $path . '_2';
		$state = $image->save($tmp);

		// Delete the main file first
		JFile::delete($path);

		// Rename the temporary stored file to the original file name
		JFile::move($tmp, $path);

		return $state;
	}

	/**
	 * Gets the formatted date of the uploaded date.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getCreator()
	{
		$creator = ES::user($this->user_id);

		return $creator;
	}

	/**
	 * Gets the formatted date of the uploaded date.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getUploadedDate()
	{
		$date = ES::date($this->created);

		return $date;
	}

	/**
	 * Override parent's delete behavior
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function delete($pk = null, $appendPath = '', $delStream = true)
	{
		// Get the storage path
		$path = $this->getStoragePath(true);

		if ($appendPath) {
			$path .= '/' . $appendPath;
		}

		$path = $path . '/' . $this->hash;

		$storage = ES::storage($this->storage);
		$state = $storage->delete($path);

		if ($delStream) {
			// Delete the stream item related to this file
			ES::stream()->delete($this->id, SOCIAL_TYPE_FILES);
		}

		if (!$state) {
			$this->setError(JText::_('Unable to delete the file from ' . $storage));
			return false;
		}

		// Once the file is deleted, delete the record from the database.
		parent::delete();

		return true;
	}

	/**
	 * Determines if the file is delete-able by the user.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function deleteable($id = null)
	{
		$user = ES::user($id);

		if ($this->user_id == $user->id) {
			return true;
		}

		return false;
	}

	/**
	 * Returns the absolute uri to the item.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getURI()
	{
		$config = ES::config();

		if ($this->storage != 'joomla') {
			$storage = ES::storage($this->storage);
			$path = $this->getStoragePath(true);
			$path = $path . '/' . $this->hash;

			return $storage->getPermalink($path);
		}

		$path = $this->getStoragePath(true);
		$path = $path . '/' . $this->hash;
		$uri = rtrim(JURI::root(), '/') . $path;

		return $uri;
	}

	/**
	 * Gets the content of the file.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getContents()
	{
		$config = ES::config();

		$path = ltrim($config->get(strtolower($this->type) . '_uploads_path') , '\\/');
		$path = SOCIAL_MEDIA . '/' . $path . '/' . $this->uid . '/' . $this->hash;

		$contents = JFile::read($path);

		return $contents;
	}

	/**
	 * Copies the temporary file from the table `#__social_uploader` and place the item in the appropriate location.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function copyFromTemporary($id, $deleteSource = true)
	{
		$uploader = ES::table('Uploader');
		$uploader->load($id);

		// Bind the properties from uploader over.
		$this->name = $uploader->name;
		$this->mime = $uploader->mime;
		$this->size = $uploader->size;
		$this->user_id = $uploader->user_id;
		$this->created = $uploader->created;

		jimport('joomla.filesystem.file');

		$this->hash = JFile::makeSafe($uploader->name);

		// Lets figure out the storage path.
		$config = ES::config();

		if ($this->type == 'comments') {
			$path = JPATH_ROOT . '/' . ES::cleanPath($config->get('comments.storage'));
		} else {
			$path = ES::cleanPath($config->get('files.storage.container'));
			$path = JPATH_ROOT . '/' . $path . '/' . ES::cleanPath($config->get('files.storage.' . $this->type . '.container'));
		}

		// Test if the folder exists for this upload type.
		if (!ES::makeFolder($path)) {
			$this->setError(JText::sprintf('COM_EASYSOCIAL_UPLOADER_UNABLE_TO_CREATE_DESTINATION_FOLDER', $path));
			return false;
		}

		if ($this->sub) {
			$path = $path . '/' . $this->sub;

			if (!ES::makeFolder($path)) {
				$this->setError(JText::sprintf('COM_EASYSOCIAL_UPLOADER_UNABLE_TO_CREATE_DESTINATION_FOLDER', $path));
				return false;
			}
		}

		// Let's finalize the storage path.
		$storage = $path . '/' . $this->uid;

		if (!ES::makeFolder($storage)) {
			$this->setError(JText::sprintf('COM_EASYSOCIAL_UPLOADER_UNABLE_TO_CREATE_DESTINATION_FOLDER' , $storage));
			return false;
		}

		// Once the script reaches here, we assume everything is good now.
		// Copy the files over.
		jimport('joomla.filesystem.file');

		// Copy the file over.
		$source = $uploader->path;
		$dest = $storage . '/' . $this->hash;

		// Try to copy the files.
		$state = JFile::copy($source, $dest);

		if (!$state) {
			$this->setError(JText::sprintf('COM_EASYSOCIAL_UPLOADER_UNABLE_TO_COPY_TO_DESTINATION_FOLDER', $dest));
			return false;
		}

		// Once it is copied, we should delete the temporary data.
		if ($deleteSource) {
			$uploader->delete();
		}

		return $state;
	}

	/**
	 * Identical to the store method but it also stores the file properties.
	 * Maps a file object into the correct properties.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function storeWithFile($file)
	{
		// Check if file exists on the server
		if (!isset($file['tmp_name']) || empty($file)) {
			$this->setError(JText::_('COM_EASYSOCIAL_UPLOADER_FILE_NOT_FOUND'));
			return false;
		}

		// Get the name of the uploaded file.
		if (isset($file['name']) && !empty($file['name'])) {
			$this->name = ES::uploader()->normalizeFilename($file);
		}

		// Get the mime type of the file.
		if (isset($file['type']) && !empty($file['type'])) {
			$this->mime = ES::uploader()->normalizeFiletype($file);
		}

		// Get the file size.
		if (isset($file['size']) && !empty($file['size'])) {
			$this->size = $file['size'];
		}

		// If there's no type or the unique id is invalid we should break here.
		if (!$this->type || !$this->uid) {
			$this->setError(JText::_('COM_EASYSOCIAL_UPLOADER_COMPOSITE_ITEMS_NOT_DEFINED'));
			return false;
		}

		// Generate a random hash for the file.
		$this->hash = md5($this->name . $file['tmp_name']);

		// Try to store the item first.
		$state = $this->store();

		// Once the script reaches here, we assume everything is good now.
		// Copy the files over.
		jimport('joomla.filesystem.file');

		$storage = $this->getStoragePath();

		// Ensure that the storage path exists.
		ES::makeFolder($storage);

		$state = JFile::copy($file['tmp_name'] , $storage . '/' . $this->hash);

		if (!$state) {
			$this->setError(JText::sprintf('COM_EASYSOCIAL_UPLOADER_UNABLE_TO_COPY_TO_DESTINATION_FOLDER' , $typePath . '/' . $this->uid . '/' . $this->hash));
			return false;
		}

		return $state;
	}

	/**
	 * Returns the file path
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getStoragePath($relative = false)
	{
		// Lets figure out the storage path.
		$config = ES::config();
		$path = '';

		if (!$relative) {
			$path = JPATH_ROOT;
		}

		if ($this->type == 'comments') {
			$path .= '/' . rtrim(ES::cleanPath($config->get('comments.storage')), '/');
		} else {
			// Get the storage path
			$path .= '/' . ES::cleanPath($config->get('files.storage.container'));
			$path = $path .'/' . ES::cleanPath($config->get('files.storage.' . $this->type . '.container'));
		}

		if ($this->sub) {
			$path = $path . '/' . $this->sub;
		}

		// Let's finalize the storage path.
		$storage = $path . '/' . $this->uid;

		return $storage;
	}

	public function getHash($forceNew = false)
	{
		if (empty($this->hash) || $forceNew) {
			$key = $this->name . $this->size;

			if (empty($key)) {
				$key = uniqid();
			}

			$this->hash = md5($key);
		}

		return $this->hash;
	}

	/**
	 * Retrieves the permalink to the item
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getPermalink($xhtml = true)
	{
		$url = ESR::conversations(array('layout' => 'download' , 'fileid' => $this->id) , $xhtml);

		return $url;
	}

	/**
	 * Returns the download link for the file.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getDownloadURI($customView = '', $customTask = '')
	{
		if ($this->storage != 'joomla') {
			$storage = ES::storage($this->storage);
			$path = $this->getStoragePath(true);
			$path = $path . '/' . $this->hash;

			return $storage->getPermalink($path);
		}

		// We need to fix the path for groups!
		$view = $this->type;

		if ($this->type == SOCIAL_TYPE_GROUP) {
			$view = 'groups';
		}

		if ($this->type == SOCIAL_TYPE_EVENT) {
			$view = 'events';
		}

		if ($this->type == SOCIAL_TYPE_PAGE) {
			$view = 'pages';
		}

		// Default task
		$task = 'download';

		if ($this->type == SOCIAL_TYPE_USER) {
			$view = 'profile';
			$task = 'downloadFile';
		}

		if ($customView) {
			$view = $customView;
		}

		if ($customTask) {
			$task = $customTask;
		}

		$uri = ESR::raw('index.php?option=com_easysocial&view=' . $view . '&layout=' . $task . '&fileid=' . $this->id . '&tmpl=component');

		return $uri;
	}

	/**
	 * Return the preview uri of the source item
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getPreviewURI()
	{
		if ($this->storage != 'joomla') {
			$storage = ES::storage($this->storage);
			$path = $this->getStoragePath(true);
			$path = $path . '/' . $this->hash;

			return $storage->getPermalink($path);
		}

		// We need to fix the path for groups!
		$type = $this->type;

		if ($type == 'group') {
			$type = 'groups';
		}

		if ($type == 'event') {
			$type = 'events';
		}

		if ($type == 'page') {
			$type = 'pages';
		}

		if ($type == 'user') {
			$type = 'profile';
		}

		if ($type == 'conversations') {
			return ESR::conversations(array('layout' => 'preview', 'fileid' => $this->id, 'external' => true));
		}

		return ESR::raw('index.php?option=com_easysocial&view=' . $type . '&layout=preview&fileid=' . $this->id . '&tmpl=component');
	}

	/**
	 * Ends the output and allow user to preview the file
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function preview($appendPath = '')
	{
		$storage = $this->getStoragePath();

		if ($appendPath) {
			$storage .= '/' . $appendPath;
		}

		$file = $storage . '/' . $this->hash;

		jimport('joomla.filesystem.file');

		// If the file no longer exists, throw a 404
		if (!JFile::exists($file)) {
			JError::raiseError(404);
		}

		if (!$this->hasPreview()) {
			return $this->download();
		}

		// Get the real file name
		$fileName = $this->name;

		// Get the file size
		$fileSize = filesize($file);

		header('Content-Description: File Transfer');
		header('Content-Type: ' . $this->mime);
		header('Content-Disposition: inline');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . $fileSize);

		// http://dtbaker.com.au/random-bits/how-to-cache-images-generated-by-php.html
		header("Cache-Control: private, max-age=10800, pre-check=10800");
		header("Pragma: private");
		header("Expires: " . date(DATE_RFC822,strtotime(" 2 day")));

		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])
			   &&
		  (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == filemtime($file))) {
		  // send the last mod time of the file back
		  header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($file)).' GMT',
		  true, 304);
		}

		ob_clean();
		flush();
		readfile($file);
		exit;
	}

	/**
	 * Ends the output and allow user to download the file
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function download($appendPath = '')
	{
		// Update the hit counter
		$this->hits += 1;
		$this->store();

		if ($this->storage != 'joomla') {
			$storage = ES::storage($this->storage);
			$path = $this->getStoragePath(true);
			$path = $path . '/' . $this->hash;

			return JFactory::getApplication()->redirect($storage->getPermalink($path));
		}

		$storage = $this->getStoragePath();

		if ($appendPath) {
			$storage .= '/' . $appendPath;
		}

		$file = $storage . '/' . $this->hash;

		jimport('joomla.filesystem.file');

		// If the file no longer exists, throw a 404
		if (!JFile::exists($file)) {
			return JError::raiseError(404, 'File no longer exists');
		}

		// Get the real file name
		$fileName = $this->name;

		// Get the file size
		$fileSize = filesize($file);

		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'. $fileName . '"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . $fileSize);
		ob_clean();
		flush();
		readfile($file);
		exit;
	}
}
