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

class PPHelperMenuAccess extends PPHelperStandardApp
{
	/**
	 * Retrieve a list of allowed menus from the params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAllowedMenus()
	{
		$menus = $this->params->get('allowedMenus', array());

		if ($menus && !is_array($menus)) {
			$menus = array($menus);
		}

		return $menus;
	}

	/**
	 * Retrieve a list of allowed menus from the params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPlans($applyAll)
	{
		$params = $this->getCoreParams();
		$all = $this->params->get('applyAll', 0);

		if ($all) {
			$plans = PPHelperPlan::getPlans(array('published' => 1), false);

			return $plans;
		}

		$appPlans = ($applyAll == 1) ? PayplansHelperPlan::getPlans(array('published' => 1), false) : $app->getPlans();

		return $appPlans;
	}

	/**
	 * For Zoo, we need to process the current requested url differently
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function zoo($current)
	{
		$option = PP::normalize($current, 'option', '');
		$id = PP::normalize($current, 'id', '');

		if ($id || $option != 'com_zoo') {
			return $current;
		}

		$itemId = PP::normalize($current, 'Itemid', '');

		$db = PP::db();
		$query = array();
		$query[] = 'SELECT ' . $db->qn('params') . ' FROM ' . $db->qn('#__menu');
		$query[] = 'WHERE ' . $db->qn('id') . '=' . $db->Quote($itemId);
		
		$query = implode(' ', $query);
		$db->setQuery($query);

		$result = $db->loadObjectList();

		if (!$result) {
			return $current;
		}

		foreach ($result as $key => $value) {
			$params = json_decode($value->params, true);
			$id = $params['item_id'];
			$current['id'] = $id;
		}

		return $current;
	}
}
