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

class PPHelperEasyBlogSubmission extends PPHelperStandardApp
{
	private $resource = 'com_easyblog.submission';

	/**
	 * Check to see if the user is allowed and hasn't used up their limits
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isAllowed(PPUser $user)
	{
		$model = PP::model('Resource');

		$category = $model->loadRecords(array(
			'user_id' => $user->id,
			'title' => $this->resource,
			'value' => 0
		));

		if (!$category) {
			$apps = $this->getAvailableApps('easyblogsubmission');

			foreach ($apps as $app) {
				$addentryin = $app->getAppParam('add_entry_in');

				if ($addentryin == 'any_category'){
					return false;
				}
			}

			return true;
		}
		
		$res = array_shift($category);
		$res = $res->count;

		if ($res != 0) {
			return true;
		}

		return false;
	}


	/**
	 * Retrieves the restriction type
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRestrictionType()
	{
		return $this->params->get('add_entry_in');
	}

	/**
	 * Retrieves the restriction type
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTotalSubmission()
	{
		return (int) $this->params->get('no_of_submisssion');
	}


	/**
	 * Retrieves the restriction categories
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRestrictionCategories()
	{
		$categories = $this->params->get('add_entry_in_category'); 

		if (!is_array($categories) && $categories) { 
			$categories = array($categories); 
		}

		return $categories;
	}

	/**
	 * Decrease the counter
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function decrease($categoryId, PPUser $user)
	{
		$db = PP::db();
		$query = array();
		$query[] = 'SELECT `count` FROM ' . $db->qn('#__payplans_resource');
		$query[] = 'WHERE ' . $db->qn('value') . '=' . $db->Quote($categoryId);
		$query[] = 'AND ' . $db->qn('title') . '=' . $db->Quote($this->resource);
		$query[] = 'AND ' . $db->qn('user_id') .'=' . $db->Quote($user->id);

		$query = implode(' ', $query);
		$db->setQuery($query);
		
		$count = (int) $db->loadResult();
		$count = $count != 0 ? $count - 1 : 0;

		$query = array();
		$query[] = 'UPDATE ' . $db->qn('#__payplans_resource') . ' SET `count` = ' . $db->Quote($count);
		$query[] = 'WHERE ' . $db->qn('value') . '=' . $db->Quote($categoryId);
		$query[] = 'AND ' . $db->qn('title') . '=' . $db->Quote($this->resource);
		$query[] = 'AND ' . $db->qn('user_id') . '=' . $db->Quote($user->id);

		$query = implode(' ', $query);
		$db->setQuery($query);
		$db->Query();

		return true;
	}

	/**
	 * Decrease the resource counter
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function decreaseAll($categoryId, $user)
	{
		$this->decrease($categoryId, $user);

		$parent = $this->getParent($categoryId);

		if ($parent == 0) {
			return true;
		}

		// Decrease parent
		$this->decreaseAll($parent, $user);
	}
	
	/**
	 * Retrieves a parent category
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getParent($categoryId)
	{
		$category = EB::table('Category');
		$category->load($categoryId);

		return $category->parent_id;
	}
	
	/**
	 * Checks if the user exceeded the limit for their postings in a category
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exceededLimit($categoryId, PPUser $user)
	{
		$model = PP::model('Resource');
		$total = $model->loadRecords(array(
					'user_id' => $user->id,
					'title' => $this->resource,
					'value' => $categoryId
				));

		// If no app is applicable then no records in resource table.
		// but the app can be applied on parent may be
		if (!$total) {
			$solution = $this->isAppExistOnCategory($categoryId); 

			if ($solution) {
				return true;
			}

			$parent = $this->getParent($categoryId);

			if ($parent == 0) {
				return false;
			}

			return $this->exceededLimit($parent, $user); 
		}		
		
		$total = array_shift($total);

		//if not allowed then it's resource counts are 0.
		if ($total->count == 0) {
			return true;
		}
	
		$parent = $this->getParent($categoryId);
		
		// If reach to top category then allowed
		if ($parent == 0) {	
			return false;
		}
		
		$res = $this->exceededLimit($parent, $user);
		return $res;
				
	}

	/**
	 * Determines if another app exists for the other category
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function isAppExistOnCategory($category)
	{
		$apps = $this->getAvailableApps('easyblogsubmission');

		foreach ($apps as $app) {
			$restrictionType = $app->helper->getRestrictionType();
			
			if ($restrictionType != 'any_category') {
				$categories = $app->helper->getRestrictionCategories();

				if ($categories) {
					foreach ($categories as $categoryId) { 
						if ($categoryId == $category) { 
							return true;
						}
					}
				}
			} 
		} 

		return false;
	}

	/**
	 * Check Allowed Category
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	function isAllowedInCategory($categoryId, $userId)
	{
		$model = PP::model('Resource');
		$total = $model->loadRecords(array(
					'user_id' => $userId,
					'title' => 'com_easyblog.submission',
					'value' => $categoryId
				));


		//if no app is applicable then no records in resource table, but the app can be applied on parent may be
		if (!$total) {
			 
			$app = $this->isAppExistOnCategory($categoryId); 
			if ($app) {
				return false;
			}

			$parent = $this->getParent($categoryId);
			if ($parent == 0) {
				return true;
			}

			return $this->isAllowedInCategory($parent, $userId); 
		}	

		$totalAllowed = array_shift($total);
		
		//if not allowed then it's resource counts are 0.
		if ($totalAllowed->count == 0) {
			return false;
		}
	
		$parent = $this->getParent($categoryId);
		
		//if reach to top category then allowed
		if ($parent == 0) {	
			return true;
		}
		
		$res = $this->isAllowedInCategory($parent, $userId);
		return $res;
				
	}
}