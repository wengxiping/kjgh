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

class PayplansModelAdvancedpricing extends PayPlansModel
{
	public function __construct()
	{
		parent::__construct('advancedpricing');
	}

	/**
	 * Retrieve userdetails app.
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getItems()
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT * FROM `#__payplans_advancedpricing`';

		$query = implode(' ', $query);
		$db->setQuery($query);
		$results = $db->loadObjectList();

		foreach ($results as $result) {

			$tmp = json_decode($result->plans);

			if (is_null($tmp)) {
				// Legacy data
				$result->assignedPlans = explode(',', $result->plans);
			} else {
				$result->assignedPlans = $tmp;
			}

			$params = json_decode($result->params);

			$prices = $params->price;

			$durations = $params->expiration_time;

			$priceSet = array();

			for ($i=0; $i < count($durations); $i++) { 
				$priceSet[] = array('duration' => $durations[$i], 'price' => $prices[$i]);
			}

			$result->priceset = $priceSet;
		}

		return $results;
	}

}

