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

class PPHelperKunena extends PPHelperStandardApp
{
	/**
	 * Retrieve a list of categories set in the params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCategories()
	{
		static $categories = null;

		if (is_null($categories)) {
			$categories = $this->params->get('kunenaCategories', '');
			
			if (!is_array($categories) && $categories) {
				$categories = array($categories);
			}
		}

		return $categories;
	}

	/**
	 * Update permission for kunena categories
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function updateKunenaSubscriptions(PPUser $user, $categories)
	{
		$db = PP::db();

		if (!$categories) {
			return false;
		}

		foreach ($categories as $categoryId) {
			$query = array();
			$query[] = 'UPDATE ' . $db->qn('#__kunena_user_categories') . ' SET ' . $db->qn('subscribed') . '=' . $db->Quote(0);
			$query[] = 'WHERE ' . $db->qn('user_id') . '=' . $db->Quote($user->getId());
			$query[] = 'AND ' . $db->qn('category_id') . '=' . $db->Quote($categoryId);

			$query = implode(' ', $query);
			$db->setQuery($query);
			$db->Query();
		}
	}
}