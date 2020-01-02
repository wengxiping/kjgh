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

class PPIpn extends PayPlans
{
	public function log($paymentId, $data)
	{
		ob_start();
		print_r($_REQUEST);
		$raw = ob_get_clean();
		ob_end_clean();

		// Log the payment request
		$ipn = PP::table('IPN');
		$ipn->payment_id = $paymentId;
		$ipn->json = json_encode($data);
		$ipn->query = http_build_query($data);
		$ipn->raw = $raw;
		$ipn->php = file_get_contents("php://input");
		$ipn->ip = @$_SERVER['REMOTE_ADDR'];
		$ipn->created = JFactory::getDate()->toSql();

		$args = array(&$ipn, $data);
		$results = PP::event()->trigger('onPayplansBeforeStoreIpn', $args);

		$ipn->store();

		return $ipn;
	}
}