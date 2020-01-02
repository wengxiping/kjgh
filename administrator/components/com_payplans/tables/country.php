<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

PP::import('admin:/tables/table');

class PayplansTableCountry extends PayplansTable
{
	public $country_id = null;
	public $title = null;
	public $isocode2 = null;
	public $isocode3 = null;
	public $isocode3n = null;

	/**
	 * Class Constructor
	 *
	 * @since	3.7
	 * @access	public
	 */
	public function __construct($db)
	{
		parent::__construct('#__payplans_country', 'country_id', $db);
	}
}

