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

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class EasySocialControllerUsers extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		// Map the alias methods here.
		$this->registerTask('save', 'store');
		$this->registerTask('savenew', 'store');
		$this->registerTask('apply', 'store');

		$this->registerTask('publish', 'togglePublish');
		$this->registerTask('unpublish', 'togglePublish');

		$this->registerTask('activate', 'toggleActivation');
		$this->registerTask('deactivate', 'toggleActivation');
	}

	/**
	 * Approves a user
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function approve()
	{
		ES::checkToken();

		$ids = $this->input->get('id', array(), 'int');

		if (!$ids) {
			$ids = $this->input->get('cid', array(), 'int');
		}

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_USERS_INVALID_ID_PROVIDED');
		}

		$sendEmail = $this->input->get('sendConfirmationEmail', false, 'bool');

		foreach ($ids as $id) {
			$user = ES::user((int) $id);
			$user->approve($sendEmail);
		}

		$this->view->setMessage('COM_EASYSOCIAL_USERS_APPROVED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__, $user);
	}

	/**
	 * Approves a request for verification
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function approveVerification()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		$verification = ES::verification();

		foreach ($ids as $id) {
			$verification->approve($id);
		}

		$this->view->setMessage('COM_ES_USERS_VERIFIED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Approves a request for verification
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function rejectVerification()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		$verification = ES::verification();

		foreach ($ids as $id) {
			$verification->reject($id);
		}

		$this->view->setMessage('COM_ES_USERS_REJECTED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Assigns user to a specific group
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function assign()
	{
		ES::checkToken();

		$ids = JRequest::getVar('cid');
		$ids = ES::makeArray($ids);
		$gid = JRequest::getInt('gid');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_USERS_UNABLE_TO_FIND_USER');
		}

		if (!$gid) {
			return $this->view->exception('COM_EASYSOCIAL_USERS_UNABLE_TO_FIND_GROUP');
		}

		foreach ($ids as $id) {
			$user = ES::user((int) $id);
			$user->assign($gid);
		}

		$this->view->setMessage('COM_EASYSOCIAL_USERS_ASSIGNED_TO_GROUP');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Unbans a user
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function unban()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_USERS_UNABLE_TO_FIND_USER');
		}

		foreach ($ids as $id) {
			$user = ES::user((int) $id);
			$user->unban();
		}

		$this->view->setMessage('COM_ES_USERS_UNBANNED');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Bans a user permanently
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function banPermanent()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_USERS_UNABLE_TO_FIND_USER');
		}

		foreach ($ids as $id) {
			$user = ES::user((int) $id);
			$user->ban();
		}

		$this->view->setMessage('COM_ES_USERS_BANNED_PERMANENTLY');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Resends an activation email
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function resendActivate()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'Array');

		$model = ES::model('Registration');
		$total = 0;

		foreach ($ids as $id) {

			$id = (int) $id;
			$user = ES::user($id);

			// If the user is not blocked and doesn't have an activation, we shouldn't be doing anything.
			if (!$user->block || !$user->activation) {
				continue;
			}

			$model->resendActivation($user);
			$total++;
		}

		if (!$total) {
			$this->view->setMessage('COM_EASYSOCIAL_USERS_ACTIVATION_EMAIL_NO_VALID_USERS', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$this->view->setMessage('COM_EASYSOCIAL_USERS_ACTIVATION_EMAIL_RESENT');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Toggle's user publishing state
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function togglePublish()
	{
		ES::checkToken();


		$task = $this->getTask();
		$ids = $this->input->get('cid', array(), 'array');
		$method = $task == 'unpublish' ? 'block' : 'unblock';

		foreach ($ids as $id) {
			$user = ES::user($id);

			// Do not allow the person to block themselves.
			if ($user->id == $this->my->id) {
				return $this->view->exception('COM_EASYSOCIAL_USERS_NOT_ALLOWED_TO_BLOCK_SELF');
			}

			$state = $user->$method();
		}

		$message = $task == 'unpublish' ? 'COM_EASYSOCIAL_USERS_UNPUBLISHED_SUCCESSFULLY' : 'COM_EASYSOCIAL_USERS_PUBLISHED_SUCCESSFULLY';

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__, $task);
	}

	/**
	 * Toggles activation
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function activate()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		foreach ($ids as $id) {
			$user = ES::user((int) $id);
			$user->activate();
		}

		$this->view->setMessage('User account activated successfully');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Exports users into csv format
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function export()
	{
		ES::checkToken();

		$output = fopen('php://output', 'w');

		// Determines if this export is on specific profile
		$id = $this->input->get('profileId', '', 'int');

		if (!$id) {
			$profileTitle = 'all';
		} else {
			$profile = ES::table('Profile');
			$profile->load($id);

			$profileTitle = str_ireplace(' ', '_', strtolower($profile->get('title')));
		}

		// Get a list of users and their custom fields
		$model = ES::model('Users');
		$data = $model->export($id);

		// Output each row now
		foreach ($data as $row) {
			fputcsv($output, $row);
		}

		// Generate the date of export
		$date = ES::date();
		$fileName = 'users_export_' . $profileTitle . '_' . $date->format('m_d_Y') . '.csv';

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . $fileName);

		fclose($output);
		exit;

	}

	/**
	 * Process user csv import
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function import()
	{
		ES::checkToken();

		$file = $this->input->files->get('user_import_csv');
		$data = ES::parseCSV($file['tmp_name'], false, false);

		$profileId = $this->input->get('profileId', '', 'default');
		$previousData = $this->input->get('previousData', '', 'default');

		if ($previousData) {
			$path = SOCIAL_IMPORT_CSV_DIR . '/es-userimport.csv';
			$data = ES::parseCSV($path, false, false);

			$previousData = json_decode($previousData);

			return $this->view->call('importSettings', $data, $profileId);
		}

		// Perform data validation
		if (!$data) {
			$this->view->info->set(false, 'COM_ES_INVALID_CSV_FILE', 'error');
			return $this->view->call(__FUNCTION__);
		}

		// Copy the file to tmp folder
		$path = SOCIAL_IMPORT_CSV_DIR . '/es-userimport.csv';

		if (!JFolder::exists(SOCIAL_IMPORT_CSV_DIR)) {
			JFolder::create(SOCIAL_IMPORT_CSV_DIR);
		} else {
			// Removed all files in the folder
			if (JFile::exists($path)) {
				JFile::delete($path);
			}
		}

		$state = JFile::copy($file['tmp_name'], $path);

		// Perform data validation
		if (!$state) {
			return $this->view->exception('COM_ES_INVALID_CSV_FILE');
		}

		$profileId = $this->input->get('profileId', '', 'default');

		return $this->view->call('importSettings', $data, $profileId);
	}

	public function importSettings()
	{
		ES::checkToken();

		$fields = $this->input->get('field_id', '', 'default');
		$profileId = $this->input->get('profileId', '', 'default');

		// Get the csv file
		$path = SOCIAL_IMPORT_CSV_DIR . '/es-userimport.csv';
		$data = ES::parseCSV($path, false, false);

		if (!$data) {
			$this->view->info->set(false, 'COM_ES_INVALID_CSV_FILE', 'error');
			return $this->view->call('import');
		}

		if (!$fields || !$profileId) {
			$this->view->info->set(false, 'COM_ES_USER_IMPORT_PLEASE_SELECT_FIELDS', 'error');
			return $this->view->call('importSettings', $data, $profileId);
		}

		// Construct the import options
		$options = array('profileId' => $profileId);
		$options['autopassword'] = $this->input->get('import_autopassword', '', 'default');
		$options['autoapprove'] = $this->input->get('import_autoapprove', '', 'default');
		$options['passwordtype'] = $this->input->get('import_passwordtype', 'plain', 'default');
		$options['passwordFieldId'] = $this->input->get('passwordFieldId', '', 'default');

		$profile = ES::table('Profile');
		$profile->load($profileId);

		// Get list of custom field available for selected profile
		$customFields = $profile->getCustomFields(null, array('exclusion' => array('avatar', 'cover')));

		$flippedFields = array_flip($fields);

		foreach ($customFields as $field) {
			if ($field->isCore() && !isset($flippedFields[$field->id])) {

				// Password field is not required when password is auto generated
				if ($options['autopassword'] && $field->id == $options['passwordFieldId']) {
					continue;
				}

				$this->view->info->set(false, 'COM_ES_IMPORT_PLEASE_SELECT_REQUIRED_FIELDS', 'error');
				return $this->view->call('importSettings', $data, $profileId);
			}
		}

		return $this->view->call('importOverview', $fields, $options);
	}

	public function importUser()
	{
		ES::checkToken();

		$fieldIds = $this->input->get('field_ids', '', 'default');
		$profileId = $this->input->get('profile_id', '', 'default');
		$total = $this->input->get('total', 0, 'int');
		$limit = $this->input->get('limit', 20, 'int');
		$importOptions = $this->input->get('importOptions', '', 'default');

		if (is_string($fieldIds)) {
			$fieldIds = json_decode($fieldIds);
		}

		if (is_string($importOptions)) {
			$importOptions = json_decode($importOptions);
		}

		$complete = false;
		$success = array();
		$failed = array();

		// Get the file
		$path = SOCIAL_IMPORT_CSV_DIR . '/es-userimport.csv';
		$data = ES::parseCSV($path, false, false);
		$currentTotal = count($data);

		// Determine how many item left to be processed
		$progress = ($total - $currentTotal) / $total * 100;
		$progress = round($progress, 2);

		if ($progress >= 100) {
			$progress = 100;
			$complete = true;

			if (JFile::exists($path)) {
				JFile::delete($path);
			}
		} else {
			// Process the user here.
			$processed = 0;
			foreach ($data as $key => $item) {
				$model = ES::model('Users');

				// Import the item
				$state = $model->import($item, $fieldIds, $profileId, $importOptions);
				$error = $state ? false : $model->getError();

				$theme = ES::themes();
				$theme->set('items', $item);
				$theme->set('fieldIds', $fieldIds);
				$theme->set('error', $error);
				$content = $theme->output('admin/users/import/overview.item');

				$importStatus = new stdClass();
				$importStatus->result = $state;
				$importStatus->error = $error;
				$importStatus->content = $content;

				if ($state) {
					$success[] = $importStatus;
				} else {
					$failed[] = $importStatus;
				}

				unset($data[$key]);
				$processed++;

				if ($processed >= $limit) {
					break;
				}
			}

			// Generate CSV data from array as buffer
			$tmp = fopen('php://temp', 'rw');

			foreach ($data as $row) {
				fputcsv($tmp, $row);
			}

			rewind($tmp);
			$csv = stream_get_contents($tmp);
			fclose($tmp);

			JFile::delete($path);
			JFile::write($path, $csv);
		}

		$status = new stdClass();
		$status->progress = $progress;
		$status->success = $success;
		$status->failed = $failed;
		$status->totalSuccess = count($success);
		$status->totalFailure = count($failed);

		return $this->ajax->resolve($status, $complete);
	}

	/**
	 * Switches a user's profile
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function switchProfile()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'array');
		$profileId = $this->input->get('profile', 0, 'int');
		$model = ES::model('Profiles');

		// For invalid ids
		if (!$ids) {
			return $this->view->exception('Invalid user id provided');
		}

		// Should we be updating the user group in Joomla
		$updateGroups = $this->input->get('switch_groups', false, 'bool');

		// Get the workflow from the profile as well
		$workflow = ES::workflows()->getWorkflow($profileId, SOCIAL_TYPE_USER);

		foreach ($ids as $id) {

			// Switch the user's profile
			$model->updateUserProfile($id, $profileId, $workflow->id);

			// Determines if we should also update the user's usergroups
			if ($updateGroups) {
				$model->updateJoomlaGroup($id, $profileId);
			}

			$user = ES::user($id);
			$user->syncIndex();
		}

		$this->view->setMessage('COM_EASYSOCIAL_USERS_USER_PROFILE_UPDATED');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allows caller to reset points for specific user
	 *
	 * @since	1.4.7
	 * @access	public
	 */
	public function resetPoints()
	{
		ES::checkToken();

		// Get the current view
		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			$points = ES::points();
			$points->reset((int) $id);
		}

		$this->view->setMessage('COM_EASYSOCIAL_USERS_POINTS_RESET_SUCCESS');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Inserts points for a list of users
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function insertPoints()
	{
		ES::checkToken();

		$points = $this->input->get('points', 0, 'int');
		$message = $this->input->get('message', '', 'default');
		$uids = $this->input->get('uid', array(), 'int');

		if (!$uids) {
			return $this->view->exception('COM_EASYSOCIAL_USERS_UNABLE_TO_FIND_USER');
		}

		// Load up our own points library.
		$lib = ES::points();

		foreach ($uids as $userId) {
			$user = ES::user((int) $userId);

			$lib->assignCustom($user->id, $points, $message);
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_USERS_POINTS_ASSIGNED_TO_USERS', $points));

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Inserts a badge for a list of users
	 *
	 * @since	2.0.14
	 * @access	public
	 */
	public function insertBadge()
	{
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$badge = ES::table('Badge');
		$badge->load($id);

		if (!$id || !$badge->id) {
			return $this->view->exception('COM_EASYSOCIAL_USERS_UNABLE_TO_FIND_BADGE');
		}

		$uids = $this->input->get('uid', array(), 'array');

		if (!$uids) {
			return $this->view->exception('COM_EASYSOCIAL_USERS_UNABLE_TO_FIND_USER');
		}

		$model = ES::model('Badges');
		$message = $this->input->get('message', '', 'default');
		$achieved = $this->input->get('achieved', '', 'default');

		foreach ($uids as $userId) {
			$user = ES::user((int) $userId);

			// Only create a new record if user hasn't achieved the badge yet.
			if (!$model->hasAchieved($badge->id, $user->id)) {
				$badges = ES::badges();
				$badges->create($badge, $user, $message, $achieved);
			}
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_USERS_BADGE_ASSIGNED_TO_USERS', $badge->get('title')));

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Retrieves the total number of pending users on the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalPending()
	{
		ES::checkToken();

		$model = ES::model('Users');
		$total = (int) $model->getTotalPending();

		return $this->view->call(__FUNCTION__, $total);
	}

	/**
	 * Allows caller to remove a badge
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function removeBadge()
	{
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$userId = $this->input->get('userid', 0, 'int');

		$badge = ES::badges();
		$badge->remove($id, $userId);

		$this->view->setMessage('Achievement removed from user successfully');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Deletes a user from the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete()
	{
		ES::checkToken();

		// Get the list of user that needs to be deleted.
		$ids = $this->input->get('id', array(), 'array');

		foreach ($ids as $id) {
			$user = ES::user((int) $id);

			if ($user) {
				$user->delete();
			}
		}

		return $this->view->call(__FUNCTION__);
	}


	/**
	 * Deletes specific download requests
	 *
	 * @since	2.2.3
	 * @access	public
	 */
	public function deleteDownload()
	{
		// Get the list of user that needs to be deleted.
		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			$id = (int) $id;

			$download = ES::table('Download');
			$exists = $download->load($id);

			if ($exists) {
				$download->delete();
			}
		}

		$this->view->setMessage('COM_ES_DOWNLOAD_REQUESTS_PURGED_SUCCESSFULLY');

		return $this->view->setRedirection('index.php?option=com_easysocial&view=users&layout=downloads');
	}

	/**
	 * Purge download requests
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function purgeDownloads()
	{
		ES::checkToken();

		$model = ES::model('Download');
		$model->purgeRequests();

		$this->view->setMessage('COM_ES_DOWNLOAD_REQUESTS_PURGED_SUCCESSFULLY');

		return $this->view->setRedirection('index.php?option=com_easysocial&view=users&layout=downloads');
	}

	/**
	 * Rejects a user's registration request
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function reject()
	{
		ES::checkToken();

		$ids = $this->input->get('id', array(), 'int');

		// Determine if we should send a confirmation email to the user.
		$sendEmail = $this->input->get('email', false, 'bool');

		// Determine if we should delete the user.
		$deleteUser = $this->input->get('deleteUser', false, 'bool');

		// Get the rejection message
		$reason = $this->input->get('reason', '', 'default');
		$reason = nl2br($reason);

		foreach ($ids as $id) {
			$user = ES::user((int) $id);
			$user->reject($reason, $sendEmail, $deleteUser);
		}

		$this->view->setMessage('COM_EASYSOCIAL_USERS_REJECTED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Removes verified status from users
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function removeVerified()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		foreach ($ids as $id) {
			$user = ES::user((int) $id);
			$user->removeVerified();
		}

		$this->view->setMessage('COM_ES_SELECTED_USERS_UNVERIFIED');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Sets the particular user as verified
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function setVerified()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		foreach ($ids as $id) {
			$user = ES::user((int) $id);
			$user->setVerified();
		}

		$this->view->setMessage('COM_ES_SELECTED_USERS_VERIFIED');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Stores the user object
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function store()
	{
		ES::checkToken();
		ES::language()->loadSite();

		// Get the current task
		$task = $this->getTask();

		// Determine if this is an edited user.
		$id = $this->input->get('id', 0, 'int');
		$id = !$id ? null : $id;

		// Get the posted data
		$post = $this->input->getArray('post');

		// this should come from backend user management page only.
		$autoApproval = isset($post['autoapproval']) ? $post['autoapproval'] : 0;
		$sendWelcomeMail = isset($post['sendWelcomeMail']) ? $post['sendWelcomeMail'] : 0;

		// Create an options array for custom fields
		$options = array();

		if (!$id) {
			$user = new SocialUser();

			// Get the profile id
			$profileId = $this->input->get('profileId');
		} else {

			$user = ES::user($id);
			$profileId = $user->getProfile()->id;

			$options['data'] = true;
			$options['dataId'] = $id;
			$options['dataType'] = SOCIAL_TYPE_USER;
		}

		$profile = ES::table('Profile');
		$profile->load($profileId);

		$options['workflow_id'] = $profile->getWorkflow()->id;
		$options['group'] = SOCIAL_FIELDS_GROUP_USER;

		// Get fields model
		$fieldsModel = ES::model('Fields');
		$fields = $fieldsModel->getCustomFields($options);

		// Initialize default registry
		$registry = ES::registry();

		// Get disallowed keys so we wont get wrong values.
		$disallowed = array(ES::token(), 'option' , 'task' , 'controller', 'autoapproval');

		// Process $_POST vars
		foreach ($post as $key => $value) {

			if (!in_array($key, $disallowed)) {

				if (is_array($value)) {
					$value = json_encode($value);
				}

				$registry->set($key, $value);
			}
		}

		// Test to see if the points has changed.
		$points = $this->input->get('points', 0, 'int');

		// Lets get the difference of the points
		$userPoints = $user->getPoints();

		// If there is a difference, the admin may have altered the user points
		if ($userPoints != $points) {

			// Insert a new points record for this new adjustments.
			if ($points > $userPoints) {

				// If the result points is larger, we always need to subtract and get the balance.
				$totalPoints = $points - $userPoints;
			} else {

				// If the result points is smaller, we always need to subtract.
				$totalPoints = -($userPoints - $points);
			}

			$pointsLib = ES::points();
			$pointsLib->assignCustom($user->id, $totalPoints, JText::_('COM_EASYSOCIAL_POINTS_ADJUSTMENTS'));

			$user->points = $points;
		}

		// Convert the values into an array.
		$data = $registry->toArray();

		// Get the fields lib
		$fieldsLib = ES::fields();

		// Get the general field trigger handler
		$handler = $fieldsLib->getHandler();

		// Build arguments to be passed to the field apps.
		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$user);

		// Format conditional data
		$fieldsLib->trigger('onConditionalFormat', SOCIAL_FIELDS_GROUP_USER, $fields, $args, array($handler));

		// Rebuild the arguments since the data is already changed previously.
		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$user);

		// @trigger onAdminEditValidate
		$errors = $fieldsLib->trigger('onAdminEditValidate', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		// If there are errors, we should be exiting here.
		if (is_array($errors) && count($errors) > 0) {
			// We need to set the data into the post again because onEditValidate might have changed the data structure
			JRequest::set($data, 'post');

			$this->view->setMessage('COM_EASYSOCIAL_PROFILE_SAVE_ERRORS', ES_ERROR);

			return $this->view->call('form', $errors);
		}

		// @trigger onAdminEditBeforeSave
		$errors = $fieldsLib->trigger('onAdminEditBeforeSave', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		if (is_array($errors) && count($errors) > 0) {
			// We need to set the data into the post again because onEditValidate might have changed the data structure
			JRequest::set($data, 'post');

			$this->view->setMessage('COM_EASYSOCIAL_PROFILE_ERRORS_IN_FORM', ES_ERROR);
			return $this->view->call('form', $errors);
		}

		// Update the user's gid
		$gid = $this->input->get('gid', array(), 'array');
		$data['gid'] = $gid;

		// Bind the user object with the form data.
		$user->bind($data);

		// Create a new user record if the id don't exist yet.
		if (!$id) {
			$model = ES::model('Users');
			$user = $model->create($data, $user, $profile, $autoApproval);

			if (!$user) {
				// We need to set the data into the post again because onEditValidate might have changed the data structure
				JRequest::set($data, 'post');

				$this->view->setMessage($model->getError(), ES_ERROR);
				return $this->view->call('form');
			}

			$message = 'COM_EASYSOCIAL_USERS_CREATED_SUCCESSFULLY';

			if ($autoApproval) {

				$message = 'COM_EASYSOCIAL_USERS_CREATED_SUCCESSFULLY_AND_APPROVED';

				if ($sendWelcomeMail) {

					// Load registration model
					$registrationModel = ES::model('Registration');

					// send welcome email notification for user if their account approval immediately
					$registrationModel->notify($data, $user, $profile, false, $sendWelcomeMail);
				}
			}

		} else {
			// If this was an edited user, save the user object.
			$user->save();

			$message = 'COM_EASYSOCIAL_USERS_USER_UPDATED_SUCCESSFULLY';
		}

		// Reconstruct args
		$args = array(&$data, &$user);
		$fieldsLib->trigger('onAdminEditAfterSave', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		// Bind the custom fields for the user.
		$user->bindCustomFields($data);

		// Reconstruct args
		$args = array(&$data, &$user);
		$fieldsLib->trigger('onAdminEditAfterSaveFields', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		// Prepare the dispatcher
		ES::apps()->load(SOCIAL_TYPE_USER);

		$args = array(&$user, &$fields, &$data);

		$dispatcher = ES::dispatcher();
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onUserProfileUpdate', $args);

		// Process notifications
		if (isset($post['notifications']) && !empty($post['notifications'])) {
			$systemNotifications = $post['notifications']['system'];
			$emailNotifications = $post['notifications']['email'];

			// Store the notification settings for this user.
			$model = ES::model('Notifications');

			$model->saveNotifications($systemNotifications, $emailNotifications, $user);
		}

		// Process privacy items
		if (isset($post['privacy']) && !empty($post['privacy'])) {
			$resetPrivacy = isset($post['privacyReset']) ? true : false;

			$user->bindPrivacy($post['privacy'], $post['privacyID'], $post['privacyCustom'], $post['privacyOld'], $resetPrivacy);
		}

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__, $task, $user);
	}
}
