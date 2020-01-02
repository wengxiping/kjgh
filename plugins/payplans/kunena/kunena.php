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

$file = JPATH_ADMINISTRATOR . '/components/com_payplans/includes/payplans.php';

if (!JFile::exists($file)) {
	return;
}

require_once($file);

class plgPayplansKunena extends PPPlugins
{
	const DO_NOTHING = -1;
	const ALLOWED =  1;
	const BLOCKED =  0;

	public function onPayplansAccessCheck(PPUser $user)
	{
		$option = $this->input->get('option', '');
		$task = $this->input->get('func', '');
		$view = $this->input->get('view', '');

		if ($option !== 'com_kunena') {
			return true;
		}
		
		$lib = PP::kunena();

		if (!$lib->exists()) {
			return true;
		}

		$kUser = $lib->getUser($user->getId());

		// Get category id
		$catId = $this->input->get('catid', 0);
		$categories = $lib->getParentCategories($catId);

		if ($user->isAdmin() || $kUser->isModerator()) {
			return true;
		}

		// Known tasks and views
		$tasks = array('showcat', 'view', 'post', 'listcat');
		$views = array('category', 'topic');

		$allowed = $this->isAllowed($categories, $user, $view);

		if (in_array(SELF::ALLOWED, $allowed)) {
			return true;
		}

		if (in_array(SELF::BLOCKED, $allowed)) {

			if (!$this->my->id) {
				$join = "<a href='index.php?option=com_payplans&view=plan&task=subscribe' >".JText::_('COM_PAYPLANS_ELEMENT_POPUP_CLICK_HERE')." </a>";
				$message = JText::sprintf('COM_PAYPLANS_APP_KUNENA_EITHER_LOGIN_OR_SUBSCRIBE', $join);

				$redirect = PP::getLoginLink();				
				return PP::redirect(PP::getLoginLink(), $message);
			}

			PP::info()->set('COM_PAYPLANS_APP_KUNENA_UPGRADE_PLAN_DESC', 'success');

			$redirect = PPR::_('index.php?option=com_payplans&view=plan', false);
			return PP::redirect($redirect);
		}

		return true;
	}

	/**
	 * Determines if a user can access a specific category
	 *
	 * @since	4.0.0
	 * @access	private
	 */
	private function isAllowed($category, $user, $view = '')
	{
		$kunenaApps = PPHelperApp::getAvailableApps('kunena');
		$appResult  = array();
		
		$lib = PP::kunena();

		$task = $this->input->get('task', '');
		$layout = $this->input->get('layout', '');

		//check which app allow category, which not allow and which app do nothing
		foreach ($kunenaApps as $app) {
			$params = $app->getAppParams();
			$coreParams = $app->getCoreParams();

			$allowedCategories = $params->get('kunenaCategories', array());
			$blockAccess = $params->get('blockaccess', 1);

			if (!is_array($allowedCategories)) {
				$allowedCategories = array($allowedCategories);
			}

			// If app is not applicable on that category 
			if (count(array_intersect($category, $allowedCategories)) == 0) {
				$appResult[] = self::DO_NOTHING;
				continue;
			}

			$plans = $user->getPlans();

			// As the plans are all objects, we need to format it into proper resultset
			if ($plans) {
				$plans = PP::getIds($plans);
			}

			// If user doesn't have any plans 
			//if user has no plans & visibility access is set to none
			if (!$plans && $blockAccess == 1) {
				$appResult[] = self::BLOCKED;
				continue;
			}
		 
			// if user has plans and app is core app 
			$allPlans = $coreParams->get('applyAll', false);

			if ($plans && $allPlans) {
				$appResult[] = self::ALLOWED;
				continue;
			}

			// When user doesn't have any plans, do not allow them to access the write new post page
			if (!$plans && $view == 'topic' && $layout == 'create') {
				$appResult[] = self::BLOCKED;
				continue;
			}

			// If user has app plans
			$appPlans = $app->getPlans();

			if (array_intersect($plans, $appPlans) != false) {
				$appResult[] = self::ALLOWED;
				continue;
			}

			$tasks = array('subscribe', 'post', 'edit');
			$layouts = array('default', 'edit', 'reply', 'unread');

			if ($blockAccess != 1 && $view == 'category' && $task == 'subscribe') {
				$appResult[] = self::BLOCKED;
				continue;
			}
			
			// Visibility access is not set to none, it means user can access thread list
			if ($blockAccess != 1 && $view == 'category') {
				$appResult[] = self::ALLOWED;
				continue;
			}

			// Visibility access is set to read only. User should not be able to reply
			if ($blockAccess == 0 && $view == 'topic' && $layout == 'reply') {
				$appResult[] = self::BLOCKED;
				continue;
			}

			if ($blockAccess == 0 && $view == 'topic' && !in_array($task, $tasks)) {
				$appResult[] = self::ALLOWED;
				continue;
			}

			//visibility access is set to ReplyOnly, it means user can reply on any thread
			//but he can't create new thread
			if ($blockAccess == 3 && $view == 'topic') {
				if (!$task && !$layout) {
					$appResult[]  = self::ALLOWED;
					continue;
				}

				// in case of new topic creation task is post and parent id is 0
				// so we have to restrict here 
				$parentId = $this->input->get('parentid', 0, 'int');

				if ((in_array($task, $tasks) && $parentId != 0) || in_array($layout, $layouts)) {
					$appResult[]  = self::ALLOWED;
					continue;
				}

				$appResult[]  = self::BLOCKED;
				continue;
			}

			$appResult[]  = self::BLOCKED;
			continue;
		}
		
		return $appResult;
	}
}
