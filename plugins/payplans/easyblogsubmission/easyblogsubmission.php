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

class plgPayplansEasyblogSubmission extends PPPlugins
{
	/**
	 * Determines which category to be shown to the author
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onEasyBlogPrepareComposerCategories(&$categories, $selectedCategories)
	{
		$user = PP::user();

		if ($this->app->isAdmin() || $user->isAdmin()) {
			return true;
		}

		if (!$categories) {
			return;
		}

		$selectedIds = array();

		// If there are selected categories, it means user is trying to edit the post.
		if ($selectedCategories) {
			foreach ($selectedCategories as $selectedCategory) {
				$selectedIds[$selectedCategory->id] = $selectedCategory->id;
			}
		}

		$helper = $this->getAppHelper();
		$user = PP::user();

		foreach ($categories as $index => $category) {

			// Remove the selected category from the list since they have no access to post into it
			if (!$helper->isAllowedInCategory($category->id, $user->id)) {

				// Determine if there pre-selected categories in the post
				if (!$selectedIds || ($selectedIds && !isset($selectedIds[$category->id]))) {
					unset($categories[$index]);
				}
			}
		}

		$categories = array_values($categories);

		//If catgeories blank then redirect to plan page
		if (!$categories) {
			return $this->redirectDisallowed();
		}
	}

	/**
	 * Triggered before saving a blog post
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onBeforeEasyBlogSave(EasyBlogPost $post, $isNew = false)
	{
		$option = $this->input->get('option', '', 'default');
		$categoryId = $this->input->get('category_id', '', 'default');
		$categories = $this->input->get('categories', '', 'default');

		// if single category posted the create array of categories
		if ($categories == null) {
			$categories = array($categoryId);
		}

		if ($option != 'com_easyblog' || !$categoryId) {
			return;
		}

		// Ensure that the user is logged in
		PP::requireLogin();

		$user = PP::user();

		if ($user->isAdmin()) {
			return true;
		}

		$helper = $this->getAppHelper();
		$allowed = $helper->isAllowed($user);

		if (!$allowed) {
			return $this->redirectDisallowed();
		}

		// check if any app applicable for category
		$userId = $user->getId();

		foreach ($categories as $categoryId) {
			$allowedCat = $helper->isAllowedInCategory($categoryId, $userId);

			if (!$allowedCat) {
				return $this->redirectDisallowed();
			}
				
			// The user is allowed, check their limits
			$exceededLimit = $helper->exceededLimit($categoryId, $user);

			// Decrease their limits
			if (!$exceededLimit) {
				$helper->decreaseAll(0, $user);
				$helper->decreaseAll($categoryId, $user);
			}
		}

		return true;
	}

	/**
	 * Standard redirection method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function redirectDisallowed()
	{
		$helper = $this->getAppHelper();
		$redirect = $helper->getRedirectPlanLink();
		$message = JText::_('COM_PAYPLANS_APP_EASYBLOG_SUBMISSION_NOT_ALLOWED_MORE_POST');

		$doc = JFactory::getDocument();

		if ($doc->getType() != 'html') {
			return EB::ajax()->reject(EB::exception($message))->send();
		}

		PP::info()->set($message, 'error');
		return PP::redirect($redirect);
	}

}