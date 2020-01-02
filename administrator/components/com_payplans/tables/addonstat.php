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

PP::import('admin:/tables/table');

class PayplansTableAddonStat extends PayplansTable
{
	// Table fields
	public $planaddons_stats_id;
	public $user_id;
	public $planaddons_id;
	public $title;
	public $price;
	public $addons_condition;
	public $price_type;
	public $reference;
	public $status;
	public $purchase_date;
	public $params;

	public function __construct($db)
	{
		parent::__construct('#__payplans_planaddons_stats', 'planaddons_stats_id', $db);
	}

	/**
	 * @since	4.0
	 * @access	public
	 */
	public function getPurchaseDate()
	{
		return $this->purchase_date;
	}

	/**
	 * @since	4.0
	 * @access	public
	 */
	public function getUserName()
	{
		$user = JFactory::getUser($this->user_id);
		$format = '#' . $this->user_id . ': ' . $user->name . ' (' . $user->username . ')';

		return $format;
	}

	/**
	 * @since	4.0
	 * @access	public
	 */
	public function getCondition($displayAsText = false)
	{
		if ($displayAsText) {
			$conditions = $this->getConditionRules();
			return isset($conditions[$this->addons_condition]) ? $conditions[$this->addons_condition] : '';
		}

		return $this->addons_condition;
	}

	/**
	 * @since	4.0
	 * @access	public
	 */
	public function getPrice($raw = false)
	{
		if ($raw) {
			return $this->price;
		}

		return PPFormats::displayAmount($this->price);
	}

	/**
	 * @since	4.0
	 * @access	public
	 */
	public function getConditionRules()
	{
		$conditions = array(PP_PLANADDONS_ONETIME => JText::_('COM_PP_ADDONS_CONDITION_ONETIME'), PP_PLANADDONS_EACHRECURRING => JText::_('COM_PP_ADDONS_CONDITION_EACHRECURRING'));

		return $conditions;
	}

	/**
	 * @since	4.0
	 * @access	public
	 */
	public function getStatusList()
	{
		$status = array(
			PP_PLANADDONS_STAT_PENDING => JText::_('COM_PP_ADDONS_STAT_PENDING'),
			PP_PLANADDONS_STAT_WORKING => JText::_('COM_PP_ADDONS_STAT_WORKING'),
			PP_PLANADDONS_STAT_PROCESSED => JText::_('COM_PP_ADDONS_STAT_PROCESSED')
		);

		return $status;
	}

 }
