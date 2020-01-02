<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.filesystem.file');

$file = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/plugins.php';

if (!JFile::exists($file)) {
	return;
}

require_once($file);

class PlgSystemEasySocialAutologin extends EasySocialPlugins
{
	/**
	 * Triggered upon Joomla application initialization
	 *
	 * @since	2.0.20
	 * @access	public
	 */
	public function onAfterRoute()
	{
		$tmpl = $this->input->get('tmpl', '');
		$option = $this->input->get('option', '');

		// We only process on the front end.
		if ($this->app->isAdmin() || $tmpl == 'component') {
			return;
		}

		if (!$this->my->guest || !ES::sso()->hasAutologin()) {
			return;
		}

		$doc = JFactory::getDocument();
		// Only process on html views
		if ($doc->getType() != 'html') {
			return;
		}

		ES::initialize();

		$scripts = ES::sso()->getAutologinScripts();

		$url = JRequest::getUri();

		ES::setCallback($url);

		$doc->addCustomTag('<script type="text/javascript">' . $scripts . '</script>');
	}
}
