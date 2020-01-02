<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
if(defined('_JEXEC')===false) die();


/**
 * These functions are listed for Plan object
 *
 */
interface PayplansIfaceApiPlan
{

	/**
	 * Gets the title of the plan
	 *
	 * @return string Title of the plan
	 */
	public function getTitle();

	/**
	 * Returns the price of plan with different expiration types
	 *
	 * @param integer $type  A constant indicating expiration type
	 *
	 * if type is not set then return the regular/normal price
	 * if type is set to RECURRING_TRIAL_1 then return first trial price
	 * if type is set to RECURRING_TRIAL_2 then return second trial price
	 *
	 * @return float  Price of the plan
	 */
	public function getPrice($type = null);

	/**
	 * Gets plan expiration time
	 *
	 * @return Array  An array containing expiration values for year, month, day and so on
	 */
	public function getExpiration();

	/**
	 * Sets the expiration time(regular expiration time) of the plan
	 *
	 * @param  string $time  12 digits numeric string each 2 digits denotes the
	 * 						 value for year, month, day, hour, minute and second in the
	 * 						 same sequence, starting from year(starting 2 digits indicate year)
	 *
	 * @return object PayplansPlan  Instance of PayplansPlan
	 */
	public function setExpiration($time);

	/**
	 * Gets the currency of the plan
	 *
	 * @param  string $format  An optional parameter to get the currency in different format.
	 * Available formats are isocode, symbol, fullname
	 *
	 * @return string  currency of the plan in desired format
	 */
	public function getCurrency($format = null);

	/**
	 * Setup an Order and subscription object for the User id
	 * It will update Buyer = $userId
	 * For Subscription object, it will update
	 * Plan = current plan
	 * Order = order created for this subscription
	 *
	 * @param integer $userId  for which order and subscription is to be created
	 *
	 * @return object  PayplansOrder Instance of PayplansOrder
	 */
	public function subscribe($userId);

	/**
	 * Gets group the plan is attached with
	 *
	 * @return Array  array of group id
	 */
	public function getGroups();

	/**
	 * Gets published status of the plan
	 *
	 * @return boolean True if plan is published
	 */
	public function getPublished();

	/**
	 * Gets plan visibility
	 *
	 * @return boolean True if plan is visible
	 */
	public function getVisible();

	/**
	 * Gets the description of the plan
	 *
	 * @param boolean $descriptionFormat  Trigger Joomla events if true
	 *
	 * @return string The description of the plan
	 */
	public function getDescription($descriptionFormat = false);

	/**
	 * Gets the app ids the plan is attached with
	 *
	 * @return Array  Array of app id
	 */
	public function getPlanapps();

	/**
	 * Gets the teaser-text of the plan
	 * 		Teaser-text is one line description of the plan
	 *
	 * @return string  teaser text of the plan
	 */
	public function getTeasertext();

	/**
	 * Is plan reccuring ?
	 * Available constants are PP_RECURRING for recurring,
	 * 						   PP_RECURRING_TRIAL_1 for recurring with 1 trial
	 * 						   PP_RECURRING_TRIAL_2 for recurring with 2 trials
	 *
	 * @return mixed  Integer constant if plan is of recurring type else False
	 */
	public function isRecurring();

	/**
	 * Gets the recurrence count of the plan.
	 * Recurrence count indicates the number of times the plan will recur
	 * 		Special case : 0 indicates lifetime recurring
	 * This parameters is not applicable to fixed and forever type of plans

	 * @return integer  Value of the recurrence count set for the current plan
	 */
	public function getRecurrenceCount();

	/**
	 * Gets the expiration type of the plan
	 * Possible result : forever, fixed, recurring_trial_1, recurring_trial_2
	 *
	 * @return string expiration type of the plan
	 */
	public function getExpirationType();

	/**
	 * Gets the url where user will be redirect to after completing subscription.
	 *
	 * @return string  Value of the url where the user is redirected to
	 */
	public function getRedirecturl();

	/**
	 * Gets the details of the plan
	 *
	 * Plan Details include expiration type, expiration time,
	 * price, currecny, trial time, trial price, recurrence count etc
	 *
	 * @return object XiParameter Instance of XiParameter
	 */
	public function getDetails();

	/**
	 * Gets the css classes which will be applied on the current plan while displaying it at frontend
	 * @return string css-class applied on the current plan
	 */
	public function getCssClasses();
}
