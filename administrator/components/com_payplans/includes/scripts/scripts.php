<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPScripts extends Payplans
{
	public $dependencies = array();
	public $baseurl = null;

	private $async = false;
	private $defer = false;
	private $location = 'site';

	static $attached = false;

	public function __construct($location = 'site')
	{
		parent::__construct();

		$this->location = $location;
		$this->baseurl = JURI::root(true);
		$this->dependencies = $this->getDependencies(false, $this->config->get('expert_use_jquery'));
		$this->environment = $this->config->get('environment', 'production');
		$this->jconfig = PP::jconfig();
	}

	/**
	 * Generates a configuration string for PayPlans's javascript library
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getJavascriptConfiguration()
	{
		$appendTitle = $this->jconfig->get('sitename_pagetitles') == 1 ? 'before' : 'after';
		$siteName = $this->jconfig->get('sitename');
		$locale = JFactory::getLanguage()->getTag();

		ob_start();
		?>
<!--googleoff: index-->
<script type="text/javascript">
window.pp = {
	"environment": "<?php echo $this->environment;?>",
	"rootUrl": "<?php echo rtrim(JURI::root(), '/');?>",
	"ajaxUrl": "<?php echo PP::ajax()->getUrl();?>",
	"baseUrl": "<?php echo PP::getBaseUrl();?>",
	"token": "<?php echo PP::token();?>",
	"mobile": <?php echo PP::responsive()->isMobile() ? 'true' : 'false'; ?>,
	"appendTitle": "<?php echo $appendTitle;?>",
	"siteName": "<?php echo $siteName;?>",
	"locale": "<?php echo $locale;?>",
	"direction": "<?php echo $this->doc->getDirection();?>",
	"ios": <?php echo PP::responsive()->isIos() ? 'true' : 'false';?>,
	"android": <?php echo PP::responsive()->isAndroid() ? 'true' : 'false';?>,
	"version": "<?php echo PP::getLocalVersion(); ?>"
}
</script>
<!--googleon: index-->
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	/**
	 * Retrieves the main dependencies from vendors
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDependencies($absolutePath = false, $jquery = false)
	{
		$coreFiles = array(
					'jquery.debounce.js',
					'joomla.js',
					'bootstrap3.js',
					'module.js',
					'utils.js',
					'uri.js',
					'script.js',
					'require.js',
					'server.js',
					'component.js'
				);

		// Determines if we should include jquery.easysocial.js library
		if ($jquery) {
			array_unshift($coreFiles, 'jquery.payplans.js');
		} else {
			array_unshift($coreFiles, 'jquery.js');
		}

		if ($absolutePath) {
			foreach ($coreFiles as &$file) {
				$file = PP_SCRIPTS . '/vendors/' . $file;
			}
		}

		return $coreFiles;
	}

	/**
	 * Retrieves api scripts based on location
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getApiScripts($location = 'site')
	{
		$path = PP_SCRIPTS . '/' . $location . '/api';

		if (!JFolder::exists($path)) {
			return array();
		}

		$files = JFolder::files($path, '.js$', false, false);

		return $files;
	}

	/**
	 * Generates script tags that should be added on the page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createScriptTag($path)
	{
		$script = '<script' . (($this->defer) ? ' defer' : '') . (($this->async) ? ' async' : '') . ' src="' . $path . '"></script>';

		return $script;
	}

	/**
	 * Generates the file name for the scripts
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getFileName($section, $jquery = true)
	{
		$version = PP::getLocalVersion();
		$file = $section . '-' . $version;

		if (!$jquery) {
			$file .= '-basic';
		}

		return $file;
	}

	/**
	 * Generates the file path
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getFileUri($section, $minified = true, $jquery = true)
	{
		$path = $this->baseurl . '/media/com_payplans/scripts/' . $this->getFileName($section, $jquery);

		if ($minified) {
			$path .= '.min.js';
		} else {
			$path .= '.js';
		}

		return $path;
	}

	/**
	 * Attaches the necessary script libraries on the page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function attach($location = 'site')
	{
		$this->location = $location;

		// Only attach the scripts on the page once.
		if (self::$attached) {
			return true;
		}

		// We should only attach scrips on html documents otherwise JDocument would hit an error
		if ($this->doc->getType() != 'html') {
			return;
		}

		// Add configurations about the site
		$configuration = $this->getJavascriptConfiguration();
		$this->doc->addCustomTag($configuration);


		// In production mode, we need to attach the compiled scripts
		if ($this->environment == 'production') {

			// Render Payplana's own jQuery to avoid conflict other products's
			$renderjQuery = $this->config->get('expert_use_jquery');
			$option = $this->input->get('option', '', 'cmd');

			if ($option == 'com_easysocial' || $option == 'com_easyblog') {
				$renderjQuery = true;
			}

			// For now, if we detected the current component is com_menus, we load the complete version.
			// #920
			if (!$renderjQuery && $this->app->isAdmin() && $this->input->get('option', '', 'cmd') == 'com_menus') {
				$renderjQuery = true;
			}

			// Test if ES or KMT already load Joomla jquery or not. if yes, we have to load our own jquery
			if (!$renderjQuery) {
				if (defined('COM_EASYSOCIAL_JQUERY_FRAMEWORK') || defined('COM_KOMENTO_JQUERY_FRAMEWORK')) {
					$renderjQuery = true;
				}
			}

			// If jquery is not rendered, we need to trigger Joomla to enforce it to load jquery
			if (!$renderjQuery) {
				define('COM_PAYPLANS_JQUERY_FRAMEWORK', 1);
				JHtml::_('jquery.framework');
			}

			$minified = true;

			$fileName = $this->getFileUri($this->location, $minified, $renderjQuery);

			$this->doc->addCustomTag($this->createScriptTag($fileName));
		}

		if ($this->environment == 'development') {

			// Render dependencies from the core
			foreach ($this->dependencies as $dependency) {
				$path = $this->baseurl . '/media/com_payplans/scripts/vendors/' . $dependency;

				$this->doc->addCustomTag($this->createScriptTag($path));
			}

			// Attach admin / site scripts
			$path = JURI::root() . '/media/com_payplans/scripts/' . $location . '/' . $location . '.js';
			$this->doc->addCustomTag($this->createScriptTag($path));

			// load the api js scripts
			$apis = $this->getApiScripts($location);
			if ($apis) {
				// Render api scrips from the core
				foreach ($apis as $api) {
					$path = $this->baseurl . '/media/com_payplans/scripts/' . $location . '/api/' . $api;

					$this->doc->addCustomTag($this->createScriptTag($path));
				}
			}

		}

		self::$attached = true;
	}

	/**
	 * Allows caller to attach inline scripts
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function addInlineScripts($script)
	{
		$scriptTag = true;
		$cdata = true;
		$safeExecution = false;

		ob_start();
		include(PP_MEDIA . '/scripts/template.php');
		$contents = ob_get_contents();
		ob_end_clean();

		$this->doc->addCustomTag($contents);
	}

	/**
	 * Allows caller to attach an external script
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function addScript($url)
	{
		$tag = $this->createScriptTag($url);

		$this->doc->addCustomTag($tag);
	}
}
