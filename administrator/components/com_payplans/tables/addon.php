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

class PayplansTableAddon extends PayplansTable
{
	public $planaddons_id = null;
	public $title = null;
	public $description = null;
	public $price = null;
	public $consumed = null;
	public $addons_condition = null;
	public $price_type = null;
	public $apply_on = null;
	public $plans = null;
	public $start_date = null;
	public $end_date = null;
	public $published = null;
	public $params = null;

	public function __construct($db)
	{
		parent::__construct('#__payplans_planaddons', 'planaddons_id', $db);
	}

	/**
	 * Allow caller to publish the advancepricing rule
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function publish($items = array(), $state = 1, $userId = 0)
	{
		$this->published = 1;
		$this->store();
	}

	/**
	 * Allow caller to unpublish the advancepricing rule
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function unpublish($items = array())
	{
		$this->published = 0;
		$this->store();
	}
 }
