<?php
/**
* @package		EasyBlog
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasyBlog is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPCompiler extends Payplans
{
	static $instance = null;
	public $version;
	public $cli = false;

	// These script files should be rendered externally and not compiled together
	// Because they are either too large or only used in very minimal locations.
	public $exclusions = array(
								"chart.js",
								"moment.js"
						);

	// Exclusions based on sections
	public $sectionExclusionsFilters = array();

	public function __construct()
	{
		$this->version = (string) PP::getLocalVersion();

		// Manually insert folders which we would like to exclude
		$this->sectionExclusionsFilters['admin'] = array('vendors\/*');
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
	 * Allows caller to compile a script file on the site, given the section
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function compile($section = 'admin', $minify = true, $jquery = true)
	{
		// Get the file name that should be used after compiling the scripts
		$fileName = $this->getFileName($section, $jquery);

		$files = $this->getFiles($section, $jquery);

		$contents = '';

		// 1. Core file contents needs to be placed at the top
		$contents .= $this->compileJSFiles($files->core);

		// 2. Libraries should be appended next
		$contents .= $this->compileJSFiles($files->libraries);

		// 3. Compile the normal scripts
		$contents .= $this->compileJSFiles($files->scripts);

		$result = new stdClass();
		$result->section = $section;
		$result->minify = $minify;

		// Store the uncompressed version
		$standardPath = PP_SCRIPTS . '/' . $fileName . '.js';

		$this->write($standardPath, $contents);

		$result->standard = $standardPath;
		$result->minified = false;

		// Compress the script and minify it
		if ($minify) {
			$closure = $this->getClosure();

			// 1. Minify the main library
			$contents = $closure->minify($contents);

			// Store the minified version
			$minifiedPath = PP_SCRIPTS . '/' . $fileName . '.min.js';
			$this->write($minifiedPath, $contents);

			$result->minified = $minifiedPath;
		}

		if (defined('PAYPLANS_COMPONENT_CLI')) {
			return $result;
		}

		return $result;
	}

	/**
	 * Compiles core files used in Payplans
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function compileJSFiles($files)
	{
		$contents = '';

		foreach ($files as $file) {
			$contents .= JFile::read($file);
		}

		return $contents;
	}


	/**
	 * Only creates this instance once
	 *
	 * @since	4.0
	 * @access	public
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Retrieves the contents of a particular file
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getContents($file)
	{
		$contents = JFile::read($file);
		return $contents;
	}

	/**
	 * Retrieves the closure compiler
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getClosure()
	{
		$closure = PP::closure();
		return $closure;
	}

	/**
	 * Retrieves a list of files for specific sections
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getFiles($section, $jquery = true)
	{
		$files = new stdClass();

		$coreFiles = $this->getDependenciesFiles(true, $jquery);

		$files->core = $coreFiles;

		// Get a list of libraries
		$files->libraries = $this->getLibraryFiles();

		// Get a list of shared scripts that is used across sections
		$scriptFiles = array();
		$scriptFiles = array_merge($scriptFiles, $this->getSharedFiles());

		// Get script files from the particular section
		$scriptFiles = array_merge($scriptFiles, $this->getScriptFiles($section));
		$files->scripts = $scriptFiles;

		return $files;
	}

	public function getDependenciesFiles($absolutePath = false, $jquery = true)
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

		// Determines if we should include jquery.payplans.js library
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
	 * Retrieves a list of library files used on the site
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getLibraryFiles()
	{
		// Retrieve core dependencies
		$excludes = array('moment', 'jquery.js');

		// Add exclusion files
		foreach ($this->exclusions as $exclusion) {
			$excludes[] = $exclusion;

			// Excluded files may also contain a .min.js
			$excludes[] = str_ireplace('.js', '.min.js', $exclusion);
		}

		// Exclude dependencies since these dependencies are stored in the core
		$dependencies = $this->getDependenciesFiles();
		$excludes = array_merge($excludes, $dependencies);

		$path = PP_SCRIPTS . '/vendors';
		$files = JFolder::files($path, '.js$', true, true, $excludes);

		return $files;
	}

	/**
	 * Retrieves list of shared files that is used across all sections
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getSharedFiles()
	{
		// Retrieve core dependencies
		// $dependencies = PP::scripts()->getDependencies();

		// Get shared scripts
		$files = JFolder::files(PP_SCRIPTS . '/shared', '.js$', true, true, $this->exclusions);

		return $files;
	}

	/**
	 * Retrieves list of scripts that is only used in the particular section
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getScriptFiles($section)
	{
		// Check if we have any exclusion filters defined
		$exclusionFilters = array('^\..*', '.*~');

		if (isset($this->sectionExclusionsFilters[$section])) {
			$exclusionFilters = array_merge($exclusionFilters, $this->sectionExclusionsFilters[$section]);
		}

		$path = PP_SCRIPTS . '/' . $section;
		$files = JFolder::files($path, '.js$', true, true, $this->exclusions, $exclusionFilters);

		return $files;
	}

	/**
	 * Saves the contents into a file
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function write($path, $contents)
	{
		if (JFile::exists($path)) {
			JFile::delete($path);
		}

		return JFile::write($path, $contents);
	}
}
