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
require_once(PP_LIB . '/interfaces/apptriggerable.php');
require_once(PP_LIB . '/interfaces/api/plan.php');

class PPPlan extends PPAbstract implements PPAppTriggerableInterface, PayplansIfaceApiPlan
{
	// skip these tokens in token rewriter
	public  $_blacklist_tokens = array('published','visible','params');

	public static function factory($id = 0)
	{
		// static $loaded = array();

		// $key = $id;

		// if (is_object($id)) {
		// 	$key = $id->plan_id;
		// }

		// if (is_array($id) && isset($id['plan_id'])) {
		// 	$key = $id['plan_id'];
		// }

		// var_dump($key);

		// if (!isset($loaded[$key])) {
		// 	$loaded[$key] = new self($key);
		// }

		// return $loaded[$key];

		return new self($id);
	}


	// not for table fields
	/**
	 * reset all the Plans properties to their default values
	 */
	public function reset($option = array())
	{
		$this->table->plan_id = 0;
		$this->table->title = '';
		$this->table->published = 1;
		$this->table->visible = 1;
		$this->table->description = '';
		$this->table->details = new JRegistry();
		$this->table->params = new JRegistry();
		$this->_planapps = array();
		$this->_groups = array();
		$this->_modifier = array();
		$this->_advPricing = array();

		return $this;
	}

	/*
	 * @return PayplansPlan
	 * @param string $dummy is added just for removing warning with development mode(XiLib::getInstance is having 4 parameters)
	 */
	// static public function getInstance($id=0, $type=null, $bindData=null, $dummy=null)
	// {
	// 	return parent::getInstance('plan',$id, $type, $bindData);
	// }

	/**
	 * Override parent's afterBind behavior
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function afterBind($id = 0)
	{
		if (!$id) {
			return $this;
		}

		$this->_planapps = PP::model('planapp')->getPlanApps($id);
		$this->_groups = PP::model('plangroup')->getPlanGroups($id);

		return $this;
	}

	/**
	 * Override parent's bind behavior
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function bind($data = array(), $ignore=array())
	{
		if (is_object($data)) {
			$data = (array) ($data);
		}

		parent::bind($data, $ignore=array());

		// Bind details params if needed
		$paramsData = array();

		if (is_array($data) && isset($data['details'])) {
			$paramsData = $data['details'];

			if ($data['details'] instanceof JRegistry) {
				$paramsData = $data['details']->toArray();
			}
		}

		if (is_object($data) && isset($data->details)) {
			$paramsData = $data->details;
		}

		$params = new JRegistry($paramsData);
		$this->table->details = $params->toString();


		if (isset($data['planapps'])) {
			$this->_planapps = $data['planapps'];
		}

		// bind groups
		if (isset($data['groups'])) {
			$this->_groups = $data['groups'];
		}
		return $this;
	}

	/**
	 * Handle pre-saving process
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function preSave()
	{
		// Ensure that any newly set params is stored correctly
		if ($this->getId()) {
			$params = $this->getParams();
			$this->table->params = $params->toString();
		}
	}

	/**
	 * Save the plan
	 * @see XiLib::save()
	 *
	 * @return object PayplansPlan
	 */
	public function save()
	{
		$this->preSave();

		parent::save();

		$this->updateOrdering();
		$this->savePlanApps();
		$this->savePlanGroups();

		$this->postSave();

		return $this;
	}

	/**
	 * Update ordering column if needed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function updateOrdering()
	{
		if (! $this->table->ordering) {
			$this->table->saveOrder();
		}
	}

	/**
	 * Post processing after the plan is save
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function postSave()
	{
		// Process plan scheduling
		$this->updatePublishingState();

		// Process dependable plans
		$this->updateDependablePlans();
	}

	/**
	 * Updated the publishing state of the plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function updatePublishingState()
	{
		$this->checkSubscriptionLimit();
		$this->checkSchedulingStatus();
	}

	/**
	 * Determine the subscription limit status
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function checkSubscriptionLimit()
	{
		if ($this->hasSubscriptionLimit()) {
			$max = $this->getMaxSubscriptionLimit();
			$totalSubscribers = $this->getTotalSubscribers();

			if ($totalSubscribers >= $max) {
				$this->table->published = 0;
			} else {
				$this->table->published = 1;
			}

			$this->table->store();
		}
	}

	public function checkSchedulingStatus()
	{
		if (!$this->isScheduled()) {
			return true;
		}

		// Check for max subscription
		if ($this->hasSubscriptionLimit()) {
			$max = $this->getMaxSubscriptionLimit();
			$totalSubscriber = $this->getTotalSubscribers();

			// Do not change status if limit is reached
			if ($totalSubscriber >= $max) {
				return true;
			}
		}

		if ($this->isWithinSchedule()) {
			$this->table->published = 1;
		} else {
			$this->table->published = 0;
		}

		$this->table->store();
	}

	/**
	 * Determine the state of the scheduling
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isWithinSchedule()
	{
		$currentDate = PP::date();
		$startDate = $this->getPublishedDate();
		$endDate = $this->getUnpublishedDate();

		// when range is not set then do nothing
		if (empty($startDate) && empty($endDate)) {
			return true;
		}

		// when both date set then check current date lies between this range
		if (!empty($startDate) && !empty($endDate) && (($currentDate->toUnix() > $endDate->toUnix()) || ($currentDate->toUnix() < $startDate->toUnix()))) {
			return false;
		}

		// when range is set then check subscription date whether lies in that range 
		if (!empty($startDate) && !empty($endDate) && ($currentDate->toUnix() >= $startDate->toUnix()) && ($currentDate->toUnix() <= $endDate->toUnix())) {
			return true;
		}

		// when start date is set and end date is not, check plan lies in the range
		if (!empty($startDate) && $currentDate->toUnix() >= $startDate->toUnix()) {
			return true;
		}

		//when end date is set and start date not , check plan lies in the range
		if (!empty($endDate) && $currentDate->toUnix() <= $endDate->toUnix()) {
			return true;
		}

		//when end date is set then check current date is higher then end date (to unpublish plan)
		if (!empty($endDate) && $currentDate->toUnix() > $endDate->toUnix()) {
			return false;
		}
	}

	/**
	 * Process dependable plans
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function updateDependablePlans()
	{
		$table = PP::table('Parentchild');
		$table->load(array('dependent_plan' => $this->getId()));

		if (!$table->dependent_plan) {
			$table->dependent_plan = $this->getId();
		}

		$table->base_plan = $this->getDependablePlans() ? implode(',', $this->getDependablePlans()) : '';
		$table->relation = $this->getParams()->get('displaychildplanon');
		$table->store();
	}

	/**
	 * Add subscriber count
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function updateSubscriberCount($operation = 'add')
	{
		$total = $this->getTotalSubscribers();

		if ($operation == 'add') {
			$total++;
		} else {
			$total--;
		}

		$this->setParam('total_count', $total);
		$this->save();
	}

	/**
	 * Allows caller to assign a plan to a user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function subscribe($userId)
	{
		// Create a new order and assign to the user
		$order = PP::order();
		$order->setBuyer($userId);
		$order->currency = $this->getCurrency('isocode');
		$order->save();

		// Create a new subscription and attach order with subscription
		$subscription = PP::subscription();
		$subscription->setPlan($this);
		$subscription->setOrder($order);
		$subscription->save();

		// Refresh order after saving subscription
		$order->refresh();
		$state = $order->save();

		if (!$state) {
			return false;
		}

		return $order;
	}

	/**
	 * Determine if user can really subscribe to the plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function canSubscribe($userId = null)
	{
		$parentChild = PP::parentChild();

		$isAllowed = $parentChild->canSubscribe($this->getId(), $userId);

		if (!$parentChild->canSubscribe($this->getId(), $userId)) {
			$this->setError($parentChild->getError());
			return false;
		}

		$user = PP::user($userId);

		if (!PPLimitsubscription::canSubscribe($user, $this->getId())) {
			$message = JText::sprintf('COM_PP_LIMITSUBSCRIPTION_NOT_ALLOW');
			$this->setError($message);
			return false;
		}

		return true;
	}

	public function hasApp($appId)
	{
		return in_array($appId,$this->_planapps);
	}

	/**
	 * Deletes a plan from the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function delete()
	{
		//delete plan only when no subscription exists for the corresponding plan
		$subscription = PP::model('subscription')->loadRecords(array('plan_id'=>$this->getId()));

		if (empty($subscription)) {

			// Delete discounts related to the plan
			$this->deleteDiscounts();

			// Delete plan relations with apps
			$this->deleteAppRelations();

			return parent::delete();
		}

		$this->setError(JText::_('COM_PAYPLANS_PLAN_GRID_CAN_NOT_DELETE_PLAN_SUBSCRIPTION_EXISTS'));
		return false;
	}

	/**
	 * Deletes discounts related to the plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function deleteDiscounts()
	{
		$model = PP::model('Discount');
		return $model->deletePlanDiscounts($this->getId());
	}

	/**
	 * Delete relations between a plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function deleteAppRelations()
	{
		$model = PP::model('Plan');
		return $model->deleteAppRelations($this->getId());
	}

	/**
	 * Retrieves the title of the plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set the title of the plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setTitle($title)
	{
		$this->table->title = $title;

		// so that caller can do chaining
		return $this;
	}

	/**
	 * Set plan's ordering
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setOrdering($ordering)
	{
		$this->table->ordering = $ordering;

		// so that caller can do chaining
		return $this;
	}

	/**
	 * Retrieves the permalink of the plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSelectPermalink($xhtml = true)
	{
		$url = 'index.php?option=com_payplans&task=plan.subscribe&plan_id=' . $this->getId() . '&tmpl=component';

		$url = JRoute::_($url, $xhtml);

		return $url;
	}

	/**
	 * Returns the price of plan with different expiration types
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPrice($type = null)
	{
		if ($type === PP_PRICE_RECURRING_TRIAL_1) {
			return PPFormats::price($this->getDetails()->get('trial_price_1', 0.00));
		}

		if ($type === PP_PRICE_RECURRING_TRIAL_2) {
			return PPFormats::price($this->getDetails()->get('trial_price_2', 0.00));
		}

		return PPFormats::price($this->getDetails()->get('price', 0.00));
	}

	/**
	 * Sets the price of the plan
	 *
	 * @param  float $price  Price to set on the current plan
	 * @param  integer               $type   Expiration type for which price is to be set
	 *
	 * @return mixed  The value of the that has been set.
	 */
	public function setPrice($price, $type = null)
	{
		$var = 'price';
		if ($type === PP_RECURRING_TRIAL_1) {
			$var = 'trial_price_1';
		} elseif ($type === PP_RECURRING_TRIAL_2) {
			$var = 'trial_price_2';
		}

		return $this->getDetails()->set($var, $price);
	}

	/**
	 * Gets plan expiration time
	 *
	 * @see PayplansIfaceApiPlan::getExpiration()
	 *
	 * @return Array  An array containing expiration values for year, month, day and so on
	 */
	public function getExpiration($type = PP_PRICE_FIXED, $raw = false)
	{
		$column = 'expiration';

		if ($type == PP_PRICE_RECURRING_TRIAL_1) {
			$column = 'trial_time_1';
		} else if ($type == PP_PRICE_RECURRING_TRIAL_2) {
			$column = 'trial_time_2';
		}

		$rawTime = $this->getDetails()->get($column);

		if ($raw) {
			return $rawTime;
		}

		return PPHelperPlan::convertIntoTimeArray($rawTime);
	}

	/**
	 * Sets the expiration time(regular expiration time) of the plan
	 *
	 * @see PayplansIfaceApiPlan::setExpiration()
	 *
	 * @param  string $time  12 digits numeric string each 2 digits denotes the value for year, month, day, minute, hour and second in the same sequence, starting from year(starting 2 digits indicate year)
	 *
	 * @return object PayplansPlan
	 */
	public function setExpiration($time)
	{
		$this->getDetails()->set('expiration', $time);
		return $this;
	}

	/**
	 * Gets the currency of the plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCurrency($format = null)
	{
		$currency = $this->getDetails()->get('currency', PP::config()->get('currency'));
		$currency = PPFormats::currency(PP::getCurrency($currency), array(), $format);

		return $currency;
	}

	/**
	 *
	 * Sets the currency of the plan
	 * if currency is not mentioned then currency set in the configuration will be set in the plan
	 *
	 * @param string  $currency currency isocode to set for the current plan
	 *
	 * @return object PayplansPlan
	 */
	public function setCurrency($currency = null)
	{
		if ($currency === null) {
			$currency = PP::config()->get('currency');
		}

		$this->getDetails()->set('currency', $currency);
		return $this;
	}

	/**
	 * Implementing interface Apptriggerable
	 * @return array
	 */
	public function getPlans($all= false , $instanceRequire = false )
	{
		if ($all == false) {
			return array($this->getId());
		}

		$filter = array('published' => 1);
		$plans = PP::model('Plan')->loadRecords($filter);

		if ($instanceRequire !== PP_INSTANCE_REQUIRE) {
			return array_keys($plans);
		}

		$instances = array();
		foreach ($plans as $data) {
			// $instances[$plan->plan_id] = PayplansPlan::getInstance($plan->plan_id, null, $plan);

			$plan = PP::plan();
			$plan->bind($data);

			$instances[$plan->plan_id] = $plan;
		}

		return $instances;

	}

	/**
	 * Retrieve all of the dependable plans
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDependablePlans()
	{
		$plans = $this->getParams()->get('parentplans');
		$plans = PP::makeArray($plans);

		return $plans;
	}

	/**
	 * Gets group the plan is attached with
	 *
	 * @see PayplansIfaceApiPlan::getGroups()
	 *
	 * @return Array of group id
	 */
	public function getGroups()
	{
		return $this->_groups;
	}

	/**
	 * Gets published status of the plan
	 *
	 * @see PayplansIfaceApiPlan::getPublished()
	 *
	 * @return boolean True if plan is published
	 */
	public function getPublished()
	{
		return $this->table->published;
	}

	/**
	 * Gets plan visibility
	 *
	 * @see PayplansIfaceApiPlan::getVisible()
	 *
	 * @return boolean True if plan is visible
	 */
	public function getVisible()
	{
		return $this->table->visible;
	}

	/**
	 * Retrieves the description of the plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDescription($descriptionFormat = false)
	{
		if ($descriptionFormat == true && $this->table->description) {

			$planDescription = new stdClass();
			$planDescription->text = $this->table->description;
			JPluginHelper::importPlugin('content');

			$param = null;
			$args = array('com_payplans.planDescription', &$planDescription, &$param, 0);
			PP::dispatcher()->triggerPlugin('onContentPrepare', $args);

			return $planDescription->text;
		}

		return $this->table->description;
	}

	/**
	 * Retrieves the teaser text of the plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTeaser()
	{
		$params = $this->getParams();
		$teaser = $params->get('teasertext', '');

		return $teaser;
	}

	/**
	 * Gets the app the plan is attached with
	 *
	 * @see PayplansIfaceApiPlan::getPlanapps()
	 *
	 * @return array of app id
	 */
	public function getPlanapps()
	{
		return $this->_planapps;
	}

	/**
	 * Gets the raw expiration time of the plan
	 * @return string  expiration time of the plan
	 */
	public function getRawExpiration()
	{
		return $this->getDetails()->get('expiration');
	}

	/**
	 * Retrieves the details of the plan. Plan Details include expiration type, expiration time,
	 * price, currecny, trial time, trial price, recurrence count etc
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDetails()
	{
		$details = new JRegistry($this->table->details);
		return $details;
	}

	/**
	 * Gets the Teaser Text of the plan
	 *
	 * @see PayplansIfaceApiPlan::getTeasertext()
	 *
	 * @return string
	 */
	public function getTeasertext()
	{
		return $this->getParams()->get('teasertext','');
	}

	public function getCssClasses()
	{
		return $this->getParams()->get('css_class','');
	}

	// Getters for Badges Configuration

	public function getBadgeTitle()
	{
		return $this->getParams()->get('badgeTitle','');
	}

	public function getBadgeBackgroundColor()
	{
		return $this->getParams()->get('badgebackgroundcolor','');
	}

	public function getBadgePosition()
	{
		$mapping = array('top-left' => 'left', 'top-center' => 'center', 'top-right' => 'right');
		$position = $this->getParams()->get('badgePosition', '');

		// Legacy naming
		if (isset($mapping[$position])) {
			return $mapping[$position];
		}

		return $position;
	}

	public function getBadgeVisible()
	{
		return $this->getParams()->get('badgeVisible','');
	}

	public function getBadgeTitleColor()
	{
		return $this->getParams()->get('badgeTitleColor','');
	}

	/**
	 * Determine if there is subscription limit for the app
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function hasSubscriptionLimit()
	{
		return $this->getMaxSubscriptionLimit() > 0 ? true : false;
	}

	/**
	 * Retrieve the maximum number of the subscriptions allowed for this plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMaxSubscriptionLimit()
	{
		return $this->getParams()->get('limit_count', '');
	}

	/**
	 * Determine if the plan is scheduled
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isScheduled()
	{
		return $this->getParams()->get('scheduled', '');
	}

	/**
	 * Determine if the plan's subscription need a moderation
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function requireModeration()
	{
		return $this->getParams()->get('moderate_subscription', '');
	}

	/**
	 * Retrieve the start date of the plan availibility
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPublishedDate()
	{
		$date = $this->getParams()->get('start_date', '');
		$date = PP::date($date);

		return $date;
	}

	/**
	 * Retrieve the end date of the plan availibility
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUnpublishedDate()
	{
		$date = $this->getParams()->get('end_date', '');
		$date = PP::date($date);

		return $date;
	}

	/**
	 * Get total subscribers
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTotalSubscribers()
	{
		return $this->getParams()->get('total_count', 0);
	}

	/**
	 * Retrieves params for the plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getParams()
	{
		static $data = array();

		if (!isset($data[$this->getId()])) {
			$params = new JRegistry($this->params);
			
			$data[$this->getId()] = $params;
		}
		
		return $data[$this->getId()];
	}

	/**
	 * Determines if this plan is a free plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isFree()
	{
		$zero = floatval(0);
		$price = floatval($this->getPrice());
		$free = $price == $zero;

		return $free;
	}

	/**
	 * Determines if the plan is highlighted
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isHighlighted()
	{
		$params = $this->getParams();

		$highlighted = $params->get('planHighlighter', false);

		return $highlighted;
	}

	/**
	 * Determine if the plan has badge
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function hasBadge()
	{
		return $this->getBadgeVisible();
	}

	/**
	 * Determines if the plan is really published
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isPublished()
	{
		return $this->table->published ? true : false;
	}

	/**
	 * Is recurring Plan?
	 *
	 * @see PayplansIfaceApiPlan::isRecurring()
	 *
	 * @return integer when plan is recurring/recurring+trial else return False
	 */
	public function isRecurring()
	{
		$expirationType = $this->getExpirationType();

		if ($expirationType == PP_RECURRING) {
			return PP_PRICE_RECURRING;
		}

		if ($expirationType == PP_RECURRING_TRIAL_1) {
			return PP_PRICE_RECURRING_TRIAL_1;
		}

		if ($expirationType == PP_RECURRING_TRIAL_2) {
			return PP_PRICE_RECURRING_TRIAL_2;
		}

		return false;
	}

	/**
	 * Gets the recurrence count of the plan
	 *
	 * @see PayplansIfaceApiPlan::getRecurrenceCount()
	 *
	 * @return integer recurrence count value of the plan
	 */
	public function getRecurrenceCount()
	{
		return $this->getDetails()->get('recurrence_count', 1);
	}

	/**
	 * Gets the expiration type of the plan
	 *
	 * @see PayplansIfaceApiPlan::getExpirationType()
	 *
	 * @return string expiration type of the plan
	 */
	public function getExpirationType()
	{
		return $this->getDetails()->get('expirationtype', 'forever');
	}

	/**
	 * Retrieves a list of applicable payment providers for the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPaymentProviders()
	{
		$model = PP::model('Gateways');
		$gateways = $model->getPaymentGateways($this);

		// Ensure that there is a payment gateway attached to the plan
		if (!$this->isFree() && !$gateways) {
			throw new Exception(JText::_('COM_PAYPLANS_NO_APPLICATION_AVAILABLE_FOR_PAYMENT'));
		}

		return $gateways;
	}

	/**
	 * Gets the redirect url of the plan
	 *
	 * @see PayplansIfaceApiPlan::getRedirecturl()
	 */
	public function getRedirecturl()
	{
		return $this->getParams()->get('redirecturl','');
	}

	/**
	 * Retrieves a list of tokens available for token rewriting
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRewriterTokens()
	{
		$tokens = array(
			'PLAN_ID' => $this->getId(),
			'TITLE' => $this->getTitle(),
			'DESCRIPTION' => $this->getDescription()
		);

		return $tokens;
	}

	private function savePlanApps()
	{
		// delete all existing values of current plan id
		$model = PP::model('planapp');
		$model->deleteMany(array('plan_id' => $this->getId()));

		// insert new values into planapp for current plan id
		$data['plan_id'] = $this->getId();
		if (is_array($this->_planapps)) {
			foreach ($this->_planapps as $app) {
				$data['app_id'] = $app;
				$model->save($data);
			}
		}

		return $this;
	}

	private function savePlanGroups()
	{
		// delete all existing values of current plan id
		$model = PP::model('plangroup');
		$model->deleteMany(array('plan_id' => $this->getId()));

		// insert new values into planapp for current plan id
		$data['plan_id'] = $this->getId();
		if (is_array($this->_groups)) {
			foreach ($this->_groups as $group) {
				$data['group_id'] = $group;
				$model->save($data);
			}
		}

		return $this;
	}

	/**
	 * Deprecated. Use @isHighlighted
	 *
	 * @deprecated	4.0.0
	 */
	public function getPlanHighlighter()
	{
		return $this->isHighlighted();
	}

	/**
	 * Set plan modifier
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function setModifier($modifier)
	{
		$this->_modifier = $modifier;
	}

	/**
	 * Determine if this plan has a modifier or not
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function getModifier()
	{
		return $this->_modifier;
	}

	/**
	 * Set plan advPricing
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function setAdvPricing($advPricing)
	{
		$this->_advPricing = $advPricing;
	}

	/**
	 * Determine if this plan has a advPricing or not
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function getAdvPricing()
	{
		return $this->_advPricing;
	}
	
	/**
	 * Retrieve the expiration date of the plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getExpirationOnDate()
	{
		$date = $this->getParams()->get('expiration_date', '');
		$date = PP::date($date);

		return $date;
	}

	/**
	 * Retrieve the start date of the plan for fixed date expiration 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSubscriptionFromExpirationDate()
	{
		$date = $this->getParams()->get('subscription_from', '');
		$date = PP::date($date);

		return $date;
	}

	/**
	 * Retrieve the end date of the plan for fixed date expiration 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSubscriptionEndExpirationDate()
	{
		$date = $this->getParams()->get('subscription_to', '');
		$date = PP::date($date);

		return $date;
	}

	/**
	 * Determine if the fixed expiration date is enable
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isFixedExpirationDate()
	{
		return $this->getParams()->get('enable_fixed_expiration_date', '');
	}

	/**
	 * Retrieve price per day for this plan
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getPricePerDay()
	{
		// Get the actual price for each priceset
		// (planPrice/planDuration)
		$planDuration = PPHelperPlan::convertTimeArrayToDays($this->getExpiration());
		
		if ($this->isFree()) {
			return 0;
		}
		
		$pricePerDay = (int) $this->getPrice() / $planDuration;
		
		return $pricePerDay;
	}

}

class PayplansPlanxFormatter extends PayPlansFormatter
{
	function getIgnoredata()
	{
		$ignore = array('_trigger', '_component', '_name', '_errors','_blacklist_tokens');
		return $ignore;
	}

	// get formatter to apply on vars
	function getVarFormatter()
	{
		$rules = array('_planapps'    => array('formatter'=> 'PayplansAppFormatter',
											   'function' => 'getApplicableApps'),
						'_groups'     => array('formatter'=> 'PayplansGroupFormatter',
											   'function' => 'getPlanGroups'),
						'params'      => array('formatter'=> 'PayplansFormatter',
											   'function' => 'getFormattedParams'),
						'plan_id'     => array('formatter' => 'PayplansPlanFormatter',
												'function' => 'getPlanName'));
		return $rules;
	}

	function getPlanName($key,$value,$data)
	{
		if(!empty($value)){
			$planName = PayplansHelperPlan::getName($value);
			$value = PayplansHtml::link(XiRoute::_("index.php?option=com_payplans&view=plan&task=edit&id=".$value, false), $value.'('.$planName.')', array('target' => '_blank'));
		}
	}


}
