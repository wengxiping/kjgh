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

jimport('joomla.filesystem.file');

require_once(PP_LIB . '/themes/themes.php');

class PPScript extends PPThemes
{
	public $extension = 'js';

	public $scriptTag = false;
	public $openingTag = '<script>';
	public $closingTag = '</script>';

	public $CDATA = false;
	public $safeExecution = false;

	public $header = '';
	public $footer = '';
	
	/**
	 * Parses a script file.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function parse($file = '', $vars = null)
	{
		// Pass to the parent to process the theme file
		$vars = parent::parse($this->file, $vars);
		$script	= $this->header . $vars . $this->footer;

		// Do not reveal root folder path.
		$file = str_ireplace(PP_JOOMLA, '', $this->file);

		// Replace \ with / to avoid javascript syntax errors.
		$file = str_ireplace('\\' , '/' , $file);

		$cdata = $this->CDATA;
		$scriptTag = $this->scriptTag;
		$safeExecution = $this->safeExecution;

		ob_start();
		include(PP_MEDIA . '/scripts/template.php');
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	/**
	 * Allows inclusion of scripts within another script
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function output($namespace = null , $vars = null)
	{
		$template = $this->getTemplate($namespace);

		// Ensure that the script file exists
		$exists = JFile::exists($template->script);

		if (!$exists) {
			return;
		}

		$this->file = $template->script;

		$output = $this->parse();

		return $output;
	}
}
