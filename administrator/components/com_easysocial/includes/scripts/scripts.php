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

class SocialScripts extends EasySocial
{
	public $dependencies = array();
	public $baseurl = null;

	private $async = false;
	private $defer = false;
	private $location = 'site';

	static $attached = false;
	static $attachedTags = false;


	public function __construct()
	{
		parent::__construct();

		// Get the base url
		$this->baseurl = JURI::root(true);

		// Get a list of foundry dependencies
		$this->dependencies = $this->getDependencies(false, $this->config->get('general.jquery'));

		// Get the current environment
		$this->environment = $this->config->get('general.environment');

		// Legacy purposes
		if ($this->environment == 'static') {
			$this->environment = 'production';
		}

		// If cdn is enabled, we need to update the base url
		$cdn = ES::getCdnUrl();

		if ($this->environment == 'production' && $cdn) {
			$this->baseurl = $cdn;
		}

		if (!defined('SOCIAL_COMPONENT_CLI')) {
			if ($this->app->isAdmin()) {
				$this->location = 'admin';
			}
		}
	}

	/**
	 * Generates a configuration string for EasySocial's javascript library
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getJavascriptConfiguration()
	{
		$appendTitle = 'none';

		if ($this->jConfig->getValue('sitename_pagetitles') > 0) {
			$appendTitle = $this->jConfig->getValue('sitename_pagetitles') == 1 ? 'before' : 'after';
		}

		$siteName = $this->jConfig->getValue('sitename');
		$locale = JFactory::getLanguage()->getTag();

		// moment locale mapping against joomla language
		// If the counter part doesn't exist, then we all back to the nearest possible one, or en-gb
		$momentLangMap = array(
			'af-za' => 'en-gb',
			'ar-aa' => 'ar',
			'bg-bg' => 'bg',
			'bn-bd' => 'en-gb',
			'ca-es' => 'ca',
			'cs-cz' => 'cs',
			'da-dk' => 'da',
			'de-de' => 'de',
			'el-gr' => 'el',
			'en-gb' => 'en-gb',
			'en-us' => 'en-gb',
			'es-cl' => 'es',
			'es-es' => 'es',
			'fa-ir' => 'fa',
			'fi-fi' => 'fi',
			'fr-ca' => 'fr',
			'fr-fr' => 'fr',
			'he-il' => 'he',
			'hr-hr' => 'hr',
			'hu-hu' => 'hu',
			'hy-am' => 'hy-am',
			'id-id' => 'id',
			'it-it' => 'it',
			'ja-jp' => 'ja',
			'ko-kr' => 'ko',
			'lt-lt' => 'lt',
			'ms-my' => 'ms-my',
			'nb-no' => 'nb',
			'nl-nl' => 'nl',
			'pl-pl' => 'pl',
			'pt-br' => 'pt-br',
			'pt-pt' => 'pt',
			'ro-ro' => 'ro',
			'ru-ru' => 'ru',
			'sq-al' => 'sq',
			'sv-se' => 'sv',
			'sw-ke' => 'en-gb',
			'th-th' => 'th',
			'tr-tr' => 'tr',
			'uk-ua' => 'uk',
			'vi-vn' => 'vi',
			'zh-cn' => 'zh-cn',
			'zh-hk' => 'zh-cn',
			'zh-tw' => 'zh-tw'
		);

		$lcLocale = strtolower($locale);
		$momentLang = isset($momentLangMap[$lcLocale]) ? $momentLangMap[$lcLocale] : 'en-gb';
		ob_start();
?>
<!--googleoff: index-->
<script type="text/javascript">
window.es = {
	"environment": "<?php echo $this->environment;?>",
	"rootUrl": "<?php echo rtrim(JURI::root(), '/');?>",
	"ajaxUrl": "<?php echo ES::ajax()->getUrl();?>",
	"baseUrl": "<?php echo ES::getBaseUrl();?>",
	"locationLanguage": "<?php echo ES::user()->getLocationLanguage();?>",
	"gmapsApiKey": "<?php echo ES::location()->getGmapsApiKey('browser');?>",
	"requireGmaps": <?php echo $this->config->get('location.provider') !== 'osm' ? 'true' : 'false';?>,
	"token": "<?php echo ES::token();?>",
	"mobile": <?php echo ES::responsive()->isMobile() ? 'true' : 'false'; ?>,
	"appendTitle": "<?php echo $appendTitle;?>",
	"siteName": "<?php echo addslashes($siteName);?>",
	"locale": "<?php echo $locale;?>",
	"momentLang": "<?php echo $momentLang;?>",
	"direction": "<?php echo $this->doc->getDirection();?>",
	"ios": <?php echo ES::responsive()->isIos() ? 'true' : 'false';?>,
	"android": <?php echo ES::responsive()->isAndroid() ? 'true' : 'false';?>,
	"tablet": <?php echo ES::responsive()->isTablet() ? 'true' : 'false';?>,
	"isHttps": <?php echo ES::isHttps() ? 'true' : 'false'; ?>
};
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
	 * @since	2.1
	 * @access	public
	 */
	public function getDependencies($absolutePath = false, $jquery = true)
	{
		$coreFiles = array(
					'lodash.js',
					'bootstrap3.js',
					'utils.js',
					'uri.js',
					'mvc.js',
					'joomla.js',
					'module.js',
					'script.js',
					'stylesheet.js',
					'template.js',
					'require.js',
					'iframe-transport.js',
					'server.js',
					'component.js'
		);

		// Determines if we should include jquery.easysocial.js library
		if ($jquery) {
			array_unshift($coreFiles, 'jquery.easysocial.js');
		} else {
			array_unshift($coreFiles, 'jquery.js');
		}

		if ($absolutePath) {
			foreach ($coreFiles as &$file) {
				$file = SOCIAL_SCRIPTS . '/vendors/' . $file;
			}
		}

		return $coreFiles;
	}

	/**
	 * Generates script tags that should be added on the page
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string 		Path to the script file
	 */
	public function createScriptTag($path)
	{
		$script = '<script' . (($this->defer) ? ' defer' : '') . (($this->async) ? ' async' : '') . ' src="' . $path . '"></script>';

		// used in es template error.php page. #1693
		self::$attachedTags[] = $script;

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
		$version = ES::getLocalVersion();
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
		$path = $this->baseurl . '/media/com_easysocial/scripts/' . $this->getFileName($section, $jquery);

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
	public function attach()
	{
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

			$minified = true;
			$debug = $this->input->get('debug', '', false);

			if ($this->my->isSiteAdmin() && $debug) {
				$minified = false;
			}

			// If jquery is not rendered, we need to trigger Joomla to enforce it to load jquery
			$withjQuery = $this->config->get('general.jquery');

			// Cheap hack to fix compatibility issues #1175
			$jqueryException = array('com_easyblog', 'com_komento');
			$option = $this->input->get('option', 'cmd', '');

			if (in_array($option, $jqueryException)) {
				$withjQuery = true;
			}

			if (!$withjQuery) {
				// test if EB or KMT already load Joomla jquery or not. if yes, we have to load our own jquery
				if (defined('COM_EASYBLOG_JQUERY_FRAMEWORK') || defined('COM_KOMENTO_JQUERY_FRAMEWORK') || class_exists('EB') || class_exists('KT')) {
					$withjQuery = true;
				}
			}

			if (!$withjQuery) {
				define('COM_EASYSOCIAL_JQUERY_FRAMEWORK', 1);
				JHtml::_('jquery.framework');
			}

			$fileName = $this->getFileUri($this->location, $minified, $withjQuery);


			$this->doc->addCustomTag($this->createScriptTag($fileName));
		}

		// In development mode, we need to attach the easysocial main entry file so the system knows which files to be
		// rendered asynchronously.
		if ($this->environment == 'development') {

			// Render the bootloader on the page first
			$bootloader = $this->baseurl . '/media/com_easysocial/scripts/bootloader.js';
			$this->doc->addCustomTag($this->createScriptTag($bootloader));

			// Render dependencies from the core
			foreach ($this->dependencies as $dependency) {
				$path = $this->baseurl . '/media/com_easysocial/scripts/vendors/' . $dependency;

				$this->doc->addCustomTag($this->createScriptTag($path));
			}

			// Render easysocial's dependencies
			$script = $this->createScriptTag($this->baseurl . '/media/com_easysocial/scripts/' . $this->location . '/' . $this->location . '.js');
			$this->doc->addCustomTag($script);
		}

		self::$attached = true;
	}

	/**
	 * Allows caller to attach an external script
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function addScript($url)
	{
		// We should only attach scrips on html documents otherwise JDocument would hit an error
		if ($this->doc->getType() != 'html') {
			return;
		}

		$tag = $this->createScriptTag($url);

		$this->doc->addCustomTag($tag);
	}
}
