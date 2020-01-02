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

class PPStyleSheet extends PayPlans
{
	public $location = '';

	public function __construct($location = 'site')
	{
		parent::__construct();

		$this->location = $location;
	}

	/**
	 * Attaches the stylesheet to the head of the document
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function attach()
	{
		// RTL support
		$lang = JFactory::getLanguage();
		$rtl = $lang->isRTL();
		// $rtl = true;

		if ($this->location == 'admin') {
			$uri = $this->getAdminCss($rtl);
		}

		if ($this->location == 'site') {
			$uri = $this->getSiteCss($rtl);
		}

		// Hash version to avoid cache
		$hash = md5(PP::getLocalVersion());
		$uri = $uri . '?' . $hash;

		// Add the css of the extension
		$this->doc->addStyleSheet($uri);

		// Attach the custom css first
		$this->attachCustomCss();

	}

	/**
	 * Generates the file name for the stylesheets being used
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFileName($rtl = false)
	{
		$file = 'style';

		if ($rtl) {
			$file .= '-rtl';
		}

		// Should we be using a minified version
		$config = PP::config();
		$environment = $config->get('environment');

		if ($environment == 'production') {
			$file .= '.min';
		}

		$file .= '.css';

		return $file;
	}

	/**
	 * Attaches admin stylesheets
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function getAdminCss($rtl = false)
	{
		$fileName = $this->getFileName($rtl);
		$uri = JURI::root(true) . '/media/com_payplans/css/admin/' . $fileName;

		return $uri;
	}

	/**
	 * Attaches admin stylesheets
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function getSiteCss($rtl = false)
	{
		$fileName = $this->getFileName($rtl);
		$uri = JURI::root(true) . '/media/com_payplans/css/site/' . $fileName;

		return $uri;
	}

	/**
	 * if there is a custom.css overriding, we need to attach this custom.css file.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function attachCustomCss()
	{
		static $loaded = null;

		if (is_null($loaded)) {
			$path = JPATH_ROOT . '/templates/' . $this->app->getTemplate() . '/html/com_payplans/css/custom.css';
			$exists = JFile::exists($path);

			if ($exists) {
				$customURI = JURI::root() . 'templates/' . $this->app->getTemplate() . '/html/com_payplans/css/custom.css';
				$this->doc->addStyleSheet($customURI);
			}

			$loaded = true;
		}

		return true;
	}
}
