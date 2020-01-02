<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class plgPayplansSobipro extends PPPlugins
{
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		$this->helper = $this->getAppHelper();

		if (!$this->helper->exists()) {
			return;
		}
	}

	public function onPayplansAccessCheck(PPUser $user)
	{
		$option = $this->input->get('option', '', 'cmd');
		$task = $this->input->get('task', null, 'cmd');

		$hasSobiproApp = $this->helper->hasSobiproApp();

		$message = JText::_('COM_PAYPLANS_APP_SOBIPRO_YOU_ARE_NOT_ALLOWED_TO_ADD_ENTRY_IN_SELECTED_CATEGORY');
		$url = PPR::_('index.php?option=com_payplans&view=plan&task=subscribe');

		// Retrieve current user subscribed plans
		$userPlans = $user->getPlans();

		// Skip this check if the user is admin
		if ($user->isAdmin()) {
			return false;
		}

		// Only run this if this is submitting entry in sobipro
		if ($option != 'com_sobipro' ||  ($task != 'entry.submit' && $task != 'entry.edit' && $task != 'entry.publish')) {
			return false;
		}

		if ($task == 'entry.submit') {

			// Do not do anything if there do not have any subipro app
			if (!$hasSobiproApp) {
				return false;
			}

			// Do not do anything if current user do not have any subscribed plan
			if ($hasSobiproApp && !$userPlans) {
				PP::info()->set($message, 'info');
				return PP::redirect($url);
			}

			// In sobipro, user able to add entry in multiple categories
			$categoryIds = $this->input->get('field_category',array(), 'array');
			
			// Get the section id of that entry
			$sectionId = $this->input->get('pid', 0);

			$categoryIds[] = $sectionId;

			// Check for submission restriction
			$isRestricted =	$this->helper->restrictSubmission($categoryIds, $user, $sectionId);

			if ($isRestricted) {
				return true;
			}
			
			PP::info()->set($message, 'info');
			return PP::redirect($url);			
		}
		
		if ($task == 'entry.edit' || $task == 'entry.publish') {
			
			// Do not do anything if there do not have any subipro app
			if (!$hasSobiproApp) {
				return false;
			}

			// Do not do anything if current user do not have any subscribed plan
			if ($hasSobiproApp && !$userPlans) {
				PP::info()->set($message, 'info');
				return PP::redirect($url);
			}

			$entryId = $this->input->get('sid', 0);
			
			$categoryId = $this->helper->getEntryCategory($entryId);

			if ($this->helper->restrictSubmission(array($categoryId),$user)) {
				return true;
			}

			PP::info()->set($message, 'info');
			return PP::redirect($url);			
		}

		return true;
	}
}
