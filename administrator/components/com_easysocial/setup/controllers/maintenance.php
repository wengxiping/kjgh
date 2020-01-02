<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/controller.php');

class EasySocialControllerMaintenance extends EasySocialSetupController
{
	public $limit = 100;

	public function __construct()
	{
		parent::__construct();
		$this->engine();
	}

	/**
	 * Synchronize Users on the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function syncUsers()
	{
		// Fetch first $limit items to be processed.
		$db = ES::db();
		$query = array();

		$query[] = 'SELECT a.' . $db->nameQuote( 'id' ) . ' FROM ' . $db->nameQuote( '#__users' ) . ' AS a';
		$query[] = 'WHERE a.' . $db->nameQuote( 'id' ) . ' NOT IN( SELECT b.' . $db->nameQuote( 'user_id' ) . ' FROM ' . $db->nameQuote( '#__social_users' ) . ' AS b )';
		$query[] = 'LIMIT 0,' . $this->limit;

		$db->setQuery($query);
		$items = $db->loadObjectList();

		$totalItems = count($items);

		// Nothing to process here.
		if (!$items) {

			$result = new stdClass();
			$result->state 	= 1;

			$result = $this->getResultObj('Great! No users on the site that needs to be updated.', 1, 'success');
			return $this->output($result);
		}

		// Initialize all these users.
		$users = ES::user($items);

		// we need to sync the user into indexer
		foreach ($users as $user) {
			$indexer = ES::get('Indexer');

			$contentSnapshot = array();
			$contentSnapshot[] = $user->getName('realname');

			$idxTemplate = $indexer->getTemplate();

			$content = implode( ' ', $contentSnapshot );
			$idxTemplate->setContent( $user->getName( 'realname' ), $content );

			$url = ''; //FRoute::_( 'index.php?option=com_easysocial&view=profile&id=' . $user->id );
			$idxTemplate->setSource($user->id, SOCIAL_INDEXER_TYPE_USERS, $user->id, $url);

			$date = ES::date();
			$idxTemplate->setLastUpdate( $date->toMySQL() );

			$indexer->index( $idxTemplate );
		}

		// Detect if there are any more records.
		$query = array();
		$query[] = 'SELECT COUNT(1) FROM ' . $db->nameQuote( '#__users' ) . ' AS a';
		$query[] = 'WHERE a.' . $db->nameQuote( 'id' ) . ' NOT IN( SELECT b.' . $db->nameQuote( 'user_id' ) . ' FROM ' . $db->nameQuote( '#__social_users' ) . ' AS b )';

		$db->setQuery($query);
		$total = $db->loadResult();
		$result = $this->getResultObj(JText::sprintf('Synchronized %1s users on the site.', $totalItems), 2, 'success');

		return $this->output($result);
	}

	/**
	 * Synchronize users with the default profile.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function syncProfiles()
	{
		// Fetch first $limit items to be processed.
		$db = ES::db();
		$sql = $db->sql();

		$query = array();
		$query[] = 'SELECT a.' . $db->nameQuote( 'id' ) . ', a.' . $db->nameQuote('name');
		$query[] = 'FROM ' . $db->nameQuote( '#__users' ) . ' AS a';
		$query[] = 'WHERE a.' . $db->nameQuote( 'id' ) . ' NOT IN( SELECT b.' . $db->nameQuote( 'user_id' ) . ' FROM ' . $db->nameQuote( '#__social_profiles_maps' ) . ' AS b )';
		$query[] = 'LIMIT 0,' . $this->limit;

		$db->setQuery( $query );
		$items = $db->loadObjectList();

		// Nothing to process here.
		if (!$items) {
			$result = new stdClass();
			$result->state = 1;

			$result = $this->getResultObj('Great! No orphaned users found. All users on the site is already assigned to a profile.', 1, 'success');
			$this->output( $result );
		}

		// Get the default profile id that we should use.
		$model = ES::model('Profiles');
		$profile = $model->getDefaultProfile();
		$fnField = $model->getProfileField($profile->getWorkflow()->id, 'JOOMLA_FULLNAME');

		// Get the total users that needs to be fixed.
		$totalItems = count($items);

		foreach ($items as $item) {
			$profileMap = ES::table('ProfileMap');
			$profileMap->profile_id = $profile->id;
			$profileMap->user_id = $item->id;
			$profileMap->state = SOCIAL_STATE_PUBLISHED;
			$profileMap->store();

			// lets atleast migrate the user name into profile field;
			// store the data in multirow format
			$names = explode(' ', $item->name);

			$fname = '';
			$lname = '';

			if (is_array($names)) {
				$fname = array_shift($names);
				// if there is still elements in array, lets implode it and set it as last name
				if ($names) {
					$lname = implode(' ', $names);
				}
			}

			$arrNames = array('first' => $fname,
							'middle' => '',
							'last' => $lname,
							'name' => $item->name
						);

			foreach ($arrNames as $key => $val) {

				$fData = ES::table( 'FieldData' );
				$fData->field_id = $fnField->id;
				$fData->uid = $item->id;
				$fData->type = 'user';
				$fData->data = $val;
				$fData->datakey = $key;
				$fData->raw = $val;
				$fData->store();
			}

		}

		// Detect if there are any more records.
		$query = array();
		$query[] = 'SELECT COUNT(1) FROM ' . $db->nameQuote( '#__users' ) . ' AS a';
		$query[] = 'WHERE a.' . $db->nameQuote( 'id' ) . ' NOT IN( SELECT b.' . $db->nameQuote( 'user_id' ) . ' FROM ' . $db->nameQuote( '#__social_profiles_maps' ) . ' AS b )';

		$db->setQuery($query);
		$total = $db->loadResult();

		$result = $this->getResultObj(JText::sprintf('%1s orphaned users found, synchronizing them with a default profile.', $totalItems), 2, 'success');

		return $this->output($result);
	}

	public function getTotalUnsyncUsers()
	{
		// Fetch first $limit items to be processed.
		$db = ES::db();

		$query = "select count(1) from `#__users` as a";
		$query .= " where not exists (select id from `#__social_users` as b where b.`user_id` = a.`id`)";

		$db->setQuery($query);

		$total = $db->loadResult();

		if ($total > $this->limit) {
			$total = ceil($total / $this->limit);
		}

		return $this->output($total);
	}

	public function getTotalUnsyncProfileUsers()
	{
		// Fetch first $limit items to be processed.
		$db = ES::db();

		$query = "select count(1) from `#__users` as a";
		$query .= " where not exists (select id from `#__social_profiles_maps` as b where b.`user_id` = a.`id`)";

		$db->setQuery($query);

		$total = $db->loadResult();

		if ($total > $this->limit) {
			$total = ceil($total / $this->limit);
		}

		return $this->output($total);

	}

	public function getScripts()
	{
		$maintenance = ES::maintenance();

		// Get previous version installed
		$previous = $this->getPreviousVersion('scriptversion');

		$files = array();

		// 1.3 UPDATE: No previous version means this is a fresh installation, then we only run the installed version script.
		if (empty($previous)) {
			$files = $maintenance->getScriptFiles($this->getInstalledVersion(), '==');
		} else {
			$files = $maintenance->getScriptFiles($previous);
		}

		if (empty($files)) {
			$msg = JText::sprintf('COM_EASYSOCIAL_INSTALLATION_MAINTENANCE_NO_SCRIPTS_TO_EXECUTE');
		} else {
			$msg = JText::sprintf('COM_EASYSOCIAL_INSTALLATION_MAINTENANCE_TOTAL_FILES_TO_EXECUTE', count($files));
		}

		$result = array(
			'message' => $msg,
			'scripts' => $files
		);

		return $this->output($result);
	}

	public function runScript()
	{
		$script = JRequest::getVar('script');

		$maintenance = ES::maintenance();

		$state = $maintenance->runScript($script);

		if (!$state) {
			$message = $maintenance->getError();
			$result = $this->getResultObj($message, 0);
		} else {
			$title = $maintenance->getScriptTitle($script);
			$message = JText::sprintf('Executed script: %1s', $title);
			$result = $this->getResultObj($message, 1);
		}

		return $this->output($result);
	}

	public function updateScriptVersion()
	{
		$version = $this->getInstalledVersion();

		// Update the version in the database to the latest now
		$config = ES::table('Config');
		$exists = $config->load(array('type' => 'scriptversion'));
		$config->type = 'scriptversion';
		$config->value = $version;

		$config->store();

		$result = $this->getResultObj('Updated maintenance version.', 1, 'success');

		// Purge all old version files
		ES::purgeOldVersionScripts();

		return $this->output($result);
	}
}
