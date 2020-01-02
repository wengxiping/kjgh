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

class PPAppK2category extends PPApp
{
	public function isApplicable($refObject = null, $eventName = '')
	{
		// applicable only if k2 component exists
		if (!$this->helper->exists()) {
			return false;
		}
		
		if ($eventName == 'onPayplansAccessCheck') {
			return true;
		}

		return parent::isApplicable($refObject, $eventName);
	}
	
	public function onPayplansSubscriptionAfterSave($prev, $new)
	{
		// no need to trigger if previous and current state is same
		if ($prev != null && $prev->getStatus() == $new->getStatus()){
			return true;
		}
		
		$category = $this->getAppParam('category', array());
		$allowedEntries = $this->getAppParam('allowed_submission',0);
		$allowed_submission = explode(',', $allowedEntries);
		$subid = $new->getId();
		$user = $new->getBuyer();
		$userId = $user->getId();

		$category = (is_array($category))? $category : array($category);
		
		if ($new->isActive()) {
			$action = 1;

			for ($i=0;$i < count($category);$i++) {
				if (!isset($allowed_submission[$i])) {
					$allowed_submission[$i] = $allowed_submission[count($allowed_submission) - 1];
				}

				$this->helper->addResource($subid, $userId, $category[$i], 'com_k2.submission' . $category[$i], $allowed_submission[$i]);
				$this->helper->changeItemState($userId, $allowed_submission[$i], $action, $category[$i]);
			}
		}
		
		if (($prev != null && $prev->isActive()) && ($new->isExpired() || $new->isOnHold())) {

			$action = 0;

			for ($i=0;$i < count($category);$i++) {
				if (!isset($allowed_submission[$i])) {
					$allowed_submission[$i]= $allowed_submission[count($allowed_submission) - 1];
				}

				$this->helper->removeResource($subid, $userId, $category[$i], 'com_k2.submission' . $category[$i], $allowed_submission[$i]);
				$this->helper->changeItemState($userId, $allowed_submission[$i], $action, $category[$i]);
			}
		}

		return true;
	}

	public function onPayplansAccessCheck(PPUser $user)
	{
		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'default');
		$task = $this->input->get('task', '', 'default');
		
		if ($option !== 'com_k2'){
			return true;
		}
		
		if ($user->isAdmin()) {
			return true;
		}

		if ($view != 'item' || $task != 'save') {
			return true;
		}

		$this->checkAccess($user);

		return true;
	}

	/**
	 * Perform the access checking
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function checkAccess($user)
	{
		// Get k2 category that user use to create the article
		$catId = $this->input->get('catid', '', 'default');

		$applicable = $this->helper->isCategoryApplicable($catId);

		if (!$applicable) {
			return true;
		}

		$accessibleCategories = $this->helper->getAccessibleCategories($user);

		if (!$accessibleCategories && !array_key_exists($catId, $accessibleCategories)) {
			$redirect = PPR::_('index.php?option=com_payplans&view=dashboard');
			$link = $this->helper->getRedirectPlanLink();
			$msg = JText::sprintf('COM_PAYPLANS_APP_K2_CATEGORY_NOT_ALLOWED_CREATE', $link);

			return PP::redirect($redirect, $msg);
		}

		$userItems = $this->helper->getUserPosts($user->getId(), $catId);
		$record = $this->helper->getResource($user->getId(), $catId, 'com_k2.submission' . $catId);
		
		// If no record found, return because there is no data in resource table
		if (empty($record)) {
			return true;
		}
		
		$editItemId = $this->input->get('id', false);
		
		if ($editItemId && array_key_exists($editItemId, $userItems)) {
			return true;
		}

		if (count($userItems) >= $record->count) {
			$msg = JText::_('COM_PAYPLANS_APP_K2_CATEGORY_YOU_ARE_NOT_ALLOWED_TO_ADD_MORE_SUBMISSIONS');
			$redirect = PPR::_('index.php?option=com_payplans&view=dashboard');
			
			return PP::redirect($redirect, $msg);
		}
	}
}

class PPAppK2categoryFormatter extends PayplansAppFormatter
{ 
	public function getVarFormatter()
	{
		$rules = array('_appplans' => array('formatter'=> 'PayplansAppFormatter', 'function' => 'getAppPlans'),
						'app_params' => array('formatter'=> 'PPAppK2categoryFormatter', 'function' => 'getFormattedParams'));
		
		return $rules;
	}

	public function getK2Categories()
	{
		$db = PP::db();
		$query = 'SELECT `id` as category_id, `name` FROM `#__k2_categories`';
		
		$db->setQuery($query);
		
		return $db->loadObjectList('category_id');
	}
	
	public function getFormattedParams($key, $value, $data)
	{
		if (!JFolder::exists(JPATH_SITE . '/components/com_k2')) {
			return false;
		}
		
		$categories = $this->getK2Categories();
		
		if (empty($value['category'])) {
			return false;
		}

		if (is_array($value['category'])) {
			foreach($value['category'] as $param){
				$category[] = $categories[$param]->name;
			}

			$value['category'] = implode(',', $category);
		} else {
			$value['category'] = $categories[$value['category']]->name;
		}
	}
}
