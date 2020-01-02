<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

PP::import('admin:/tables/table');

class PayplansTableLanguage extends PayplansTable
{
	public $id = null;
	public $title = null;
	public $locale = null;
	public $updated = null;
	public $state = null;
	public $translator = null;
	public $progress = null;
	public $params = null;

	public function __construct(&$db)
	{
		parent::__construct('#__payplans_languages', 'id', $db);
	}

	/**
	 * Installs a language file
	 *
	 * @since	5.1.8
	 * @access	public
	 */
	public function install()
	{
		$params = new JRegistry($this->params);

		$config = PP::config();
		$key = $config->get('main_apikey');

		if (!$key) {
			$this->setError('API key is invalid. Perhaps your downloaded package is corrupted. Please get in touch with our support');
			return false;			
		}
		
		// Get the download url
		$url = $params->get('download');

		if (!$url) {
			$this->setError('Invalid download URL provided by language server');
			return false;
		}

		$connector = PP::connector();
		$connector->addUrl($url);
		$connector->addQuery('key', $key);
		$connector->setMethod('POST');
		$connector->execute();
		$result = $connector->getResult($url);

		// Generate a random hash
		$hash = md5($this->locale . JFactory::getDate()->toSql());

		$storage = JPATH_ROOT . '/tmp/' . $hash . '.zip';
		$state = JFile::write($storage, $result);
		$folder = JPATH_ROOT . '/tmp/' . $hash;

		jimport('joomla.filesystem.archive');

		$state = JArchive::extract($storage, $folder);

		// Throw some errors when we are unable to extract the zip file.
		if (!$state) {
			$this->setError('Unable to extract language archive file');
			return false;
		}

		// Read the meta data
		$raw  = JFile::read($folder . '/meta.json');
		$meta = json_decode($raw);

		foreach ($meta->resources as $resource) {

			// Get the correct path based on the meta's path
			$dest = $this->getPath($resource->path) . '/language/' . $this->locale;

			// If language folder don't exist, create it first.
			if (!JFolder::exists($dest)) {
				JFolder::create($dest);
			}

			// Build the source and target files
			$destFile 	= $dest . '/' . $this->locale . '.' . $resource->title;
			$sourceFile = $folder . '/' . $resource->path . '/' . $this->locale . '.' . $resource->title;

			// Ensure that the source file exists
			if (!JFile::exists($sourceFile)) {
				continue;
			}

			// If the destination file already exists, delete it first
			if (JFile::exists($destFile)) {
				JFile::delete($destFile);
			}
			
			// Try to copy the file
			$state = JFile::copy($sourceFile, $destFile);

			if (!$state) {
				$this->setError('There was some errors copying the language files. It could most likely be caused by folder permissions');
				return false;
			}
		}

		// After everything is copied, ensure that the extracted folder is deleted to avoid dirty filesystem
		JFile::delete($storage);
		JFolder::delete($folder);

		// Once the language files are copied accordingly, update the state
		$this->state = PP_LANGUAGES_INSTALLED;

		return $this->store();
	}

	/**
	 * Allows caller to uninstall a language
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function uninstall()
	{
		$locale = $this->locale;

		$paths = array(JPATH_ADMINISTRATOR . '/language/' . $locale, JPATH_ROOT . '/language/' . $locale);

		// Get the list of files on each folders
		foreach ($paths as $path) {

			$filter = 'payplans';
			$files = JFolder::files($path, $filter, false, true);

			if (!$files) {
				continue;
			}

			foreach ($files as $file) {
				JFile::delete($file);
			}

			JFolder::delete($path);
		}

		$this->state = PP_LANGUAGES_NOT_INSTALLED;
		return $this->store();
	}

	/**
	 * Determines if the language is installed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isInstalled()
	{
		return $this->state == PP_LANGUAGES_INSTALLED;
	}

	/**
	 * Generates the absolute path given the meta location
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPath($metaPath)
	{
		$path = JPATH_ROOT;

		if ($metaPath == 'admin' || $metaPath == 'menu') {
			$path = JPATH_ADMINISTRATOR;
		}

		return $path;
	}

	/**
	 * Retrieves the list of translators for this language
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTranslators()
	{
		$translators = json_decode($this->translator);
		
		return $translators;
	}

	/**
	 * Determines if the language requires to be updated
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function requiresUpdating()
	{
		return $this->state == PP_LANGUAGES_NEEDS_UPDATING;
	}
}
