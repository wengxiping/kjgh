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

class EasySocialViewSharer extends EasySocialSiteView
{
	/**
	 * Checks if this feature should be enabled or not.
	 *
	 * @since	1.2
	 * @access	private
	 */
	private function checkFeature()
	{
		// Do not allow user to access groups if it's not enabled
		if (!$this->config->get('sharer.enabled')) {
			return JError::raiseError(404);
		}
	}

	/**
	 * Displays the embed page
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function embed()
	{
		$this->checkFeature();

		if (!$this->config->get('sharer.users')) {
			return JError::raiseError(404);
		}

		// Get user's affiliation id
		$affiliationId = $this->my->getAffiliationId(true);

		$this->set('affiliationId', $affiliationId);

		return parent::display('site/sharer/embed/default');
	}

	/**
	 * Default method to render share form
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$this->checkFeature();

		$url = $this->input->get('url', '', 'default');

		if (!$url) {
			die();
		}

		if (!$this->my->id) {
			$return = base64_encode(JRequest::getUri());

			$theme = ES::themes();
			$theme->set('return', $return);
			$theme->set('url', $url);
			$contents = $theme->output('site/sharer/login/default');

			echo $this->getWindowOutput($contents);
			exit;
		}

		$affiliationId = $this->input->get('aff', '', 'string');
		$assets = $this->getAssets();

		$sharer = ES::sharer();
		$meta = $sharer->crawl($url);

		$theme = ES::themes();
		$theme->set('url', $url);
		$theme->set('meta', $meta);
		$theme->set('assets', $assets);
		$theme->set('affiliationId', $affiliationId);
		$contents = $theme->output('site/sharer/form/default');

		echo $this->getWindowOutput($contents);
		exit;
	}

	/**
	 * Renders the sharer button
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function button()
	{
		$this->checkFeature();

		$assets = $this->getAssets();

		$link = $this->input->get('link', '', 'default');
		$source = $this->input->get('source', '', 'default');
		$id = $this->input->get('id', '', 'default');
		$affiliationId = $this->input->get('aff', '', 'string');

		if (!$link) {
			die();
		}

		$link = urlencode($link);

		$theme = ES::themes();
		$theme->set('affiliationId', $affiliationId);
		$theme->set('id', $id);
		$theme->set('source', $source);
		$theme->set('link', $link);
		$theme->set('assets', $assets);
		$output = $theme->output('site/sharer/button/default');

		echo $output;
		exit;
	}

	/**
	 * Post processing after save
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function save()
	{
		$this->checkFeature();

		$siteName = ES::jconfig()->getValue('sitename');

		$theme = ES::themes();
		$theme->set('siteName', $siteName);
		$contents = $theme->output('site/sharer/form/complete');

		echo $this->getWindowOutput($contents);
		exit;
	}

	/**
	 * Generates the standard output
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getWindowOutput($contents)
	{
		$assets = $this->getAssets();

		$theme = ES::themes();
		$theme->set('assets', $assets);
		$theme->set('contents', $contents);
		$output = $theme->output('site/sharer/window/default');

		return $output;
	}

	/**
	 * Retrieve the js and css files needed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function getAssets()
	{
		ES::initialize();

		$stylesheet = ES::stylesheet('site');
		$cssFiles = array_keys($stylesheet::$attached);
		$jsFiles = array();

		$environment = $this->config->get('general.environment');

		$scripts = ES::scripts();

		if ($environment == 'development') {
			$root = rtrim(JURI::root(), '/');

			// Render the bootloader on the page first
			$jsFiles[] = $root . '/media/com_easysocial/scripts/bootloader.js';

			// Render dependencies from the core
			foreach ($scripts->dependencies as $dependency) {
				$jsFiles[] = $root . '/media/com_easysocial/scripts/vendors/' . $dependency;
			}

			// Render easysocial's dependencies
			$jsFiles[] = $root . '/media/com_easysocial/scripts/site/site.js';
		} else {
			$jsFiles[] = $scripts->getFileUri('site', true, $this->config->get('general.jquery'));
		}

		$result = new stdClass();
		$result->scripts = $jsFiles;
		$result->stylesheets = $cssFiles;

		return $result;
	}
}
