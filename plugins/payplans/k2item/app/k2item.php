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

class PPAppK2item extends PPApp
{
	public function isApplicable($refObject=null, $eventName='')
	{
		if (!$this->helper->exists()) {
			return false;
		}
		
		if ($eventName == 'onPayplansAccessCheck' || $eventName == 'onK2PrepareContent') {
			return true;
		}

		return parent::isApplicable($refObject, $eventName);
	}
	
	public function onPayplansAccessCheck(PPUser $user)
	{
		if ($user->isAdmin()) {
			return false;
		}
		
		$option = $this->input->get('option', '', 'default');
		
		if ($option != 'com_k2') {
			return false;
		}

		$view = $this->input->get('view', '', 'default');
		$task = $this->input->get('task', '', 'default');
		$layout = $this->input->get('layout', '', 'default');
		$id = $this->input->get('id', '', 'string');

		$cat_id = strtok($id, ':');

		if ($view == 'itemlist' && ($layout == 'category' || $task == 'category')) {
			//it should also restricted for parent categories also
			$categories = $this->helper->getChildCategoriesAndSelf($cat_id);

			$apprestriction = $this->getAppParam('restricted_category');
			
			if (empty($apprestriction)) {
				return true;
			}

			$apprestriction = (is_array($apprestriction))? $apprestriction : array($apprestriction);
			
			if (array_intersect($categories, $apprestriction)) {

				$applyAll = $this->getParam('applyAll', false);

				if ($applyAll == false) {

					$plans = $user->getPlans();
					$plans = PP::getIds($plans);

					if ($plans) {
						$ret = array_intersect($this->getPlans(), $plans);

						if (count($ret) > 0 ) {
							return true;
						}
					}

					$msg = JText::_('COM_PAYPLANS_APP_K2_YOU_ARE_NOT_ALLOWED_TO_VIEW_CATEGORY');
					$redirect = PPR::_('index.php?option=com_payplans&view=dashboard');

					return PP::redirect($redirect, $msg);
				}

				if (count($user->getSubscriptions()) == 0) {
					$msg = JText::_('COM_PAYPLANS_APP_K2_YOU_ARE_NOT_ALLOWED_TO_VIEW_CATEGORY');
					$redirect = PPR::_('index.php?option=com_payplans&view=dashboard');

					return PP::redirect($redirect, $msg);
				}
			}
		}
		return true;
		
	}

	public function onK2PrepareContent($item, $params, $limitstart)
	{
		$view = $this->input->get('view', '', 'default');
		$task = $this->input->get('task', '', 'default');

		$appType = $this->getAppParam('restrictionOn', '');
		$allowed = true;

		if ($appType == 'restricted_item') {

			// skip this if there do not have any K2 item id return
			if (!$item->id) {
				return true;
			}

			$accessibleItems = $this->processItem($item, $params, $limitstart);
			$id = $item->id;

			// Only applied in single item view for now
			if ($accessibleItems && !array_key_exists($id, $accessibleItems) && $view != 'itemlist') {
				$allowed = false;
			}

		} else {
			$accessibleItems = $this->processCategory($item, $params, $limitstart);
			$id = isset($item->catid) ? $item->catid : $item->id;

			// Check for single item view and single category view
			if (!$accessibleItems || (!array_key_exists($id, $accessibleItems))) {
				if ($view != 'itemlist' || ($view == 'itemlist' && $task == 'category')) {
					$allowed = false;
				}
			}
		}

		// Tell the user about the restriction
		if (!$accessibleItems || !$allowed) {
			$msg = JText::_('COM_PAYPLANS_APP_K2_YOU_ARE_NOT_ALLOWED_TO_VIEW_ITEM');
			$url = PPR::_('index.php?option=com_payplans&view=dashboard');

			return PP::redirect($url, $msg);
		}

		return true;
	}

	/**
	 * Process article restriction
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function processItem($item, $params, $limitstart)
	{
		$user = PP::user();
		$accessibleItems = $this->helper->getAccessibleItems($user);

		return $accessibleItems;
	}

	/**
	 * Process category restriction
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function processCategory($item, $params, $limitstart)
	{
		$id = isset($item->catid) ? $item->catid : $item->id;

		$user = PP::user();
		$accessibleCategories = $this->helper->getAccessibleCategories($user);

		return $accessibleCategories;
	}
}

class PayplansAppK2itemFormatter extends PayplansAppFormatter
{
	//get Rules 
	public function getVarFormatter()
	{
		$rules = array('_appplans' => array('formatter'=> 'PayplansAppFormatter',
											'function' => 'getAppPlans'),
						'app_params' => array('formatter'=> 'PayplansAppK2itemFormatter',
												'function' => 'getFormattedParams'));
		return $rules;
	}

	public function getK2Items()
	{
		$db = PP::db();
		$query = 'SELECT `id` as item_id, `title` FROM ' . $db->qn('#__k2_items') . ' where ' . $db->qn('trash') . '=' . $db->Quote(0);
		$db->setQuery($query);
		
		return $db->loadObjectList('item_id');
	}
	
	public function getFormattedParams($key,$value,$data)
	{
		// Do nothing if component is not installed
		if (!JFolder::exists(JPATH_SITE . '/components/com_k2')){
			return false;
		}

		$items  = $this->getK2Items();

		if (empty($value['k2item'])) {
			return false;
		}

		if (is_array($value['k2item'])) {
			foreach ($value['k2item'] as $param) {
				$item[] = $items[$param]->title;
			}

			$value['k2item'] = implode(', ', $item);
		} else {
			$value['k2item'] = $items[$value['k2item']]->title;
		}
	}
}
