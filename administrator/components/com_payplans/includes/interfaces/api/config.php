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
 * These functions are listed for Config object.
 * @author JPayplans
 */

class PayplansIfaceApiConfig
{
	/**
	 * Gets the title of Configuration setting for Payplans.
	 * Configuration setting Titles are used to show tabs like Advance, Expert and so on at Configuration screen of Payplans.
	 * 
	 * @return String String indicating one of from Basic/ Advance/ Invoice/ Expert/ Customization.
	 */	
	public function getTitle();
	
	
	/**
	 * Gets Key of Configuration setting.
	 * 
	 * @return String Return one of the following options: payplans_basic/ payplans_advance/ payplans_invoice/ payplans_expert/ payplans_customization.
	 */
	public function getKey();

	
	/**
	 * Gets Path for the required key.
	 *
	 * @return String Path of the XML file like components/com_payplans/libraries/model/xml/config.[payplans_basic] or [payplans_advance] or [payplans_invoice] or [payplans_expert] or [payplans_customization]
	 *
	 */		
	public function getPath();
	
	
	/**
	 * Gets configuration params object for the current instance of config
	 * such as params for expert configuration are 
	 * 		expert_use_jquery
	 *		expert_use_jqueryui
	 *		expert_useminjs
	 *		expert_encryption_key
	 *		expert_wait_for_payment
	 *		expert_run_automatic_cron
	 *		expert_auto_delete	
	 *
	 * @return object XiParameter XiParameter type object containing configuration params
	 */
	public function getConfig();

	
	/**
	 * Gets Component name.
	 * 
	 * @return String Curently it returns "payplans", but there is possibilty of other values also
	 */
	public function getComponentname();
	
}