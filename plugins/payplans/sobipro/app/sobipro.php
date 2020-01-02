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

class PPAppSobipro extends PPApp
{
	protected $_location = __FILE__;

	/**
	 * Applicable only if sobipro exist
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function _isApplicable(PPAppTriggerableInterface $refObject, $eventname = '')
	{
		return $this->helper->exists();
	}

	public function onPayplansSubscriptionAfterSave($prev, $new)
	{
		// No need to trigger if previous and current state is same
		if ($prev != null && $prev->getStatus() == $new->getStatus()) {
			return true;
		}

		$allowToSubmit = $this->getAppParam('addEntryIn', 'any_category');
		$restrictedcategories = $this->getAppParam('addEntryInCategory', 0);
		$restrictedsections = $this->getAppParam('addEntryInSection', 0);
		$submissionLimit = $this->getAppParam('entriesToPublish', 0);

		$subscriptionId = $new->getId();
		$userId = $new->getBuyer()->getId();

		if ($new->isActive()) {
			$action = 1;

			if ($allowToSubmit == 'on_specific_category') {
				foreach ($restrictedcategories as $restrictedcategory) {
					$this->helper->addResource($subscriptionId, $userId, $restrictedcategory, 'com_sobipro.entry' . $restrictedcategory, $submissionLimit);
					$this->helper->toggleCategoryEntry($userId, $submissionLimit, $action, $new, $restrictedcategory);
				}
			}
			
			if ($allowToSubmit == 'on_section') {
				foreach ($restrictedsections as $restrictedsection) {
					$this->helper->addResource($subscriptionId, $userId, $restrictedsection, 'com_sobipro.entry' . $restrictedsection, $submissionLimit);
					$this->helper->toggleSectionEntry($userId, $submissionLimit, $action, $new, $restrictedsection);
				}
			}

			if ($allowToSubmit == 'any_category') {
				$this->helper->addResource($subscriptionId, $userId, 0, 'com_sobipro.entry*', $submissionLimit);
				$this->helper->toggleCategoryEntry($userId, $submissionLimit, $action, $new);
			}
		}
		
		if (($prev != null && $prev->isActive()) && ($new->isExpired() || $new->isOnHold())) {
			$action = 0;
			
			if ($allowToSubmit == 'on_specific_category') {
				foreach ($restrictedcategories as $restrictedcategory){
					$this->helper->toggleCategoryEntry($userId, $submissionLimit, $action, $new,$restrictedcategory);
					$this->helper->removeResource($subscriptionId, $userId,$restrictedcategory, 'com_sobipro.entry' . $restrictedcategory, $submissionLimit);
				}
			}
			
			if ($allowToSubmit == 'on_section'){
				foreach ($restrictedsections as $restrictedsection){
					$this->helper->toggleSectionEntry($userId, $submissionLimit, $action, $new, $restrictedsection);
					$this->helper->removeResource($subscriptionId, $userId,$restrictedsection, 'com_sobipro.entry' . $restrictedsection, $submissionLimit);
				}
			}

			if ($allowToSubmit == 'any_category') {
				$this->helper->toggleCategoryEntry($userId, $submissionLimit, $action, $new);
				$this->helper->removeResource($subscriptionId, $userId,0, 'com_sobipro.entry*', $submissionLimit);
			}
		}

		return true;
	}

	/**
	 * Render Widget
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function renderWidgetHtml()
	{
		$userId = PP::user()->id;
		
		if (!$userId) {
			return '';
		}

		$entries = $this->helper->getEntryResource($userId);

		if (empty($entries)) {
			return '';
		}

		$this->assign('sobipro_entries', $entries);
		$data = $this->_render('widgethtml');
		
		return $data;
	}
}