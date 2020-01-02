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

class PayplansInvoiceFormatter extends PayplansFormatter
{
	public function getIgnoredata()
	{
		$ignore = array('_trigger', '_component', '_errors', '_name', '_blacklist_tokens','_transactions');
		return $ignore;
	}

	public function getVarFormatter()
	{
		$rules = array('params'	=> array('formatter' => 'PayplansFormatter', 'function' => 'getFormattedParams'),
					   'user_id' => array('formatter' => 'PayplansUserFormatter', 'function' => 'getBuyerName'),
					   'invoice_id' => array('formatter' => 'PayplansInvoiceFormatter', 'function' => 'getInvoiceLink')
					);

		return $rules;
	}

	/**
	 * Get invoice link
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getInvoiceLink($key, $value, $data)
	{
		if (!$value) {
			return;
		}

		$url = JRoute::_('index.php?option=com_payplans&view=invoice&task=edit&id='. $value, false);
		$value = '<a href="' . $url . '" target="_Blank">' . $value . '</a>';

		return $value;
	}
}