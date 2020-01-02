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

require_once(PP_LIB . '/abstract.php');

class PPCustomDetails extends PayPlans
{
	public $table = null;

	public function __construct($key = null)
	{
		$this->table = PP::table('CustomDetails');

		if (is_object($key) && ($key instanceof PPTableCustomDetails)) {
			$this->table = $key;
		}
		
		if (is_object($key) || is_array($key)) {
			$this->table->bind($key);
		}

		if (is_string($key) || is_int($key)) {
			$this->table->load($key);
		}
	}

	public static function factory($key)
	{
		return new self($key);
	}

	public function __get($key) 
	{
		if (!isset($this->$key) && isset($this->table->$key)) {
			return $this->table->$key;
		}
	}

	public function __set($key, $value) 
	{
		if (!isset($this->$key) && isset($this->table->$key)) {
			$this->table->$key = $value;
		}
	}

	/**
	 * Retrieves the params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getParams()
	{
		static $items = array();

		if (!isset($items[$this->id])) {
			$registry = new JRegistry($this->params);
			$items[$this->id] = $registry;
		}

		return $items[$this->id];
	}

	/**
	 * Retrieves the title 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Given a user object, determine if the user is applicable to the current custom details
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isApplicable(PPUser $user)
	{
		$params = $this->getParams();

		if ($this->isApplicableToAllPlans()) {
			return true;
		}

		$appPlans = $params->get('plans');

		// If for some reason there is no plan to associated with, we just return false
		if (!$appPlans) {
			return false;
		}

		// we need to get both active and expired.
		$plans = $user->getPlans(array(PP_SUBSCRIPTION_ACTIVE, PP_SUBSCRIPTION_EXPIRED));

		if (!$plans) {
			return false;
		}

		$planIds = [];
		foreach ($plans as $plan) {
			$planIds[] = $plan->getId();
		}

		$appPlans = $params->get('plans');

		if (array_intersect($planIds, $appPlans)) {
			return true;
		}

		return false;
	}

	/**
	 * This is different than the normal @isApplicable as this will only fetch custom details 
	 * that should be rendered based on plans
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isPlanApplicable(PPPlan $plan)
	{
		$params = $this->getParams();

		if ($this->isApplicableToAllPlans()) {
			return true;
		}

		$plans = $params->get('plans');

		if (!$plans) {
			return false;
		}

		if (in_array($plan->getId(), $plans)) {
			return true;
		}

		return false;

	}

	/**
	 * Given a user object, determine if the user is applicable to the current custom details
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isApplicableToAllPlans()
	{
		$params = $this->getParams();

		$applyToAll = (bool) $params->get('applyAll', false);

		return $applyToAll;
	}

	/**
	 * Retrieve fields from this custom details item
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getFieldsOutput($userParams)
	{
		// / process the xml tu get the field name and label
		$xml = @simplexml_load_string(base64_decode($this->data));

		if (!$xml) {
			return array();
		}

		$fields = $xml->fields;
		$items = array();

		foreach ($fields->fieldset as $fieldset) {
			foreach ($fieldset->children() as $child) {
				$item = new stdClass();
				$name = (string) $child->attributes()['name'];

				$item->name = $name;
				$item->label = (string) $child->attributes()['label'];
				$item->value = $userParams->get($name);

				if (isset($child->option)) {
					$value = $userParams->get($name);

					$options = (array) $child->option;
					$optionsValue = array();

					foreach ($child->option as $childOption) {
						$optionsValue[(string) $childOption['value']] = (string) $childOption;
					}

					if (is_array($value)) {
						$tmpVal = array();

						foreach ($value as $val) {
							if (isset($optionsValue[$val])) {
								$tmpVal[] = $optionsValue[$val];
							}
						}

						$value = implode(', ', $tmpVal);
					} else {
						$value = isset($optionsValue[$userParams->get($name)]) ? $optionsValue[$userParams->get($name)] : '';
					}

					$item->value = $value;
				}

				$items[] = $item;
			}
		}

		return $items;
	}

	/**
	 * Renders the form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function renderForm($params, $site = false, $type = 'userparams')
	{
		$xml = @simplexml_load_string(base64_decode($this->data));

		if (!$xml) {
			return false;
		}

		$form = PP::form('customdetails');
		$renderer = $form->getRenderer($xml, $params);

		$output = $renderer->render($site, $this->title, $type);
		return $output;
	}
}