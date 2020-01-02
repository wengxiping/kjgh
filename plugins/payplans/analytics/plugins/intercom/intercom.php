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

class AnalyticsIntercom
{
	public function __construct()
	{
		$this->appId = ANALYTICS_INTERCOM_APPID;
		$this->apiKey = ANALYTICS_INTERCOM_APPKEY;
	}

	/**
	 * Create a record for the tracked event
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function trackEvent($user_id, $event_name, $args, $is_created = 0)
	{
		if ($is_created) {
			$this->createUser($user_id, $args);
		}

		$this->createEvent($user_id, $event_name, $args);
	}

	 public function createEvent($userId, $eventName, $metadata = null)
	{
		$data = array();
		$data['user_id'] = $userId;

		if (!empty($eventName)) {
			$data['event_name'] = $eventName;
		}

		if (!empty($email)) {
			$data['email'] = $metadata['email'];
		}

		if (!empty($metadata)) {
			$data['metadata'] = $metadata;
		}

		$data['created'] = time();

		return $this->send("https://api.intercom.io/events", json_encode($data));
	}

	/**
	 * Create user if the user is not exists
	 *
	 * @since	4.0.0
	 * @access	protected
	 */
	 protected  function createUser($id, $customData = array())
	{
		$data = array();
		$data['user_id'] = $id;

		if (!empty($customData['email'])) {
			$data['email'] = $customData['email'];
			unset($customData['email']);
		}

		if (!empty($customData['name'])) {
			$data['name'] = $customData['name'];
			unset($customData['name']);
		}

		if (!empty($createdAt)) {
			$data['created_at'] = time();
		}

		if (!empty($customData['ip'])) {
			$data['last_seen_ip'] = $customData['ip'];
			unset($customData['ip']);
		}

		if (!empty($customData)) {
			$data['custom_data'] = $customData;
		}

		return $this->send("https://api.intercom.io/users", json_encode($data));
	}

	/**
	 * Send the data to intercom
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function send($url, $post_data = null)
	{
		$headers = array('Content-Type: application/json','Accept: application/json');
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_POST, true);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_BUFFERSIZE, 4096);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, $this->appId . ':' . $this->apiKey);
		$response = curl_exec($ch);

		// Log last error for debugging purpose
		$this->lastError = array(
							'code' => curl_errno($ch),
							'message' => curl_error($ch),
							'httpCode' => curl_getinfo($ch, CURLINFO_HTTP_CODE)
						);

		return  json_decode($response);
	}
}
