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

class PPAddon extends PPAbstract
{
	public static function factory($id)
	{
		return new self($id);
	}

	/**
	 * not for table fields
	 * @since	4.0
	 * @access	public
	 */
	public function reset($config = array())
	{
		$this->table->planaddons_id = 0;
		$this->table->title = null;
		$this->table->description = null;
		$this->table->price = 0.0000;
		$this->table->consumed = 0;
		$this->table->addons_condition = null;
		$this->table->price_type = 0;
		$this->table->apply_on = 1;
		$this->table->plans = '';
		$this->table->start_date = '0000-00-00 00:00:00';
		$this->table->end_date = '0000-00-00 00:00:00';
		$this->table->published = 1;
		$this->table->params = PP::Registry();

		return $this;
	}

	public function getConditionRules()
	{
		$conditions = array(PP_PLANADDONS_ONETIME => JText::_('COM_PP_ADDONS_CONDITION_ONETIME'), PP_PLANADDONS_EACHRECURRING => JText::_('COM_PP_ADDONS_CONDITION_EACHRECURRING'));

		return $conditions;
	}

	/**
	 * @since	4.0
	 * @access	public
	 */
	public function getTitle($includePrice = false, $invoice = null)
	{
		$title = $this->table->title;

		if ($includePrice) {

			$currency = PP::config()->get('currency');
			$amount = PP::themes()->html('html.amount', $this->table->price, $currency);

			if ($invoice) {
				$amount = PP::themes()->html('html.amount', $this->table->price, $invoice->getCurrency());
			}

			$title = JText::sprintf('COM_PP_PLAN_ADDON_TITLE_WITH_PRICE', $this->table->title, $amount);
		}

		return $title;
	}

	/**
	 * @since	4.0
	 * @access	public
	 */
	public function getDescriptions()
	{
		return $this->table->description;
	}

	/**
	 * @since	4.0
	 * @access	public
	 */
	public function getId()
	{
		return (int) $this->table->planaddons_id;
	}

	/**
	 * @since	4.0
	 * @access	public
	 */
	public function getPlans()
	{
		$plans = array();

		if ($this->table->plans) {
			$plans = json_decode($this->table->plans);
		}

		return $plans;
	}

	/**
	 * @since	4.0
	 * @access	public
	 */
	public function getApplyOn()
	{
		return $this->table->apply_on;
	}

	/**
	 * @since	4.0
	 * @access	public
	 */
	public function getParam($key, $default = null)
	{
		// XiError::assert($this);
		return $this->table->params->get($key, $default);
	}

	/**
	 * @since	4.0
	 * @access	public
	 */
	public function getConsumed()
	{
		return $this->table->consumed;
	}

	/**
	 * @since	4.0
	 * @access	public
	 */
	public function getPrice($raw = false)
	{
		if ($raw) {
			return $this->table->price;
		}

		return PPFormats::displayAmount($this->table->price);
	}

	/**
	 * @since	4.0
	 * @access	public
	 */
	public function getPriceType()
	{
		return $this->table->price_type;
	}

	/**
	 * @since	4.0
	 * @access	public
	 */
	public function getStartDate($nonAsEmpty = true)
	{
		if ($nonAsEmpty && stristr($this->table->start_date, '0000-00-00') !== false) {
			return '';
		}

		return $this->table->start_date;
	}

	/**
	 * @since	4.0
	 * @access	public
	 */
	public function getEndDate($nonAsEmpty = true)
	{
		if ($nonAsEmpty && stristr($this->table->end_date, '0000-00-00') !== false) {
			return '';
		}

		return $this->table->end_date;
	}

	/**
	 * @since	4.0
	 * @access	public
	 */
	public function getCondition($displayAsText = false)
	{
		if ($displayAsText) {
			$conditions = $this->getConditionRules();
			return isset($conditions[$this->table->addons_condition]) ? $conditions[$this->table->addons_condition] : '';
		}

		return $this->table->addons_condition;
	}

	/**
	 * @since	4.0
	 * @access	public
	 */
	public function getAvailability($displayAsText = false)
	{
		$params = $this->getParams();
		$availability = $params->get('availability', 0);

		if ($displayAsText) {
			if ($availability) {
				$stock = $params->get('stock');

				return $stock;
			}

			return JText::_('COM_PP_ADDONS_AVAILABILITY_UNLIMITED');
		}

		return $availability;
	}

	/**
	 * @since	4.0
	 * @access	public
	 */
	public function getStock()
	{
		$params = $this->getParams();
		$stock = $params->get('stock', 0);

		return $stock;
	}

	//for grid screen
	// public function toArray($strict = false, $readOnly = false)
	// {
	// 	if ( $this->table->apply_on == 1 ) {
	// 		$this->table->plans = array();
	// 	} else {
	// 		$this->table->plans = $this->getPlans();
	// 	}

	// 	return  parent::toArray();
	// }

	//at the time of save in edit screen
	// public function toDatabase($strict = false, $forReadOnly = false)
	// {
	// 	if ( $this->apply_on == 1 ) {
	// 		$this->plans = '';
	// 	} else {
	// 		$this->plans =  json_encode($this->plans);
	// 	}

	// 	return parent::toDatabase($strict, $forReadOnly);
	// }

}
