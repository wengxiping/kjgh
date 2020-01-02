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

class plgPayPlansMtree extends PPPlugins
{
	/**
	 * After an item is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onContentAfterSave($resourceName, $link, $isNew)
	{
		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'default');
		$task = $this->input->get('task', '', 'cmd');

		if ($option != 'com_mtree') {
			return;
		}

		if ($view == 'addlisting' || $task == 'approve_publish_links')  {
			$linkId = $link->get('link_id');

			// Trigger PayPlans event
			$args = array(&$linkId);
			PPEvent::trigger('onPayplansUpdateFeaturing', $args);

			return true;
		}
	}

	/**
	 * Check against access
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansAccessCheck(PPUser $user)
	{
		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '');
		$task = $this->input->get('task', '', 'cmd');
		$catId = $this->input->get('cat_id', false);

		if ($option != 'com_mtree') {
			return;
		}

		// We only want to check against certain views
		if ($view != 'addlisting' && $task != 'addlisting') {
			return;
		}

		// Ensure that user is logged in.
		if (!$user->getId()) {
			return;
		}

		$plans = $user->getPlans();

		// when user does not have the required plan(plan attached with the app)
		if (!$plans) {
			PP::info()->set('COM_PAYPLANS_APP_MTREE_SELECT_PLAN', 'error');

			$redirect = PPR::_('index.php?option=com_payplans&view=plan', false);

			return PP::redirect($redirect);
		}

		$helper = $this->getAppHelper();

		$resource = $helper->getUserResource($user->getId());

		// If there is no resource for this user, create one.
		if (!$resource) {
			$publishCount = $helper->getTotalAllowedToPublish();
			$featureCount = $helper->getTotalAllowedToFeature();

			$subscriptions = $user->getSubscriptions(PP_SUBSCRIPTION_ACTIVE);

			if (!$subscriptions) {
				return;
			}

			$resource = PP::resource();

			foreach ($subscriptions as $subId => $sub) {
				$resource->add($subId, $user->getId(), $catId, 'com_mtree.publish', $publishCount);
				$resource->add($subId, $user->getId(), $catId, 'com_mtree.feature', $featureCount);
			}
		}

		$allowed = $helper->isUserAllowed($user);

		if (!$allowed) {
			PP::info()->set('COM_PAYPLANS_APP_MTREE_SELECT_PLAN', 'error');
			$redirect = PPR::_('index.php?option=com_payplans&view=plan', false);

			return PP::redirect($redirect);
		}

		// Here we assume that the user is allowed to create a new listing
		$apps = $helper->getAvailableApps();

		if (!$apps) {
			return;
		}
		
		foreach ($apps as $app) {
			$count = $resource->count;
			$restrictionType = $app->getAppParam('category_to_restrict', 'any');

			if ($restrictionType == 'any') {

				$totalCreated = $helper->getTotalItems($user->getId());

				if ($totalCreated >= $count) {

					$redirect = JRoute::_('index.php?option=com_mtree&task=mypage', false);
					return PP::redirect($redirect, 'COM_PAYPLANS_APP_MTREE_YOU_ARE_NOT_ALLOWED_TO_ADD_MORE_LISTING');
				}

				continue;
			}

			// Here we assume the restriction type is per category
			if ($task != 'savelisting') {
				continue;
			}

			$categories = $app->getAppParam('restrict_specific', array());
			
			if (!is_array($categories) && $categories) {
				$categories = array($categories);
			}

			$childCategories = $helper->getChildCategory($categories);
			$categories = array_merge($childCategories, $categories);

			$currentCategoryId = array($catId);

			if (!$currentCategoryId) {
				continue;
			}
			
			if (array_intersect($categories, $currentCategoryId)) {
				$posts = $helper->getUserItems($user->getId());
				$totalPostsInCategories = $helper->getTotalUserItemsInCategory($posts, $categories);

				if ($totalPostsInCategories >= ($app->getAppParam('publish_listings_on_active', false))) {

					$redirect = JRoute::_('index.php?option=com_mtree&task=mypage', false);
					return PP::redirect($redirect, 'COM_PAYPLANS_APP_MTREE_YOU_ARE_NOT_ALLOWED_TO_ADD_MORE_LISTING');
				}
			}
		}
	}
}
