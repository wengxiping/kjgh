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

PP::import('admin:/includes/model');

class PayplansModelNotifications extends PayPlansModel
{
	// Let the parent know that we are trying to filter by app table
	protected $_name = 'app';

	public function __construct()
	{
		parent::__construct('notifications');
	}
	
	/**
	 * Initialize default states
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function initStates($view = null)
	{
		parent::initStates();

		$type = $this->getUserStateFromRequest('type' , 'all');

		$this->setState('type', $type);
	}

	/**
	 * Retrieves a list of available payment gateways
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getItems()
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_app');
		$query[] = 'WHERE ' . $db->qn('group') . '=' . $db->Quote('email');

		$search = $this->getState('search');

		if ($search) {
			$query[] = 'AND LOWER(' . $db->qn('title') . ') LIKE ' . $db->Quote('%' . JString::strtolower($search) . '%');
		}

		$published = $this->getState('published');

		if ($published != 'all' && $published !== '') {
			$query[] = 'AND ' . $db->qn('published') . '=' . $db->Quote($published);
		}

		$type = $this->getState('type');

		if ($type != 'all') {
			$query[] = 'AND ' . $db->qn('type') . '=' . $db->Quote($type);	
		}

		$this->setTotal($query, true);

		$result	= $this->getData($query);

		$apps = array();

		if ($result) {
			foreach ($result as $row) {
				$app = PP::app($row);
				$app->params = $app->getAppParams();

				$apps[] = $app;
			}
		}

		return $apps;
	}

	/**
	 * Retrieve payment apps that is associated with the plan without states and pagination
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPaymentGateways(PPPlan $plan)
	{
		static $cache = array();

		if (!isset($cache[$plan->getId()])) {
			$db = PP::db();

			$query = array();
			$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_app');
			$query[] = 'WHERE ' . $db->qn('group') . '=' . $db->Quote('payment');
			$query[] = 'AND ' . $db->qn('published') . '=' . $db->Quote(1);

			$db->setQuery($query);
			$result = $db->loadObjectList();

			$apps = array();

			// @TODO: We need to add a column to only fetch gateways associated with the plan

			if ($result) {
				foreach ($result as $row) {
					$app = PP::app($row);
					$coreParams = $app->getCoreParams();

					if (!$coreParams->get('applyAll')) {
						continue;
					}

					$apps[] = $app;
				}
			}

			$cache[$plan->getId()] = $apps;
		}

		return $cache[$plan->getId()];
	}

	/**
	 * Generates the path to an email template
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFolder()
	{
		$folder = JPATH_ROOT . '/media/com_payplans/emails/templates';

		return $folder;
	}

	/**
	 * Generates the path to an email template
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAttachmentsFolder()
	{
		$folder = JPATH_ROOT . '/media/com_payplans/emails/attachments';

		return $folder;
	}

	/**
	 * Retrieves a list of template files for notifications
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFiles()
	{
		$folder = $this->getFolder();

		// Retrieve the list of files
		$rows = JFolder::files($folder, '.', true, true);
		$files = array();

		if (!$rows) {
			return $files;
		}

		foreach ($rows as $row) {
			$fileName = basename($row);

			if ($fileName == 'index.html' || stristr($fileName, '.orig') !== false) {
				continue;
			}

			// Get the file object
			$file = $this->getFileObject($row, false, true);
			$files[$file->relative] = $file;
		}

		return $files;
	}

	/**
	 * Retrieves a list of email templates
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getFileObject($absolutePath, $contents = false, $isCore = false)
	{
		$file = new stdClass();
		$file->name = basename($absolutePath);
		$file->path = $absolutePath;
		$file->relative = '/' . basename($absolutePath);

		if ($isCore) {
			$file->relative = str_ireplace($this->getFolder(), '', $file->path);
		}

		// Get the current site template
		$currentTemplate = $this->getCurrentTemplate();

		// Determine if the email template file has already been overriden.
		$overridePath = $this->getOverrideFolder($file->relative);

		$file->override = JFile::exists($overridePath);
		$file->overridePath = $overridePath;
		$file->contents = '';

		if ($contents) {
			if ($file->override) {
				$file->contents = JFile::read($file->overridePath);
			} else {
				$file->contents = JFile::read($file->path);
			}
		}
		return $file;
	}

	/**
	 * Retrieves the current site template
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getCurrentTemplate()
	{
		$db = PP::db();

		$query = 'SELECT ' . $db->nameQuote('template') . ' FROM ' . $db->nameQuote('#__template_styles');
		$query .= ' WHERE ' . $db->nameQuote('home') . '=' . $db->Quote(1);
		$query .= ' AND ' . $db->qn('client_id') . '=' . $db->Quote(0);

		$db->setQuery($query);

		$template = $db->loadResult();
		return $template;
	}

	/**
	 * Generates the path to the overriden folder
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getOverrideFolder($file)
	{
		$path = JPATH_ROOT . '/templates/' . $this->getCurrentTemplate() . '/html/com_payplans/emails/templates/' . ltrim($file, '/');

		return $path;
	}

	/**
	 * Retrieves a list of payment gateways 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApps()
	{
		static $gateways = null;

		if (is_null($gateways)) {
			$file = PP_ADMIN . '/defaults/notifications.json';
			$contents = JFile::read($file);
			
			$gateways = json_decode($contents);
		}

		return $gateways;
	}
}