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

class PayplansModelModifier extends PayPlansModel
{
	public function __construct()
	{
		parent::__construct('modifier');
	}

	/**
	 * To delete modifiers records based on id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function deleteModifiers($ids)
	{
		$db = $this->db;

		$str = '';
		foreach ($ids as $id) {
			$str .= ($str) ? ',' . $db->Quote($id) : $db->Quote($id);
		}

		$query = "delete from `#__payplans_modifier` where `modifier_id` IN (" . $str . ")";
		$db->setQuery($query);
		$state = $db->query();

		return $state;
	}

	/**
	 * To delete modifiers records based on invoice and modifier type
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function deleteTypeModifiers($invoiceId, $type)
	{
		$db = $this->db;

		$query = "delete from `#__payplans_modifier`";
		$query .= " where `invoice_id` = " . $db->Quote($invoiceId);
		$query .= " and `type` = " . $db->Quote($type);

		$db->setQuery($query);
		$state = $db->query();

		return $state;
	}
}

