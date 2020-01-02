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
 * These functions are listed for User object 
 * @author bhavya
 *
 */
interface PayplansIfaceApiUser
{
	/**
	 * Gets the subscription of the user with mentioned subscription status
	 * 
	 * @param integer $status status of the subscription
	 */
	public function getSubscriptions($status=NULL);
	
	/**
	 * @return string zipcode of the user's country
	 */
	public function getZipcode();
	
	/**
	 * @return object PayplansUser Instance of PayplansUser
	 * @param  string $zipcode The zipcode of the user's country
	 */
	public function setZipcode($zipcode ='');
	
	/**
	 * Gets the country user belongs to
	 * @return integer  User's country
	 */
	public function getCountry();
	
	/**
	 * @return PayplansUser instance
	 * @param interger $country The country of user
	 */
	public function setCountry($country = '');
	
	/**
	 * @return string User's city
	 */
	public function getCity();
	
	/**
	 * @return PayplansUser instance
	 * @param  string $city The city user belongs to
	 */
	public function setCity($city = '');
	
	/**
	 * @return string User's state 
	 */
	public function getState();
	
	/**
	 * @return PayplansUser instance
	 * @param  string $state The state user belongs to
	 */
	public function setState($state ='');
	
	/**
	 * @return string User's address
	 */
	public function getAddress();
	
		/**
	 * @return PayplansUser instance
	 * @param  string $address The address of the user
	 */
	public function setAddress($address ='');
	
	/**
	 * @return string Joomla usertype of the user 
	 */
	public function getUsertype();
	
	/**
	 * @return string User's email address
	 */
	public function getEmail();
	
	/**
	 * @return string Username of the user
	 */
	public function getUsername();
	
	/**
	 * @return string Registeration date of the user
	 */
	public function getRegisterDate();
	
	/**
	 * @return object XiParameter Instance of type XiParameter indicating preference of the user 
	 * @param string $key The name of the key
	 * @param $default false
	 */
	public function getPreference($key = null, $default = false);
	
	/**
	 * @return object PayplansUser Instance of PayplansUser
	 * @param string $key The name of the key
	 * @param mixed  $value The value of the key to set
	 */
	public function setPreference($key, $value);
	
	/**
	 * @return boolean True when user is Super Administrator
	 * else return false
	 */
	public function isAdmin();
}
