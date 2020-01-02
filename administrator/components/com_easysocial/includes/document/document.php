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

class SocialDocument extends EasySocial
{
	/**
	 * A copy of itself for caching purposes.
	 * @var SocialDocument
	 */
	static $instance = null;

	/**
	 * The adapter
	 * @var Object
	 */
	private $helper = null;

	/**
	 * Store a list of scripts on the page.
	 * @var	Array
	 */
	public $scripts = array();

	/**
	 * Store a list of inline scripts on the page.
	 * @var	Array
	 */
	public $inlineScripts = array();

	/**
	 * Store a list of stylesheets on the page
	 * @var	Array
	 */
	public $stylesheets = array();

	/**
	 * Store a list of inline style sheets on the page
	 * @var	Array
	 */
	public $inlineStylesheets = array();

	/**
	 * The title of the page.
	 * @var	Array
	 */
	public $title = null;

	public function __construct()
	{
		parent::__construct();

		// Determine the current document type.
		$type = $this->doc->getType();

		$known = array('ajax', 'feed', 'html', 'json', 'embed');

		if (!in_array($type, $known)) {
			return;
		}

		// Let's find for any helpers for this type.
		$file = __DIR__ . '/helpers/' . strtolower($type) . '.php';

		require_once($file);

		$docClass = 'SocialDocument' . strtoupper($type);

		$this->helper = new $docClass();
	}

	/**
	 * There should only be one copy of document running at page load.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function getInstance()
	{
		if (self::$instance === null) {
			self::$instance	= new self();
		}

		return self::$instance;
	}

	public function __call($method, $args)
	{
		return call_user_func_array(array($this->helper, $method), $args);
	}

	/**
	 * Starting point of any document
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function start()
	{
		// Additional triggers to be processed when the page starts.
		$dispatcher = ES::dispatcher();
		$dispatcher->trigger('user', 'onComponentStart', array());

		$section = $this->app->isAdmin() ? 'admin' : 'site';

		// Only allow compiling if the user is really a site admin to prevent abuse
		if (!$this->my->isSiteAdmin()) {
			return;
		}

		// Allow caller to invoke recompiling of the entire css
		if ($this->input->get('compileCss')) {
			$stylesheet = ES::stylesheet($section, $this->config->get('theme.' . $section));
			$result = $stylesheet->build('full');

			header('Content-type: application/json; UTF-8');
			echo json_encode($result);
			exit;
		}

		// Run initialization codes for javascript side of things.
		if ($this->input->get('compile', false, 'bool') != true) {
			return false;
		}

		// Determines if we should minify the output.
		$minify = $this->input->get('minify', false, 'bool');

		$compiler = ES::compiler();

		// Compile with jQuery
		$result = array();
		$result[] = $compiler->compile($section, $minify, true);

		// Compile without jQuery
		$result[] = $compiler->compile($section, $minify, false);

		header('Content-type: application/json; UTF-8');
		echo json_encode($result);

		exit;
	}

	/**
	 * This is the ending point of the page library.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function end($options = array())
	{
		// Initialize required dependencies.
		$this->helper->init($options);

		$processStylesheets	= isset($options['processStylesheets']) ? $options['processStylesheets'] : true;

		// @task: Process any scripts that needs to be injected into the head.
		if ($processStylesheets) {
			$this->processStylesheets();
		}

		// Process any scripts that needs to be injected into the head.
		$this->processScripts();

		// Process the document title.
		$this->processTitle();

		// Render meta tags on the page
		ES::meta()->renderMeta();

		// Render header for mobile device shortcut
		$this->renderShortcutHeader();

		// Additional triggers to be processed when the page starts.
		$dispatcher = ES::dispatcher();
		$dispatcher->trigger('user', 'onComponentEnd', array());
	}

	/**
	 * To render required header for mobile device shortcut
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function renderShortcutHeader()
	{
		if ($this->app->isAdmin() || !$this->config->get('mobileshortcut.enabled')) {
			return;
		}

		// We should only attach scrips on html documents otherwise JDocument would hit an error
		if ($this->doc->getType() != 'html') {
			return;
		}

		// Used for 'Add to Homescreen' in android mobile device
		$this->doc->addHeadLink(ESR::external('index.php?option=com_easysocial&shortcutmanifest=true'), 'manifest', 'rel');

		// Apple device
		$this->doc->addHeadLink(ES::getMobileIcon(), 'apple-touch-icon', 'rel');
		$this->doc->setMetaData('apple-mobile-web-app-title', $this->config->get('mobileshortcut.shortname'));
		$this->doc->setMetaData('apple-mobile-web-app-capable', 'yes');
	}

	public function addStylesheet( $path )
	{
		$url = $this->toUri( $path );

		if ( !empty($url) )
		{
			$this->stylesheets[] = $url;
		}
	}

	public function addInlineStylesheet($stylesheet)
	{
		if (!empty($stylesheet)) {
			$this->inlineStylesheets[] = $stylesheet;
		}
	}

	/**
	 * Gets the current title and sets the title on the page.
	 *
	 * @access	private
	 * @param	null
	 */
	private function processTitle()
	{
		// We do not want to set the title for admin area.
		if ($this->app->isAdmin()) {
			return;
		}

		if ($this->title) {
			$this->doc->setTitle($this->title);
		}
	}


	public function toUri($path)
	{
		// TODO: Move this to the actual toUri
		$url = '';
		$uri = JURI::getInstance();

		// Url
		if (stristr($path, $uri->getScheme()) !== false) {
			$url = $path;
		}

		// File
		if (is_file($path)) {
			$url = ES::assets()->toUri($path);
		}

		return $url;
	}

	/**
	 * We need to wrap all javascripts into a single <script> block. This helps us maintain a single object.
	 *
	 * @access	public
	 * @param 	string 	$source		The script source.
	 */
	public function addScript($path)
	{
		$url = $this->toUri($path);

		if (!empty($url)) {
			$this->scripts[] = $url;
		}
	}

	public function addInlineScript($script)
	{
		if (!empty($script)) {
			$this->inlineScripts[] = $script;
		}
	}

	/**
	 * Internal method to build scripts to be embedded on the head or
	 * external script files to be added on the head.
	 *
	 * @access	private
	 */
	public function processScripts()
	{
		// Scripts
		if (!empty($this->scripts)) {
			foreach ($this->scripts as $script) {
				$this->doc->addScript($script);
			}
		}

		if (empty($this->inlineScripts)) {
			return;
		}

		// Inline scripts
		$script = FD::get('Script');
		$script->file = SOCIAL_MEDIA . '/head.js';
		$script->scriptTag	= true;
		$script->CDATA = true;
		$script->set('contents', implode($this->inlineScripts));
		$inlineScript = $script->parse();

		if ($this->doc->getType() == 'html') {
			$this->doc->addCustomTag($inlineScript);
		}
	}

	/**
	 * Processes stylesheets that needs to be added on the page
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function processStylesheets()
	{
		// Stylesheets
		if (!empty($this->stylesheets)) {
			foreach ($this->stylesheets as $stylesheet) {
				$this->doc->addStyleSheet($stylesheet);
			}
		}

		if (empty($this->inlineStylesheets)) {
			return;
		}

		// Inline scripts
		$stylesheet = FD::get('Style');
		$stylesheet->file = SOCIAL_MEDIA . '/head.css';
		$stylesheet->styleTag = true;
		$stylesheet->CDATA = true;
		$stylesheet->set('contents', implode($this->inlineStylesheets));

		$inlineStylesheet = $stylesheet->parse();

		if ($this->doc->getType() == 'html') {
			$this->doc->addCustomTag($inlineStylesheet);
		}
	}

	/**
	 * Adds into the breadcrumb list
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function breadcrumb($title, $link = '')
	{
		$app = JFactory::getApplication();
		$pathway = $app->getPathway();
		$pathways = $pathway->getPathwayNames();

		if (!empty($pathways)) {
			$pathways = array_map(array('JString', 'strtolower'), $pathways);
		}

		// Ensure that the title is translated
		$title = JText::_($title);

		// Set the temporary title
		$tmp = JString::strtolower($title);

		// Do not allow duplicate titles in the breadcrumb
		if (in_array($tmp, $pathways)) {
			return false;
		}

		$state = $pathway->addItem($title, $link);

		return $state;
	}

	/**
	 * Allows caller to set a canonical link
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function canonical($link)
	{
		$docLinks = $this->doc->_links;

		// if joomla already added a canonial links here, we remove it.
		if ($docLinks) {
			foreach($docLinks as $jLink => $data) {
				if ($data['relation'] == 'canonical' && $data['relType'] == 'rel') {

					//unset this variable
					unset($this->doc->_links[$jLink]);
				}
			}
		}

		$link = FD::string()->escape($link);
		$this->doc->addHeadLink($link, 'canonical');
	}

	/**
	 * Sets the title of the page.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function title($default, $override = true, $view = null)
	{
		// Get the view.
		$view = is_null($view) ? $this->input->get('view', '', 'cmd') : $view;

		// Get the passed in title.
		$title = $default;

		// @TODO: Create SEO section that allows admin to customize the header of the page. Test if there's any custom title set in SEO section

		// Get current menu
		$activeMenu = $this->app->getMenu()->getActive();

		if ($activeMenu) {
			$params = $activeMenu->params;
			$menuView = $activeMenu->query['view'];

			if ($params->get('page_title') && $override && $view == $menuView) {
				$title = $params->get('page_title');
			}
		}

		// Apply translations on the title
		$title = JText::_($title);

		// Prepare Joomla's site title if necessary.
		$this->title = $this->getSiteTitle($title);


		// Need to think about keywords , author , metadesc and robots

		// nofollow ?
	}

	/**
	 * Sets the meta description of the page
	 *
	 * @since	2.1.9
	 * @access	public
	 */
	public function description($content)
	{
		// Do not allow anything more than 300 characters
		$content = strip_tags($content);
		$content = JString::substr($content, 0, 300);

		ES::meta()->setMeta('description', $content);
	}

	/**
	 * Renders oembed tag on the page
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function oembed($permalink)
	{
		$this->doc->addHeadLink($permalink, 'alternate', 'rel', array('type' => 'application/json+oembed'));
	}

	/**
	 * Given a string to be added to the title, compute it with the site title
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getSiteTitle($title = '')
	{
		$jConfig 	= FD::config('joomla');
		$addTitle 	= $jConfig->getValue('sitename_pagetitles');
		$siteTitle 	= $jConfig->getValue('sitename');

		if ($addTitle) {

			$siteTitle 	= $jConfig->getValue( 'sitename' );

			if ($addTitle == 1) {
				$title 	= $siteTitle . ' - ' . $title;
			}

			if ($addTitle == 2) {
				$title	= $title . ' - ' . $siteTitle;
			}
		}

		return $title;
	}
}
