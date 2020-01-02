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
defined('_JEXEC') or die('Unauthorized Access');

class PPRegistrationAuto extends PPRegistrationAbstract
{
	public $type = 'auto';
	
	public function html()
	{
		$twoFactors = JAuthenticationHelper::getTwoFactorMethods();
		$showTwoFactor = count($twoFactors) > 1;

		$this->set('twoFactors', $twoFactors);
		$this->set('showTwoFactor', $showTwoFactor);

		return $this->display('default');
	}

	/**
	 * Set necessary parameters before redirecting
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function beforeStartRedirection()
	{
	}

	/**
	 * Overrides the behavior of determinining if site requires activation 
	 *
	 * @since	4.0.3
	 * @access	public
	 */
	public function requireActivation()
	{
		$registrationType = $this->config->get('account_verification');

		if ($registrationType != 'user') {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the registration requires admin for approval
	 *
	 * @since	4.0.7
	 * @access	public
	 */
	public function requireAdminActivation()
	{
		$registrationType = $this->config->get('account_verification');

		if ($registrationType != 'admin') {
			return false;
		}

		return true;
	}
}