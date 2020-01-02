<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPMailchimp
{
	/**
	 * Retrieves the list from Mailchimp
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLists($apiKey, $email)
	{
		static $lists = array();

		if (!$apiKey) {
			return array();
		}

		$idx = $apiKey . '_' . $email;

		if (!isset($lists[$idx])) {

			$datacenter = substr($apiKey, strrpos($apiKey, '-') + 1);

			$header = array(
				"Authorization: Basic " . base64_encode($email . ':' . $apiKey),
			);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://' . $datacenter . '.api.mailchimp.com/3.0/lists?offset=0&count=100');
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$output = curl_exec($ch);
			
			$response = json_decode($output);

			if (!$response) {
				$lists[$idx] = array();
				return $lists[$idx];
			}

			$results = $response->lists;

			$data = array();
			foreach ($results as $result) {
				$data[$result->id] = $result;
			}

			$lists[$idx] = $data;
		}

		return $lists[$idx];
	}
}
