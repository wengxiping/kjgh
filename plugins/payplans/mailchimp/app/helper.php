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

class PPHelperMailchimp extends PPHelperStandardApp
{
	protected $_resource = 'com_mailchimp.list';

	/**
	 * Retrieves the API key for mailchimp
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApiKey()
	{
		static $key = null;

		if (is_null($key)) {
			$key = $this->params->get('mailchimpApiKey');
		}
		return $key;
	}

	/**
	 * Retrieves the API key for mailchimp
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getConnectionHeaders()
	{
		static $headers = null;

		if (is_null($headers)) {
			$headers = array(
				"Authorization: Basic " . base64_encode($this->getMerchantEmail() . ':' . $this->getApiKey()),
			);
		}

		return $headers;
	}



	/**
	 * Retrieves the API key for mailchimp
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDatacenter()
	{
		static $datacenter = null;

		if (is_null($datacenter)) {
			$key = $this->getApiKey();
			$datacenter = substr($key, strrpos($key, '-') + 1);
		}
		return $datacenter;
	}

	/**
	 * Retrieves the API key for mailchimp
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMerchantEmail()
	{
		static $email = null;

		if (is_null($email)) {
			$email = $this->params->get('mailchimpMerchantEmail');
		}

		return $email;
	}

	/**
	 * Retrieves the API key for mailchimp
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getActiveLists()
	{
		static $lists = null;

		if (is_null($lists)) {
			$lists = $this->params->get('addToListonActive');

			if (!is_array($lists)) {
				$lists = array($lists);
			}
		}
		return $lists;
	}

	/**
	 * Retrieves the API key for mailchimp
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getHoldLists()
	{
		static $lists = null;

		if (is_null($lists)) {
			$lists = $this->params->get('addToListonHold');

			if (!is_array($lists)) {
				$lists = array($lists);
			}
		}
		return $lists;
	}

	/**
	 * Retrieves the API key for mailchimp
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getExpiredLists()
	{
		static $lists = null;

		if (is_null($lists)) {
			$lists = $this->params->get('addToListonExpire');

			if (!is_array($lists)) {
				$lists = array($lists);
			}
		}
		return $lists;
	}

	/**
	 * Determines if the app should send confirmation e-mails
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function sendConfirmationEmail()
	{
		static $send = null;

		if (is_null($send)) {
			$send = $this->params->get('mailchimpMail');
		}
		return $send;
	}

	/**
	 * Retrieves a list of members
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMemberInfo($listId, $hash, $returnEmails = true)
	{
		// Get a list of members in the list
		$datacenter = $this->getDatacenter();
		$url = 'https://' . $datacenter . '.api.mailchimp.com/3.0/lists/'.$listId.'/members/' . $hash;
		
		$result = $this->getRequest($url);

		if (!$returnEmails) {
			return $result;
		}

		$emails = array();

		foreach ($result['members'] as $data) {

			if ($data->status == 'subscribed') {
				$emails[] = $data->email_address;
			}
		}

		return $emails;
	}

	/**
	 * Retrieves a list of members
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getListMembers($listId)
	{
		// Get a list of members in the list
		$datacenter = $this->getDatacenter();
		$url = 'https://' . $datacenter . '.api.mailchimp.com/3.0/lists/'.$listId.'/members/';
		
		$result = $this->getRequest($url);
		$emails = array();

		foreach ($result->members as $data) {

			if ($data->status == 'subscribed') {
				$emails[] = $data->email_address;
			}
		}

		return $emails;
	}

	/**
	 * Connects to mailchimp to retrieve data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRequest($url, $payload = null, $customRequestOverride = '')
	{
		$headers = $this->getConnectionHeaders();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		if ($payload) {
			$customRequest = ($customRequestOverride) ? $customRequestOverride : 'PUT';

			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $customRequest);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
		}

		$output = curl_exec($ch);

		$response = json_decode($output);
		
		return $response;
	}

	/**
	 * Sends a DELETE request to mailchimp
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function deleteRequest($url)
	{
		$headers = $this->getConnectionHeaders();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		$response = json_decode($result);

		return $response;
	}

	/**
	 * Retrieves a list of members
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function insertEmail($listId, $email, $name, $hash = null)
	{
		$defaultStatus = 'subscribed';

		if ($this->sendConfirmationEmail()) {
			// when send confirmation email enabled, we need to check if the list enabled 
			// double opt in or not. If yes, set the status
			// to pending so that the user who about to be subscribed get the
			// email confirmation.

			$lists = PP::mailchimp()->getLists($this->getApiKey(), $this->getMerchantEmail());
			if ($lists) {
				if (isset($lists[$listId])) {
					$listObj = $lists[$listId];
					// check for double opt in.
					if (isset($listObj->double_optin) && $listObj->double_optin) {
						$defaultStatus = 'pending';
					}
				}
			}
		}

		$payload = array(
			'email_type' => 'html',
			'status' => $defaultStatus,
			'status_if_new' => $defaultStatus,
			'merge_fields' => array('FNAME' => $name),
			'email_address' => $email
		);

		// when adding new member and if there is no hash tag, we need to use POST
		$customRequest = 'POST';

		$datacenter = $this->getDatacenter();
		$url = 'https://' . $datacenter . '.api.mailchimp.com/3.0/lists/'.$listId.'/members';

		if ($hash) {
			$customRequest = '';
			$url .= '/' . $hash;
		}

		$response = $this->getRequest($url, $payload, $customRequest);

		return $response;
	}

	/**
	 * Retrieves a list of members
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function removeEmail($listId, $hash)
	{
		$datacenter = $this->getDatacenter();
		$url = 'https://' . $datacenter . '.api.mailchimp.com/3.0/lists/'.$listId.'/members/' . $hash;

		return $this->deleteRequest($url);
	}

	/**
	 * Inserts user into list
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function insert($lists, PPUser $user, $subscriptionId)
	{
		$apiKey = $this->getApiKey();
		$datacenter = $this->getDatacenter($apiKey);

		if (!is_array($lists) || empty($lists)) {
			return false;
		}

		foreach ($lists as $listId) {

			if (!$listId) {
				continue;
			}

			// Get a list of members in the list
			$emails = $this->getListMembers($listId);

			if (in_array($user->getEmail(), $emails)) {
				$this->addResource($subscriptionId, $user->getId(), $listId, $this->_resource);
				continue;
			}

			$params = $user->getParams();
			$hash = $params->get('subscriber_hash');

			// Insert the record into mailchimp
			if (!$hash) {
				$response = $this->insertEmail($listId, $user->getEmail(), $user->getName());

				// Save the hash
				$hash = PP::normalize($response, 'id', '');

				if ($hash) {
					$params->set('subscriber_hash', $hash);
					$user->params = $params->toString();
					$user->save();
				}

				$this->addResource($subscriptionId, $user->getId(), $listId, $this->_resource);
				continue;
			}

			// Here we assume that there is a subscriber_hash in their user account but they are not subscribed to the list.
			// Possibly, they unsubscribed directly from Mailchimp
			$response = $this->getMemberInfo($listId, $hash, false);

			if ($response->status == 'subscribed') {
				continue;
			}

			// if ($hash && $response->status == '404') {
			// 	// this mean the hash is not for this list id. adding member into invalid list id will cause
			// 	// error. we will remove the hash here before proceed further.
			// 	$hash = '';
			// }

			// If it is not subscribe, we need to subscribe the user
			$response = $this->insertEmail($listId, $user->getEmail(), $user->getName());

			if ($response->id == $hash) {
				$this->addResource($subscriptionId, $user->getId(), $listId, $this->_resource);
				continue;
			}

			// If we are unable to, just remove the hash
			$hash = PP::normalize($response, 'id', '');

			if ($hash) {
				$params->set('subscriber_hash', $hash);
				$user->params = $params->toString();
				$user->save();
			}
			
			$this->addResource($subscriptionId, $user->getId(), $listId, $this->_resource);

			PPLog::log(PPLogger::LEVEL_ERROR, JText::sprintf('COM_PAYPLANS_LOGGER_MAILCHIMP_LOG_ERROR_OCCURED', $user->getId(), $listId), $this, (array) $response, 'PayplansAppMailchimpFormatter');
		}

		return true;
	}

	/**
	 * Remove a user from a list
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function remove($lists, PPUser $user, $subscriptionId)
	{
		if (!is_array($lists) || empty($lists)) {
			return false;
		}

		foreach ($lists as $listId) {
			
			if (!$listId) {
				continue;
			}

			// Get a list of members in the list
			$emails = $this->getListMembers($listId);

			// If the user is not in the list, no point removing it
			if (!in_array($user->getEmail(), $emails)) {
				continue;
			}

			$params = $user->getParams();
			$hash = $params->get('subscriber_hash', '');

			if ($hash) {
				$response = $this->removeEmail($listId, $hash);
			}
			
			$this->removeResource($subscriptionId, $user->getId(), $listId, $this->_resource);
		}

		return true;
	}
}