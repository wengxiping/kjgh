<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/tables/table');

class SocialTableAdvertiser extends SocialTable
{
	public $id = null;
	public $name = null;
	public $logo = null;
	public $state = null;
	public $created = null;

	public function __construct($db)
	{
		parent::__construct('#__social_advertisers', 'id', $db);
	}

	/**
	 * Uploads a logo for advertiser
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function uploadLogo($file)
	{
		jimport('joomla.filesystem.file');

		if (!isset($file['tmp_name']) || (isset($file['error']) && $file['error'] != 0)) {
			$this->setError('COM_ES_ADS_UPLOADED_FILE_ERROR');
			return false;
		}

		$image = ES::image();
		$image->load($file['tmp_name']);

		// $image->resize($image->getWidth(), 24);

		// Generate a file title
		$fileName = md5($this->id) . $image->getExtension();

		// Copy the file into the icon emoji folder
		$config = ES::config();
		$storage = JPATH_ROOT . $this->getLogoStorage();

		if (!JFolder::exists($storage)) {
			JFolder::create($storage);
		}

		$state = JFile::copy($file['tmp_name'], $storage . '/' . $fileName);

		if (!$state) {
			$this->setError('Error copying image file into ' . $storage);
			return false;
		}

		$this->logo = ltrim($this->getLogoStorage(), '/') . '/' . $fileName;

		return $this->store();
	}

	/**
	 * Retrieves the path to the logo storage
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getLogoStorage()
	{
		$config = ES::config();
		$storage = $config->get('ads.storage') . '/advertiser/' . $this->id;
		$storage = rtrim($storage, '/');

		return $storage;
	}

	/**
	 * Retrieve company logo
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function getLogo()
	{
		$default = rtrim(JURI::root(), '/') . '/media/com_easysocial/images/defaults/advertisement/logo.png';

		if (!$this->logo) {
			return $default;
		}

		$url = JURI::root() . $this->logo;

		return $url;
	}
}
