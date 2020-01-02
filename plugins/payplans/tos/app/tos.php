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

class PPAppTos extends PPApp
{
	public function isApplicable($view = null, $eventName = '')
	{
		if (!($view instanceof PayPlansViewCheckout)) {
			return false;
		}
	
		$id = PP::getIdFromKey($this->input->get('invoice_key'));

		if (!$id) {
			return false;
		}

		$invoice = PP::invoice($id);

		return parent::isApplicable($invoice, $eventName);
	}

	/**
	 * Formats the data before it gets stored
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function collectAppParams($data)
	{
		// encode editor content
		if (isset($data['app_params']) && isset($data['app_params']['custom_content'])) {
			$data['app_params']['custom_content'] = base64_encode($data['app_params']['custom_content']);
		}

		return parent::collectAppParams($data);
	}
}

