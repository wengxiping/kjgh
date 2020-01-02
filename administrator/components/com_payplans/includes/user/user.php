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

require_once(PP_LIB . '/abstract.php');
require_once(__DIR__ . '/helpers/joomla.php');
// require_once(PP_LIB . '/interfaces/apptriggerable.php');


class PPUser extends PayPlans
{
	private $user = null;
	private $table = null;

	protected $helper = null;
	protected $subscriptions = null;

	public static function factory($id = null, $debug = false)
	{
		// return new self($id);
		$item = self::loadUser($id, $debug);
		return $item;
	}

	public function bind($data)
	{
		$this->user->bind($data);
		$this->table->bind($data);
	}

	public function __construct($key = null)
	{
		// Let the parent initiate the table first
		parent::__construct();

		// if (is_null($key)) {
		// 	$key = (int) JFactory::getUser()->id;
		// }

		// $this->table = PP::table('User');

		// if (is_object($key) || is_array($key)) {
		// 	$this->table->bind($key);
		// }

		// if (is_string($key) || is_int($key)) {
		// 	$this->table->load($key);
		// }

		// $this->user = JFactory::getUser($this->table->user_id);

		// // For user's, it is special as there are instances where the user record is not in the
		// // users table yet and it need to be initialized by adding into the table
		// $this->initialize($key);
	}


	/**
	 * Loads a given user id or an array of id's.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function loadUser($id = null, $debug = false)
	{
		$currentUserId = $id;

		// Ensure that is null or 0, the caller might be want to retrieve the current logged in user
		// Like those user cookies still stored from their browser, need to load back those user data.
		if (is_null($id) || !$id) {
			$currentUserId = JFactory::getUser()->id;
		}

		// The parsed id's could be an object from the database query.
		if (is_object($id) && isset($id->user_id) && $id->user_id) {
			$currentUserId = $id->user_id;
		}

		if (is_array($id) && isset($id['user_id']) && $id['user_id']) {
			$currentUserId = $id['user_id'];
		}

		if (isset(PPUserStorage::$users[$currentUserId])) {
			return PPUserStorage::$users[$currentUserId];
		}

		$obj = new PPUser();

		// load payplans_users
		$obj->table = PP::table('User');
		if (is_object($id) && isset($id->user_id)) {
			$obj->table->bind($id);
		} else {
			$obj->table->load($currentUserId);

			if (!$obj->table->user_id) {
				// this is new user record.
				$obj->table->user_id = (int) $currentUserId;
				$obj->table->store();
			}
		}

		// load juser
		$obj->user = JFactory::getUser($obj->table->user_id);

		// For user's, it is special as there are instances where the user record is not in the
		// users table yet and it need to be initialized by adding into the table
		$obj->initialize($currentUserId);

		PPUserStorage::$users[$currentUserId] = $obj;

		return PPUserStorage::$users[$currentUserId];
	}

	public function __get($key)
	{
		// Priority would be given to the JUser object
		if (isset($this->user->$key)) {
			return $this->user->$key;
		}

		if (isset($this->table->$key)) {
			return $this->table->$key;
		}

		if (isset($this->$key)) {
			return $this->$key;
		}
	}

	public function __set($key, $value)
	{
		// Priority would be given to the JUser object
		if (isset($this->user->$key)) {
			$this->user->$key = $value;
		}

		if (isset($this->table->$key)) {
			$this->table->$key = $value;
		}

		if (isset($this->$key)) {
			$this->$key = $value;
		}
	}

	/**
	 * Initializes the user's record into the database if it doesn't exist yet
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function initialize()
	{
		if ($this->table->user_id) {
			// load user subscriptions;
			$this->loadSubscriptions();
		}
	}

	/**
	 * Allows caller to login
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function login($username, $password, $secretkey = '')
	{
		$credentials = array(
			'username' => $username,
			'password' => $password,
			'secretkey' => $secretkey
		);

		$state = $this->app->login($credentials);
		$message = $this->app->getMessageQueue(true);

		if (!$state) {

			foreach ($message as $message) {
				$this->setError($message['message']);
			}

		} elseif ($state === true) {
			// somehow Joomla will return true even user deactivated or blocked
			// we need to check if the current user really login or not
			$jUser = JFactory::getUser();
			$user = PP::user($jUser->id);

			// Skip this process if the user unable to login and this user require reset password
			if (!$user->id || $user->require_reset) {
				
				foreach ($message as $message) {
					$this->setError($message['message']);
				}

				return false;
			}
		}

		return $state;
	}

	/**
	 * Binds the user's subscriptions to the object for easy access.
	 *
	 * @since	4.0.0
	 * @access	protected
	 */
	protected function loadSubscriptions()
	{
		$records = PP::model('subscription')->loadRecords(array('user_id' => $this->getId()));

		foreach ($records as $row) {
			$this->subscriptions[$row->subscription_id] = PP::subscription($row->subscription_id);
		}

		return $this;
	}

	/**
	 * Retrieving the avatar from Gravatar.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAvatar($size = 64, $default = '')
	{
		$avatar = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($this->getEmail()))) . "?d=" . urlencode($default) . "&s=" . $size;

		return $avatar;
	}

	/**
	 * Retrieves the user's address
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAddress()
	{
		return $this->address;
	}

	/**
	 * Retrieves the user's city
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCity()
	{
		return $this->table->city;
	}

	/**
	 * Retrieves the user's country of residence
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCountry()
	{
		return $this->table->country;
	}

	/**
	 * Retrieves the user's country of residence
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setCountry($countryId)
	{
		$this->table->country = $countryId;
	}

	/**
	 * Retrieves the custom details for the user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCustomDetails()
	{
		static $items = array();

		if (!isset($items[$this->getId()])) {
			$model = PP::model('Customdetails');
			$customDetails = $model->getUserCustomDetails($this);

			$items[$this->getId()] = $customDetails;
		}

		return $items[$this->getId()];
	}

	/**
	 * Retrieves the user's country of residence label
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCountryLabel()
	{
		static $cache = array();

		$key = $this->getId();

		if (!isset($cache[$key])) {
			if (!$this->table->country) {
				$cache[$key] = false;
				return false;
			}

			$country = PP::table('Country');
			$country->load((int) $this->table->country);

			$cache[$key] = JText::_($country->title);
		}

		return $cache[$key];
	}

	/**
	 * Retrieves the user's e-mail
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getEmail()
	{
		return $this->user->email;
	}

	/**
	 * Retrieves the user's name property
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getName()
	{
		return $this->user->name;
	}

	/**
	 * Tries to retrieve the first name
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFirstName()
	{
		static $items = array();

		$key = $this->getId();

		if (!isset($items[$key])) {
			$name = $this->getName();
			$parts = explode(' ', $name);

			$items[$key] = $name;

			if (is_array($parts)) {
				$items[$key] = $parts[0];
			}
		}

		return $items[$key];
	}

	/**
	 * Retrieves user's id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getId()
	{
		return $this->user->id;
	}

	/**
	 * Tries to retrieve the first name
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLastName()
	{
		static $items = array();

		$key = $this->getId();

		if (!isset($items[$key])) {
			$name = $this->getName();
			$parts = explode(' ', $name);

			$items[$key] = '';

			if (is_array($parts)) {
				$items[$key] = $parts[0];
			}
		}

		return $items[$key];
	}

	/**
	 * Retrieves the display-able name
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDisplayName()
	{
		return $this->user->name;
	}

	/**
	 * Retrieves the last visit date of a user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLastVisitDate()
	{
		return $this->user->lastvisitDate;
	}

	/**
	 * Retrieves the latest available plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPlan()
	{
		$plans = $this->getPlans();

		if (!$plans) {
			return false;
		}

		$subscription = array_shift($plans);
		$plan = PP::plan($subscription->plan_id);

		return $plan;
	}

	/**
	 * Retrieves a list of plans a user has
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPlans($status = PP_SUBSCRIPTION_ACTIVE)
	{
		static $result = array();
		static $plansCache = array();

		$key = $this->getId() . '.';
		$key .= (is_array($status)) ? implode('.', $status) : $status;

		// If user id not set then return empty array
		if (!$key) {
			return array();
		}

		if (!isset($result[$key])) {
			$model = PP::model('Subscription');
			$options = array('user_id' => array(array('=', $this->getId())), 'status' => $status);

			// to support retrival based on multiple status
			if (is_array($status)) {
				$tmp = array();

				foreach ($status as $stat) {
					$tmp[] = $stat;
				}

				$options['status'] = array(array('IN', '(' . implode(',', $tmp) . ')'));
			}

			$subscriptions = $model->loadRecords($options);

			$result[$key] = array();

			if ($subscriptions) {
				foreach ($subscriptions as $subscription) {

					if (isset($plansCache[$subscription->plan_id])) {
						$result[$key][] = $plansCache[$subscription->plan_id];

					} else {
						$plansCache[$subscription->plan_id] = PP::plan($subscription->plan_id);
						$result[$key][] = $plansCache[$subscription->plan_id];
					}
				}
			}
		}

		return $result[$key];
	}

	/**
	 * Retrieves the JRegistry object for user params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getParams()
	{
		static $params = array();

		if (!isset($params[$this->getId()])) {
			$param = new JRegistry($this->table->params);

			$params[$this->getId()] = $param;
		}

		return $params[$this->getId()];
	}

	/**
	 * Retrieves the JRegistry object for user preferences
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPreferences()
	{
		static $preferences = array();

		if (!isset($preferences[$this->getId()])) {

			$preference = new JRegistry($this->table->preference);

			$preferences[$this->getId()] = $preference;
		}

		return $preferences[$this->getId()];
	}

	/**
	 * Retrieves the registration date of the user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRegisterDate()
	{
		return $this->user->registerDate;
	}

	/**
	 * Because getName is used for the user's name, we need a different method to generate the key
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRewriterKey()
	{
		return 'USER';
	}

	/**
	 * Retrieves a list of tokens available for tokens
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRewriterTokens()
	{
		$preferences = $this->getPreferences();

		$tokens = array(
			'USER_ID' => $this->getId(),
			'REALNAME' => $this->getName(),
			'USERNAME' => $this->getUserName(),
			'EMAIL' => $this->getEmail(),
			// 'USERTYPE' => $this->getStatus(),
			'COUNTRY' => $this->getCountryLabel(),
			'PREFERENCE_BUSINESS_PURPOSE' => $preferences->get('purpose'),
			'PREFERENCE_BUSINESS_NAME' => $preferences->get('business_name'),
			'PREFERENCE_TIN' => $preferences->get('tin'),
			'PREFERENCE_SHIPPING_ADDRESS' => $preferences->get('shipping_address'),
			'PREFERENCE_BUSINESS_ADDRESS' => $preferences->get('business_address'),
			'PREFERENCE_BUSINESS_CITY' => $preferences->get('business_city'),
			'PREFERENCE_BUSINESS_STATE' => $preferences->get('business_state'),
			'PREFERENCE_BUSINESS_ZIP' => $preferences->get('business_zip')
		);

		// Retrieve any custom details for user
		$model = PP::model('CustomDetails');
		$customDetails = $model->getCustomDetails('user', true);

		foreach ($customDetails as $details) {
			$fields = $details->getFieldsOutput($this->getParams());
			foreach ($fields as $field) {
				$name = strtoupper(str_replace(' ', '_', $field->name));
				$tokens['CUSTOM_DETAILS_' . $name] = $field->value ? $field->value : '-';
			}
		}

		return $tokens;
	}

	/**
	 * Determines if the user requested download from the system before
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDownloadState()
	{
		$model = PP::model('Download');

		return $model->getDownloadStateByUser($this->getId());
	}

	/**
	 * Retrieves the total number of plans a user has
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTotalPlans()
	{
		$plans = $this->getPlans();

		$total = count($plans);

		return $total;
	}

	/**
	 * Retrieves the user's username
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUsername()
	{
		return $this->user->username;
	}

	/**
	 * Retrieves the state of the user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getState()
	{
		return $this->table->state;
	}

	/**
	 * Retrieves the user's zip code
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getZipcode()
	{
		return $this->table->zipcode;
	}

	/**
	 * Retrieves a list of subscriptions a user has
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSubscriptions($status = null)
	{
		if (!$this->subscriptions) {
			return array();
		}

		$subscriptions = array();

		foreach ($this->subscriptions as $key => $row) {
			if ($status === null || ($status === (int) $row->getStatus())) {
				$subscriptions[$key] = $row;
			}
		}

		return $subscriptions;
	}

	/**
	 * Determines if this account is a dummy account used to store temporary invoices
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isPlaceholderAccount()
	{
		if ($this->getUserName() == 'Not_Registered' && $this->user->block && $this->getEmail() == 'not@registered.com') {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user requested download from the system before
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isDownloadRequested()
	{
		$model = PP::model('Download');

		return $model->isDownloadRequestedByUser($this->getId());
	}

	/**
	 * Determines if the user is an admin
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isSiteAdmin()
	{
		static $data = array();

		if (!isset($data[$this->user->id])) {
			$data[$this->user->id] = $this->user->authorise('core.admin') || $this->user->authorise('core.manage');
		}

		return $data[$this->user->id];
	}

	/**
	 * Allows remote caller to set preferences for the user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setParams($params = array())
	{
		if (!($params instanceof JRegistry)) {
			$params = new JRegistry($params);
		}

		$this->table->params = $params->toString();
	}

	/**
	 * Allows remote caller to set preferences for the user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setPreferences($preference = array())
	{
		if (!($preference instanceof JRegistry)) {
			$preference = new JRegistry($preference);
		}

		$this->table->preference = $preference->toString();
	}

	/**
	 * Deprecated. Use @isSiteAdmin
	 *
	 * @deprecated	4.0.0
	 */
	public function isAdmin()
	{
		return $this->isSiteAdmin();
	}

	/**
	 * Saves the user object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function save()
	{
		// Trigger on before save
		$previous = PP::user($this->getId());
		$args = array($previous, $this);
		$result = PPEvent::trigger('onPayplansUserBeforeSave', $args, '', $this);

		$state = $this->user->save();

		if (!$state) {
			$this->setError($this->user->getError());
			return $state;
		}

		$this->table->store();
		$args = array($previous, $this);
		$result = PPEvent::trigger('onPayplansUserAfterSave', $args, '', $this);

		return true;
	}

	/**
	 * Retrieve a list of user existing plan ids
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUserPlans($userid)
	{
		$model = PP::model('Plan');
		$result = $model->getUserPlans($userid);

		return $result;
	}

	/**
	 * Method to return all the table properties as array
	 * Used in Logger
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function toArray($strict = false, $readOnly = false)
	{
		$data = $this->table->toArray();

		return $data;
	}


	/**
	 * Retrieves the usage of a code
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getReferralDetails()
	{
		$model = PP::model('Referrals');
		$result = $model->getReferralDetails($this);

		foreach ($result as $data) {
			$data->referral_id = PP::user($data->referral_id);
			$data->plan_id = PP::plan($data->plan_id);
		}

		return $result;
	}
}


/**
 * This class would be used to store all user objects
 *
 */
class PPUserStorage
{
	static $users = array();
}