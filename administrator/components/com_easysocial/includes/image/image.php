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

require_once(dirname(__FILE__) . '/adapters/asido/class.asido.php');

class SocialImage
{
	/**
	 * Stores the current image resource.
	 * @var	resource
	 */
	private $image = null;

	/**
	 * Stores the original image resource in case we need to revert.
	 * @var	resource
	 */
	private $original = null;

	/**
	 * Stores the information about the image.
	 * @var	stdClass
	 */
	private $meta = null;

	/**
	 * Stores the current adapter.
	 * @var	Asido
	 */
	private $adapter = null;

	/**
	 * Sizes definition of gif image.
	 * @var	Array
	 */
	static $sizes = array(

		'thumbnail' => array(
			'width'  => SOCIAL_PHOTOS_GIF_THUMB_WIDTH,
			'height' => SOCIAL_PHOTOS_GIF_THUMB_HEIGHT
		),

		'large' => array(
			'width'  => SOCIAL_PHOTOS_GIF_LARGE_WIDTH,
			'height' => SOCIAL_PHOTOS_GIF_LARGE_HEIGHT
		)
	);

	public function __construct($driver = 'gd_hack')
	{
		$this->adapter = new Asido();
		$this->adapter->driver($driver);
	}

	/**
	 * This class uses the factory pattern.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public static function factory($driver = 'gd_hack')
	{
		$image 	= new self($driver);

		return $image;
	}

	/**
	 * Determines if the image is a valid image type.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function isValid()
	{
		if (!$this->image) {
			return false;
		}

		return $this->adapter->is_format_supported($this->meta->info['mime']);
	}

	/**
	 * Loads an image resource given the path to the image.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function load($path, $name = '')
	{
		// Set the meta info about this image.
		$meta = new stdClass();

		// Set the path for this image.
		$meta->path = $path;

		// Set the meta info
		$meta->info = getimagesize($path);

		// Set the name for this image.
		if (!empty($name)) {
			$meta->name = $name;
		} else {
			$meta->name = basename($path);
		}

		// If name is not provided, we'll generate a unique one for it base on the path.
		if (empty($meta->name)) {
			$meta->name = $this->genUniqueName($path);
		}

		$this->meta = $meta;

		// Set the image resource.
		$this->image = $this->adapter->image($path);

		// Fix the orientation of the image first.
		$this->fixOrientation();

		// Set the original image resource.
		$this->original	= $this->image;

		return $this;
	}

	public function replaceImage($image)
	{
		$this->image = $image;
	}

	public function cloneImage()
	{
		$image 	= clone($this);
		$image->replaceImage(clone($this->image));

		return $image;

	}
	public function newInstance()
	{
		$image = ES::image();

		$image->load($this->meta->path, $this->meta->name);

		return $image;
	}

	/**
	 * Retrieves the mime of the current image.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getMime()
	{
		if (!$this->image) {
			return false;
		}

		if (!isset($this->meta->info['mime'])) {
			return false;
		}

		return $this->meta->info['mime'];
	}

	/**
	 * Resizes an image to a specific width.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function width($width)
	{
		$this->adapter->width($this->image, $width);

		return $this;
	}

	/**
	 * Resizes an image to a specific height.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function height($height)
	{
		$this->adapter->height($this->image, $height);

		return $this;
	}

	/**
	 * Gets the width of the image
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getWidth()
	{
		$width = $this->meta->info[0];

		return $width;
	}

	/**
	 * Gets the width of the image
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getHeight()
	{
		$height = $this->meta->info[1];

		return $height;
	}

	/**
	 * General resize for image
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function resize($width = null, $height = null, $mode = ASIDO_RESIZE_PROPORTIONAL)
	{
		$this->adapter->resize($this->image, $width, $height, $mode);

		return $this;
	}

	/**
	 * Rotates the image
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function rotate($degree = 0)
	{
		$this->adapter->rotate($this->image, $degree);

		return $this;
	}

	/**
	 * Resize an image to fit the target width and height
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function fit($width = null, $height = null)
	{
		$this->adapter->fit($this->image, $width, $height);

		return $this;
	}

	/**
	 * Resize an image to fill a frame
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function fill($width = null, $height = null)
	{
		$this->adapter->fill($this->image, $width, $height);

		return $this;
	}

	public function outerFit($width = null, $height = null)
	{
		$this->adapter->outerFit($this->image, $width, $height);

		return $this;
	}

	/**
	 * Resize an image to fit a frame
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function frame($width = null, $height = null)
	{
		$this->adapter->frame($this->image, $width, $height);

		return $this;
	}

	/**
	 * Crops an image given the coordinates, width and height.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function crop($x = 0, $y = 0, $width, $height)
	{
		// Try to crop the current image resource.
		$this->adapter->crop($this->image, $x, $y, $width, $height);

		return $this;
	}

	/**
	 * Save's the image resource in a target location.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function save($target)
	{
		// Set the image target.
		$this->image->target($target);

		// Try to save the image.
		$state = $this->image->save();

		return $state;
	}

	/**
	 * Save's the animated gif image resources in a target location.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function saveGif($storagePath, $filename, $overrideSize = array())
	{
		// Get the api key
		$config = ES::config();
		$key = $config->get('general.key');		

		$post = array();

		$resources = $storagePath . '/' . $filename;

		// Get absolute path of the image
		$imageFile = JPATH_ROOT . $resources;

		$sizes = json_encode(self::$sizes);

		if ($overrideSize) {
			$sizes = json_encode($overrideSize);
		}

		// Add image file
		// php 5.5 and above will use CURLFile(imagepath)' instead of '@/imagepath' to add the image file
		$cfile = class_exists('CURLFile', false) ? new CURLFile($imageFile) : "@" . $imageFile;

		$post['imageFile'] = $cfile;
		$post['imageName'] = $this->getName(false);
		$post['sizes'] = $sizes;

		// Essential post data
		$post['key'] = $key;
		$post['storagePath'] = $storagePath;
		$post['domain'] = rtrim(JURI::root(), '/');

		$url = SOCIAL_SERVICE_IMAGE_RESIZER;

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 100000);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$result = curl_exec($ch);

		curl_close($ch);

		// Check if this is a valid zip file.
		jimport('joomla.filesystem.archive');

		$zipAdapter = JArchive::getAdapter('zip');

		$isZip = $zipAdapter->checkZipData($result);

		if ($isZip) {
			return $result;
		}

		// Create log file here since we know there are some error during the process above.
		$name = md5($filename);
		$logPath = JPATH_ROOT . $storagePath . '/' . $name . '.log';

		JFile::write($logPath, $result);

		return false;
	}

	/**
	 * Test whether the image is animated
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function isAnimated()
	{
		$file = file_get_contents($this->meta->path);

		// Test if the image contain animation.
		$animated = preg_match('#(\x00\x21\xF9\x04.{4}\x00\x2C.*){2,}#s', $file);

		return $animated;
	}

	/**
	 * Just copy the file to the target
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function copy($target)
	{
		$state = JFile::copy($this->meta->path, $target);

		return $state;
	}

	/**
	 * Returns the path of the image.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getPath()
	{
		return $this->meta->path;
	}

	/**
	 * Returns the name of the image.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getName($hash = false)
	{
		if ($hash) {
			return $this->genUniqueName();
		}

		return $this->meta->name;
	}

	public function getOriginalExtension()
	{
		$extension = pathinfo($this->meta->name, PATHINFO_EXTENSION);
		$extension = '.' . $extension;	

		return $extension;
	}

	/**
	 * Returns the extension type for this image.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getExtension()
	{
		$mime = false;

		if (isset($this->meta->info['mime'])) {
			$mime = $this->meta->info['mime'];
		}

		switch ($mime) {
			case 'image/jpeg':
				$extension  = '.jpg';
			break;

			case 'image/png':
			case 'image/x-png':
			default:
				$extension  = '.png';
			break;
		}

		return $extension;
	}

	/**
	 * Generates a random image name based on the node id.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function genUniqueName($salt = '')
	{
		if ($salt) {
			$hashed = md5($this->meta->name . $salt);
		} else {
			$hashed	= md5($this->meta->name . uniqid());
		}

		return $hashed;
	}

	/**
	 * Generates a file name for an image
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function generateFilename($size, $fileName = '', $overrideExtension = false)
	{
		if (empty($fileName)) {
			$fileName = $this->getName(false);
		}

		// Remove any previously _stock from the image name
		$fileName = str_ireplace('_stock', '', $fileName);

		$extension = $this->getExtension();

		if ($overrideExtension) {
			$extension = $overrideExtension;
		}

		$fileName = str_ireplace($extension, '', $fileName);

		// Ensure that the file name is lowercased
		$fileName = strtolower($fileName);

		// Ensure that the file name is valid
		$fileName = JFilterOutput::stringURLSafe($fileName);

		// Append the size and extension back to the file name.
		$fileName = $fileName . '_' . $size . $extension;

		return $fileName;
	}

	/**
	 * Determines if the current image has exif data
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function hasExifSupport()
	{
		$mime = $this->getMime();

		if ($mime == 'image/jpg' || $mime == 'image/jpeg') {
			return true;
		}

		return false;
	}

	/**
	 * Fixes image orientation
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function fixOrientation()
	{
		$exif = ES::get('Exif');

		if (!$exif->isAvailable(false)) {
			return false;
		}

		// Get the mime type for this image
		$mime = $this->getMime();

		// Only image with jpeg are supported.
		if ($mime != 'image/jpeg') {
			return false;
		}

		// Load exif data.
		$exif->load($this->meta->path);

		$orientation = $exif->getOrientation();

		switch ($orientation) {
			case 1:
				// Do nothing here as the image is already correct.
				$this->adapter->rotate($this->image, 0);
			break;

			case 2:
				// Flip image horizontally since it's at top right
				$this->adapter->flop($this->image);
			break;

			case 3:
				// Rotate image 180 degrees left since it's at bottom right
				$this->adapter->rotate($this->image, 180);
			break;

			case 4:
				// Flip image vertically because it's at bottom left
				$this->adapter->flip($this->image);
			break;

			case 5:
				// Flip image vertically
				$this->adapter->flip($this->image);

				// Rotate image 90 degrees right.
				$this->adapter->rotate($this->image, -90);
			break;

			case 6:
				// Rotate image 90 degrees right
				$this->adapter->rotate($this->image, 90);
			break;

			case 7:
				// Flip image horizontally
				$this->adapter->flop($this->image);

				// Rotate 90 degrees right.
				$this->adapter->rotate($this->image, 90);
			break;

			case 8:
				// Rotate image 90 degrees left
				$this->adapter->rotate($this->image, -90);
			break;
		}
	}
}
