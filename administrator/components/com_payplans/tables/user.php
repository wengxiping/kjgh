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

class PayplansTableUser extends PayplansTable
{
	public $user_id = null;
	public $params = null;
	public $address = null;
	public $state = null;
	public $city = null;
	public $country = null;
	public $zipcode = null;
	public $preference = null;

	public function __construct(&$db)
	{
		parent::__construct('#__payplans_user', 'user_id', $db);
	}

	/**
	 * Overrides parent's store behavior as we need to create a new object if the object doesn't exist
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function store($updateNulls = false, $new = false)
	{
		if (!$this->user_id) {
			return false;
		}

		$model = PP::model('User');
		$exists = $model->isUserExists($this->user_id);

		if (!$exists) {
			$data = $this->toArray();
			$state = $model->initializeUser($data);
		}
	
		return parent::store($updateNulls);
	}
}