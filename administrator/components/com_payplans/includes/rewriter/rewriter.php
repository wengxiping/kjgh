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

class PPRewriter extends PayPlans
{
	public $mapping = array();

	/**
	 * Sets configuration mapping
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function mapConfig()
	{
		static $mapping = false;

		if ($mapping) {
			return true;
		}

		// initialize it with some hard coded tokens
		$jconfig = PP::jconfig();
		
		$config = new stdClass();
		$config->site_name = rtrim($jconfig->get('sitename'), '/');
		$config->company_name = $this->config->get('companyName');
		$config->company_address = nl2br($this->config->get('companyAddress'));
		$config->company_city_country = $this->config->get('companyCityCountry');
		$config->company_phone = $this->config->get('companyPhone');
		$config->site_url = rtrim(JURI::root(), '/');
		$config->name = 'config';
		$config->plan_renew_url = '';
		$config->dashboard_url = '';
		$config->order_details_url = '';

		$this->setMapping($config, false);
		$mapping = true;

		return true;
	}

	/**
	 * Replaces tokens with proper values
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function rewrite($string, $object, $retrieveRelatedObjects = true)
	{
		// Initialize the configuration mapping
		$this->mapConfig();

		// Initialize object's mapping
		$this->setMapping($object, $retrieveRelatedObjects);

		//trigger apps for mapping rewriter tokens
		$args = array(&$object, $this);
		PP::event()->trigger('onPayplansRewriterReplaceTokens', $args);

		$showBlankToken = $this->config->get('show_blank_token', false);

		foreach ($this->mapping as $key => $value) {
			
			if (!$showBlankToken && !is_array($value)) {
				$string = preg_replace('/\[\['.$key.'\]\]/', $value, $string);
				continue;
			}

			if ($showBlankToken && isset($value) && ($value != null || $value != '')) {
				$string = preg_replace('/\[\['.$key.'\]\]/', $value, $string);
				continue;
			}
		}

		return $string;
	}

	/**
	 * Initializes mapping for the object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setMapping($object, $retrieveRelatedObjects = true)
	{
		$objects = array($object);

		if ($retrieveRelatedObjects) {
			$objects = $this->getRelatedObjects($object);
		}

		if (!$objects) {
			return $this;
		}

		foreach ($objects as $object) {
			
			if (!$object) {
				continue;
			}

			$name = $object->name;

			if (method_exists($object, 'getName')) {
				$name = $object->getName();
			}

			// For objects that has a custom key to be used
			if (method_exists($object, 'getRewriterKey')) {
				$name = $object->getRewriterKey();
			}

			$properties = array();

			// New implementation in 4.0 to get tokens
			if (method_exists($object, 'getRewriterTokens')) {
				$properties = $object->getRewriterTokens();

				if ($properties === false) {
					continue;
				}
			}

			if (!$properties) {
				$properties = (method_exists($object, 'toArray')) ? $object->toArray(true, true) : (array) $object;

				if (isset($object->_blacklist_tokens)) {
					foreach ($object->_blacklist_tokens as $token) {
						unset($properties[$token]);
					}
				}
			}

			$map = array();
			foreach ($properties as $key => $value) {
				
				// if key name starts with _ then continue
				$key = JString::trim($key);

				if (JString::substr($key, 0, 1) == '_') {
					continue;
				}

				// JParameter will be an array, so handle it
				if (is_array($value)) {
					
					foreach ($value as $childKey => $childValue) {
						$index = JString::strtoupper($name . '_' . $key . '_' . $childKey);
						$map[$index] = $childValue;
					}

					continue;
				}

				if (strtolower($key) == 'status') {
					$value = JText::_('COM_PAYPLANS_STATUS_' . PP::string()->getStatusName($value));
				}

				if (($object instanceOf PPMaskableInterface) && strtolower($key)== 'currency' && method_exists($object, 'getCurrency')) {
					$value = $object->getCurrency();
				}

				if (stristr($key, 'date') && ($value == null || $value == '0000-00-00 00:00:00')) {
					$value = JText::_('COM_PAYPLANS_NEVER');
				}

				if (in_array($key, array('subtotal','total','amount'))) {
					$value = PPFormats::price($value);
				}

				$index = JString::strtoupper($name . '_' . $key);
				$map[$index] = $value;
			}

			// XITODO : clean this code, move the below code from forloop
			$this->mapping = array_merge($this->mapping, $map);

			// add key of PPMaskableInterface object
			if ($object instanceof PPMaskableInterface) {
				$index = JString::strtoupper($object->getName()) . '_KEY';
				$this->mapping[$index] = $object->getKey();
			}

			if ($name == 'invoice') {
				$this->mapping['INVOICE_INVOICE_SCREEN_LINK'] = JURI::root()."index.php?option=com_payplans&view=invoice&task=confirm&invoice_key=" . PP::getKeyFromId($this->mapping['INVOICE_INVOICE_ID']);
			}

			//Assign subscription Renew Link.
			if (isset($this->mapping['SUBSCRIPTION_SUBSCRIPTION_ID'])){
				$this->mapping['CONFIG_PLAN_RENEW_URL'] = JURI::root()."index.php?option=com_payplans&view=order&layout=processRenew&subscription_key=" . PP::getKeyFromId($this->mapping['SUBSCRIPTION_SUBSCRIPTION_ID'])." &tmpl=component";
			}

			//token rewriter for dashboard and order details page
			if ($name == 'config') {
				$this->mapping['CONFIG_DASHBOARD_URL'] = JURI::root() . "index.php?option=com_payplans&view=dashboard";
				$this->mapping['CONFIG_ORDER_DETAILS_URL'] = JURI::root() . "index.php?option=com_payplans&view=order";
			}

			//token rewriter for users wallet balance
			if ($object instanceof PPUser) {
				$user = PP::user($this->mapping['USER_USER_ID']);

				// Defaulttoken for User Prefrences app
				$preferences = $user->getPreferences();
				$preferences = $preferences->toArray();

				if (empty($preferences)) {
					$this->mapping['USER_PREFERENCE_BUSINESS_PURPOSE'] = '';
					$this->mapping['USER_PREFERENCE_BUSINESS_NAME'] = '';
					$this->mapping['USER_PREFERENCE_TIN'] = '';
					$this->mapping['USER_PREFERENCE_SHIPPING_ADDRESS'] = '';
					$this->mapping['USER_PREFERENCE_BUSINESS_ADDRESS'] = '';
					$this->mapping['USER_PREFERENCE_BUSINESS_CITY'] = '';
					$this->mapping['USER_PREFERENCE_BUSINESS_STATE'] = '';
					$this->mapping['USER_PREFERENCE_BUSINESS_ZIP'] = '';
				}

				$purpose = isset($preferences['businsess_purpose']) ? $preferences['businsess_purpose'] : false;
				if ($purpose == 2) {
					$purpose = 'business';
				}

				if ($purpose == 1) {
					$purpose = 'personal';
				}

				if ($purpose) {
					$this->mapping['USER_PREFERENCE_BUSINESS_PURPOSE'] = $purpose;
				}

				$countryCode  = $user->getCountry();
				$items = PP::model('country')->loadRecords(array('id' => $countryCode));

				$this->mapping['USER_COUNTRY'] = '';
				
				if (!empty($items)) {
					$this->mapping['USER_COUNTRY'] = PPFormats::country(array_shift($items));
				}
			}
		}

		return $this;
	}

	/**
	 * Given an object, try to figure out the order item
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getOrder($object)
	{
		$order = false;

		// Added compitibility for pp 3.x version, need to remove later
		if ($object instanceof PPOrder || $object instanceof PayplansOrder) {
			$order = $object;
		}

		if ($object instanceof PPPayment) {
			$invoice = $object->getInvoice();

			if (!$invoice) {
				return array();
			}

			$order = $invoice->getReferenceObject();
		}

		if ($object instanceof PPInvoice) {
			$order = $object->getReferenceObject();
			$latestInvoice = $object;
		}

		// If all else fail, try to get the order
		if (!$order) {
			$order = $object->getOrder();
		}

		return $order;
	}

	/**
	 * Given an object, try to figure out the order item
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLatestInvoice($object)
	{
		if (!$object instanceof PPInvoice) {
				return false;
		}

		return $object;
	}

	/**
	 * Retrieve related objects given the provided object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRelatedObjects($object)
	{
		$obj = array();
		$latestInvoice = '';

		if (!$object || !isset($object)) {
			return array();
		}

		$order = $this->getOrder($object);
		$latestInvoice = $this->getLatestInvoice($object);

		$obj[] = $order->getSubscription(true);
		$obj[] = $order->getPlan();
		$obj[] = $order->getBuyer();

		if (!$latestInvoice) {
			$invoices = $order->getInvoices();

			if (!$invoices) {
				return $obj;
			}

			$latestInvoice = array_pop($invoices);
		}

		$obj[] = $latestInvoice;
		
		$payment = $latestInvoice->getPayment();

		if ($payment instanceof PPPayment) {
			$obj[] = $payment;
		} else {
			$obj[] = PP::payment();
		}

		$transactions = $latestInvoice->getTransactions();
		
		if (!empty($transactions) && (array_pop($transactions) instanceof PPTransaction)) {
			$transactions = $latestInvoice->getTransactions();
			$transaction = array_pop($transactions);
			
			$obj[] = $transaction;
		} else {
			$obj[] = PP::transaction();
		}
		 
		$obj[] = $order;

		return $obj;
	}
}