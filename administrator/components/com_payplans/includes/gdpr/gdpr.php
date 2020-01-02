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

include_once(__DIR__ . '/sections.php');
include_once(__DIR__ . '/tabs.php');
include_once(__DIR__ . '/template.php');
require_once(__DIR__ . '/types/abstract.php');

jimport('joomla.filesystem.archive');

class PPGdpr extends PayPlans 
{
	static private $sections = array();

	/**
	 * Allows caller to process download request
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function download($userId)
	{
		$table = PP::table('Download');
		$exists = $table->load(array('user_id' => $userId));

		if (!$exists || !$table->download_id) {
			throw new Exception('Data is not available');
			return;
		}

		$params = new JRegistry($table->params);
		$file = $params->get('path');

		if (!$file) {
			throw new Exception('File is not available for download');
			return;
		}

		$user = PP::user($userId);
		$fileName = JFilterOutput::stringURLSafe($user->getUsername()) . '.zip';

		header("Content-Type: application/zip");
		header("Content-Disposition: attachment; filename=$fileName");
		header("Content-Length: " . filesize($file));

		echo JFile::read($file);
		exit;
	}

	/**
	 * Allows caller to create new sections in the downloads
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function createSection(PPUser $user, $name, $title = '', $subfolder = false)
	{
		if (!isset(self::$sections[$name])) {
			$section = new PayplansGdprSection($user, $name, $title, $subfolder);

			self::$sections[$name] = $section;
		}

		return self::$sections[$name];
	}

	/**
	 * Creates a zip archive of a folder
	 *
	 * @since	4.0.0
	 * @access	private
	 */
	private function createZipFile($sourceFolder, $zipFile)
	{
		// Check if existing zip exists
		$exists = JFile::exists($zipFile);

		if ($exists) {
			JFile::delete($zipFile);
		}

		// get all files from
		$files = JFolder::files($sourceFolder, '', true, true);
		$data = array();

		if ($files) {
			foreach ($files as $file) {
				$file = str_ireplace(array( '\\' ,'/' ) , '/' , $file);

				$tmp = array();
				$tmp['name'] = str_replace($sourceFolder, '', $file);
				$tmp['data'] = JFile::read($file);
				$tmp['time'] = filemtime($file);
				$data[] = $tmp;
			}
		}
		$zip = JArchive::getAdapter('zip');
		$state = $zip->create($zipFile, $data);

		if ($state) {
			// now delete from the tmp folder
			JFolder::delete($sourceFolder);

			return $zipFile;
		}

		return false;
	}

	/**
	 * Invoked by cron
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function cron()
	{
		// Do nothing if download info disabled in configuration
		if (!$this->config->get('users_download')) {
			return;
		}

		$model = PP::model('Download');
		$requests = $model->getRequests();

		if (!$requests) {
			return true;
		}

		foreach ($requests as $request) {

			$table = PP::table('Download');
			$table->bind($request);

			// Lock the request
			$table->state = PP_DOWNLOAD_REQ_LOCKED;
			$table->store();

			// Retrieve the params
			$params = new JRegistry($table->params);

			// Check if this user is valid or not.
			$user = PP::user($table->user_id);

			if (!$user) {
				continue;
			}

			$params = $this->process($user, $params);

			// update state to ready
			$table->state = PP_DOWNLOAD_REQ_READY;
			$table->params = $params->toString();
			$table->store();

			// Send e-mail to the user
			$this->notify($table, $params->get('path'));
		}
		
		return true;
	}

	/**
	 * Processes user data for download of data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function process(PPUser $user, $params)
	{
		$items = $this->getAvailableAdapters();
		$data = array();

		// users sections
		$section = self::createSection($user, 'user', JText::_('COM_PAYPLANS_GDPR_YOUR_INFORMATION'), false);
		$states = array();

		foreach ($items as $type) {
			$adapter = $this->getAdapter($type, $user, $params);
			$adapter->execute($section);

			// Determine if the process is completed
			$adapter->setParams('complete', true);
		}

		// Build html files
		$sections = self::getSections();
		foreach ($sections as $section) {

			// Process section
			$this->processSection($user, $params, $section);
			foreach ($section->tabs as $tab) {
				$this->processTabs($user, $params, $tab);
			}
		}

		// All adapters marked as completed
		$zip = '';

		$complete = true;

		// Zip archive
		$folder = self::getUserTempPath($user);
		$zip = $folder . '.zip';

		$this->createZipFile($folder, $zip);

		$params->set('complete', $complete);
		$params->set('path', $zip);
		return $params;
	}

	/**
	 * Creates a new adapter
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAdapter($type, PPUser $user, $params)
	{
		$this->loadAdapter($type);

		$className = 'PayplansGdpr' . ucfirst($type);
		$adapter = new $className($user, $params);

		return $adapter;
	}

	/**
	 * Retrieves a list of built-in adapters available
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAvailableAdapters()
	{
		static $adapters = null;

		// return array('post','discussion');

		if (is_null($adapters)) {
			$files = JFolder::files(__DIR__ . '/types', '.php$', false, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'abstract.php'));

			foreach ($files as $file) {
				$adapters[] = str_ireplace('.php', '', $file);
			}
		}

		return $adapters;
	}

	/**
	 * Retrieves the list of sections available
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getSections()
	{
		return self::$sections;
	}

	/**
	 * Process sections
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processSection(PPUser $user, $params, $section)
	{
		$hasIndexFile = $section->hasIndexFile();
		if (!$hasIndexFile) {
			$section->createIndexFile($this->getSidebarContents(true));
		}
	}

	/**
	 * Process tabs
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processTabs(PPUser $user, $params, $tab)
	{
		$items = $tab->getItems();
		if ($items) {

			$tabContents = '';

			foreach ($items as $item) {
				// Insert contents into the temporary file
				$tabContents .= $item->getListingContent($tab);

				// Create the view file
				if ($item->hasView()) {
					$item->createViewFile($tab);
				}

				// Add the id into the tab so that it doesn't get processed again
				//$tab->markItemProcessed($item);
			}

			// Create the listing file contents
			$listingFilePath = $tab->getTemporaryListingFileName();
			JFile::append($listingFilePath, $tabContents);
		}


		// Check if it is already finalized
		$finalized = $tab->isFinalized();
		$hasIndexFile = $tab->hasIndexFile();

		if ($finalized && !$hasIndexFile) {
			$tab->createIndexFile($this->getSidebarContents(false, $tab->key));
		}

		return;
	}


	/**
	 * Generates the contents for the sidebar
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getSidebarContents($isRoot = true, $active = '')
	{
		static $sidebars = array();

		$key = $isRoot ? 1 : 0;
		$key .= $active;
		if (!isset($sidebars[$key])) {
			$sections = self::getSections();

			ob_start();

			foreach ($sections as $section) { ?>
				<div class="gdpr-main-title">
					<?php echo $section->title; ?>
				</div>

				<ul class="gdpr-nav">
					<?php if ($section->tabs) { ?>
						<?php foreach ($section->tabs as $tab) { ?>
							<li class="<?php echo $active == $tab->key ? 'is-active' : '';?>">
								<a href="<?php echo $tab->getLink($isRoot); ?>"><?php echo $tab->title; ?></a>
							</li>
						<?php } ?>
					<?php } ?>
				</ul>
			<?php } 

			$contents = ob_get_contents();
			ob_end_clean();

			$sidebars[$key] = $contents;
		}

		return $sidebars[$key];
	}

	/**
	 * Loads adapter
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function loadAdapter($type)
	{
		$file = __DIR__ . '/types/' . $type . '.php';
		require_once($file);
	}

	/**
	 * Notifies the user when the download is complete
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function notify($table, $zipPath)
	{
		$user = PP::user($table->user_id);

		$mailer = PP::mailer();

		$uri = JURI::getInstance();
		$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
		$downloadLink = $base . JRoute::_('index.php?option=com_payplans&view=download&layout=downloadFile', false);

		$subject = JText::_("COM_PAYPLANS_EMAILS_GDPR_DOWNLOAD_SUBJECT");
		$params = array('downloadLink' => $downloadLink);

		$state = $mailer->send($user->getEmail(), $subject, 'emails/download/user', $params, array($zipPath));

		if ($state) {
			$message = JText::_('User data download requests processed and an email notification has sent to user.');
			$content = array('Message' => $message);
			
			PPLog::log(PPLogger::LEVEL_INFO, $message, null, $content, 'PayplansFormatter', 'Payplans_Cron');
		}
	}

	/**
	 * Generates a temporary path for the user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getUserTempPath(PPUser $user)
	{
		static $paths = array();

		$userId = $user->getId();

		if (!JFolder::exists(PP_DOWNLOADS)) {
			JFolder::create(PP_DOWNLOADS);
		}

		if (!isset($paths[$userId])) {
			$paths[$userId] = PP_DOWNLOADS . '/' . md5($user->getId() . $user->getEmail());
			$paths[$userId] = $path = str_ireplace(array( '\\' ,'/' ) , '/' , $paths[$userId]);;

			jimport('joomla.filesystem.folder');

			// If folder exists, we don't need to do anything
			if (JFolder::exists($paths[$userId])) {
				return true;
			}

			// Folder doesn't exist, let's try to create it.
			$state = JFolder::create($paths[$userId]);
		}

		return $paths[$userId];
	}
}
