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

class PPAppEasyBlog extends PPApp
{
	public function isApplicable($refObject = null, $eventName='')
	{
		if ($eventName === 'onPayplansAccessCheck') {
			return true;
		}

		return parent::isApplicable($refObject, $eventName);
	}

	/**
	 * Triggered when checking for access
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansAccessCheck(PPUser $user)
	{
		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'default');

		if ($option != 'com_easyblog') {
			return true;
		}

		$user = PP::user();
		if ($user->isAdmin()) {
			return true;
		}
		
		// We only want to check on certain views
		if (!$this->helper->isSupportedView($view)) {
			return true;
		}

		$this->$view();
	}

	/**
	 * EasyBlog categories view checking
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function categories()
	{
		$id = $this->input->get('id', '');

		if (!$id) {
			return true;
		}

		$applicable = $this->helper->isCategoryApplicable($id);

		if (!$applicable) {
			return true;
		}

		// Ensure that the user is logged in
		$this->helper->requireLogin();
		
		$user = PP::user();
		$accessibleCategories = $this->helper->getAccessibleCategories($user);

		if (!$accessibleCategories || !array_key_exists($id, $accessibleCategories)) {
			$redirect = EBR::_('index.php?option=com_easyblog&view=categories', false);
			$link = $this->helper->getRedirectPlanLink();
			EB::info()->set(JText::sprintf('You are not allowed to view this category. To view more, please subscribe to a plan', '<a href="' . $link . '">subscribe to a plan</a>'), 'error');
			return PP::redirect($redirect);
		}

		// User is permitted to view this category
		return true;
	}

	/**
	 * EasyBlog entry view checking
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function entry()
	{
		$id = $this->input->get('id', '');

		// Get the post
		$post = EB::post($id);
		$categoryId = $post->getPrimaryCategory()->id;

		$applicable = $this->helper->isCategoryApplicable($categoryId);

		if (!$applicable) {
			return true;
		}
		
		$this->helper->requireLogin();

		$user = PP::user();
		$accessibleCategories = $this->helper->getAccessibleCategories($user);

		if (!$accessibleCategories || !in_array($categoryId, $accessibleCategories)) {
			return $this->redirectUnauthorized();
		}
		return true;
	}

	/**
	 * Standard redirection message
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function redirectUnauthorized()
	{
		PP::info()->set('COM_PAYPLANS_APP_EASYBLOG_UPGRADE_PLAN_DESC', 'error');

		$redirect = $this->helper->getRedirectPlanLink();
		return PP::redirect($redirect);
	}
}