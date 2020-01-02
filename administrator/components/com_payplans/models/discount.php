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

PP::import('admin:/includes/model');

class PayPlansModelDiscount extends PayPlansModel
{
	public $filterMatchOpeartor = array(
										'published'	    => array('='),
										'coupon_amount' => array('>=', '<='),
										'title'			=> array('LIKE'),
										'coupon_code'	=> array('LIKE'),
										'start_date' 	=> array('>=', '<='),
										'end_date'		=> array('>=', '<=')
										);

	public function __construct()
	{
		parent::__construct('discount');
	}

	/**
	 * Initialize default states
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function initStates()
	{
		parent::initStates();

		$ordering = $this->getUserStateFromRequest('ordering', 'prodiscount_id');

		$this->setState('ordering', $ordering);
	}

	/**
	 * Determines if the user used a specific coupon code before across any invoices
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function hasUsed($couponCode, $userId, $couponType)
	{
		$db = PP::db();
		$query = array();
		$query[] = 'SELECT COUNT(1) FROM ' . $db->qn('#__payplans_modifier');
		$query[] = 'WHERE ' . $db->qn('user_id') . '=' . $db->Quote($userId);
		$query[] = 'AND ' . $db->qn('reference') . '=' . $db->Quote($couponCode);
		$query[] = 'AND ' . $db->qn('type') . '=' . $db->Quote($couponType);

		$db->setQuery($query);
		$total = (int) $db->loadResult();

		$used = $total > 0;

		return $used;
	}

	/**
	 * Get list of discouts that associated with plans.
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getDiscountPlans()
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_prodiscount');
		$query[] = 'WHERE ' . $db->qn('plans') . '!= "" AND ' . $db->qn('plans')  . ' !=' . $db->Quote('[]');

		$db->setQuery($query);
		$items = $db->loadObjectList();

		return $items;
	}

	/**
	 * Retrieves list of discounts with active states
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getItems()
	{
		$db = $this->db;

		$query = array();

		$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_prodiscount');

		$wheres = array();

		$published = $this->getState('published');

		if ($published !== '' && $published !== -1 && $published !== 'all') {
			$wheres[] = $db->qn('published') . " = " . $db->Quote((int) $published);
		}

		$search = $this->getState('search');

		if ($search !== '') {
			$search = JString::trim($search);

			$searchQuery = array();

			// Search by username, email or name
			$searchQuery[] = 'LOWER(' . $db->qn('title') . ') LIKE ' . $db->Quote('%' . JString::strtolower($search) . '%');
			$searchQuery[] = 'LOWER(' . $db->qn('coupon_code') . ') LIKE ' . $db->Quote('%' . JString::strtolower($search) . '%');

			$wheres[] = '(' . implode(' OR ', $searchQuery) . ')';
		}

		$where = '';

		if (count($wheres) > 0) {
			$where = ' where ';
			$where .= (count($wheres) == 1) ? $wheres[0] : implode(' and ', $wheres);
		}

		$query = implode(' ', $query);
		$query .= $where;

		$ordering = $this->getState('ordering');
		$direction = $this->getState('direction');

		if ($ordering) {
			$query .= " ORDER BY " . $ordering . " " . $direction;
		}

		$this->setTotal($query, true);
		$result	= $this->getData($query);

		return $result;
	}

	/**
	 * update plans associated with discount.
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function updateDiscountPlans($did, $plans)
	{
		$db = $this->db;

		$query = "update `#__payplans_prodiscount` set `plans` = " . $db->Quote($plans);
		$query .= " where `prodiscount_id` = " . $db->Quote($did);

		$db->setQuery($query);
		$state = $db->query();

		return $state;
	}

	/**
	 * Given a list of modifiers, find discounts that cannot be combined
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getNonCombinableDiscounts($modifiers)
	{
		$db = $this->db;
		$filters = array();
		$discounts = array();

		foreach ($modifiers as $key=>$modifier) {
			$modifierType = $modifier->getType();

			//step 1: first make conditions for filter acording to the discount type
			if ($modifierType == PP_PRODISCOUNT_AUTOONUPGRADE || $modifierType == PP_PRODISCOUNT_AUTOONRENEWAL || $modifierType == PP_PRODISCOUNT_AUTO_ON_INVOICE_CREATION) {
				$filters['prodiscount_id'][] = substr(strchr($modifier->getReference(),'_'),1);
			} else {
				$filters['coupon_code'][] = $modifier->getReference();
			}
		}

		$discounts = array();
		$discountsOne = array();
		$discountsTwo = array();

		//step 2: collect all records as per the applied filter
		if (isset($filters['prodiscount_id'])) {
			$query = "select * from `#__payplans_prodiscount`";
			$query .= " where `prodiscount_id` IN (" . implode(',', $filters['prodiscount_id']) . ")";

			$db->setQuery($query);
			$discountsOne = $db->loadObjectList();
		}

		if (isset($filters['coupon_code'])) {

			$tmp = '';
			foreach ($filters['coupon_code'] as $code) {
				$tmp .= ($tmp) ? ',' . $db->Quote($code) : $db->Quote($code);
			}

			$query = "select * from `#__payplans_prodiscount`";
			$query .= " where `coupon_code` IN (" . $tmp . ")";

			$db->setQuery($query);
			$discountsTwo = $db->loadObjectList();
		}

		$discounts = array_merge($discountsOne, $discountsTwo);

		$items = array();

		//step 3: unset all the modifiers which has discounts that allows clubbing with other discounts
		foreach ($discounts as $discount){
			$discountInstance = PP::Discount($discount);

			if (! $discountInstance->isCombinable()) {
				$id = $discountInstance->getId();
				$items[$id] = $discount;
			}
		}

		return $items;
	}

	/**
	 * Deletes discounts related to a plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function deletePlanDiscounts($planId)
	{
		$db = PP::db();
		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_prodiscount');
		$query[] = 'WHERE ' . $db->qn('plans') . '!=' . $db->Quote('');

		$db->setQuery($query);
		$result = $db->loadObjectList();

		// Nothing to be deleted
		if (!$result) {
			return true;
		}

		foreach ($result as $row) {
			$plans = $row->plans;
			$plans = explode(",", $plans);

			// Do nothing if plan is not mapped with discount
			if (!in_array($planId, $plans)) {
				continue;
			}

			foreach ($plans as $key => $data) {
				if ($data == $planId) {
					unset($plans[$key]);
				}
			}

			$updatedPlans = implode(",", $plans);
			$query = array();
			$query[] = 'UPDATE ' . $db->qn('#__payplans_prodiscount') . ' SET ' . $db->qn('plans') . '=' . $db->Quote($updatedPlans);
			$query[] = 'WHERE ' . $db->qn('prodiscount_id') . '=' . $db->Quote($row->prodiscount_id);

			$db->setQuery($query);
			$db->Query();
		}

		return true;
	}

	/**
	 * Generates coupon codes on the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function generate($prefix, $total, $data)
	{
		$db = PP::db();

		$query = $db->getQuery(true);
		$query->insert($db->quoteName('#__payplans_prodiscount'));
		$query->columns(array(
								$db->quoteName('title'),
								$db->quoteName('coupon_code'),
								$db->quoteName('coupon_type'),
								$db->quoteName('core_discount'),
								$db->quoteName('coupon_amount'),
								$db->quoteName('plans'),
								$db->quoteName('start_date'),
								$db->quoteName('end_date'),
								$db->quoteName('published'),
								$db->quoteName('params')
						));



		// Standard values
		$plans = '';

		if (!empty($data['plans'])) {
			$plans = implode(',', $data['plans']);
		}

		$generatedCodes = array();
		$coreDiscount = isset($data['core_discount']) ? $db->Quote($data['core_discount']) : '';
		$couponAmount = isset($data['coupon_amount']) ? $db->Quote($data['coupon_amount']) : '';
		$couponType = $db->Quote($data['coupon_type']);
		$startDate = $db->Quote($data['start_date']);
		$endDate = $db->Quote($data['end_date']);

		// Use to return to the caller
		$rows = array();

		if (!$coreDiscount && !$plans) {
			$coreDiscount = $db->Quote(1);
		}

		$plans = $db->Quote($plans);

		$i = 1;

		while ($i <= $total) {
			$randomize = time() * rand();

			// There are chances that the number can become floating point, eg : 9.8044614581955E+35
			// Hence we need to properly format the number
			$integer = number_format($randomize, 0, '.', '');

			$code = $prefix . substr($integer, 0, PP_DISCOUNTS_CODE_SIZE);

			// Ensure that there are no duplicates
			if (in_array($code, $generatedCodes)) {
				$total++;
				continue;
			}

			$generatedCodes[] = $code;
			$rows[] = array($code);

			$quotedCode = $db->Quote($code);
			$params = $db->Quote(stripcslashes(json_encode($data['params'])));

			$query->values($quotedCode . ', ' . $quotedCode . ', ' . $couponType . ', ' . $coreDiscount . ',' . $couponAmount . ', ' . $plans . ', ' . $startDate . ', ' . $endDate. ', 1,'. $params);

			$i++;
		}

		$db->setQuery($query);
		$state = $db->execute();

		if ($state) {
			return $rows;
		}

		return false;
	}

	/**
	 * Retrieves a list of coupon codes
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCouponCodes()
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_prodiscount');
		$query[] = 'WHERE ' . $db->qn('published') . '=' . $db->Quote(1);
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Retrieves the list of available coupon types
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCouponTypes()
	{
		$types = array(
			'firstinvoice' => '',
			'eachrecurring' => '',
			'autodiscount_onrenewal' => '',
			'autodiscount_onupgrade' => '',
			'autodiscount_oninvoicecreation' => '',
			'discount_for_time_extend' => '',
			'referral' => ''
		);

		foreach ($types as $key => &$value) {
			$value = JText::_('COM_PP_DISCOUNTS_TYPE_' . strtoupper($key));
		}

		return $types;
	}
}

class PayplansModelFormDiscount extends PayPlansModelform {}
