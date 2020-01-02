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

require_once(__DIR__ . '/lib.php');

class PPHelperJEvents extends PPHelperStandardApp
{
	protected $resource = 'com_jevents.user';
	static $properties = array(
							'published', 
							'cancreate', 
							'eventslimit', 
							'canpublishown', 
							'candeleteown', 
							'canedit', 
							'canpublishall',
							'candeleteall',
							'canuploadimages',
							'canuploadmovies',
							'cancreateown',
							'cancreateglobal',
							'extraslimit',
							'categories'
						);

	public function __construct($params = null, $app = null)
	{
		parent::__construct($params, $app);

		$this->lib = new PPJEvents();
	}	

	/**
	 * Retrieves ACL on the app
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAcl()
	{
		$data = array();

		foreach (self::$properties as $key) {
			$data[$key] = $this->params->get($key, 0);
		}

		return $data;
	}

	/**
	 * Retrieves default ACL
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDefaultAcl()
	{
		$data = array();

		foreach (self::$properties as $key) {

			if ($key == 'categories') {
				$data[$key] = "";
				continue;
			}
			$data[$key] = 0;
		}

		return $data;
	}

	/**
	 * Insert ACL in JEvents for a user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function addAcl(PPSubscription $subscription, PPUser $user)
	{
		$access = $this->getAcl();

		if ($this->lib->isUserExists($user->getId())) {
			// Merge the ACL
			$access = $this->lib->mergeAcl($user->getId(), $access);

			$this->lib->updateAcl($user->getId(), $access);

			$this->addResource($subscription->getId(), $user->getId(), $access['categories'], $this->resource, $access['eventslimit']);

			return true;
		}

		//If there is more than 1 category
		if (is_array($access['categories'])) {
			$access['categories'] = implode('|', $access['categories']);
		}

		// Here we assume that the user doesn't exist on JEvents table
		$state = $this->lib->insertAcl($user->getId(), $access);

		$this->addResource($subscription->getId(), $user->getId(), $access['categories'], $this->resource, $access['eventslimit']);

		return true;
	}

	/**
	 * Revoke privileges from user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function revoke(PPSubscription $subscription, PPUser $user)
	{
		if (!$this->lib->isUserExists($user->getId())) {
			return;
		}

		// Once refunded, get default values
		$defaultAcl = $this->getDefaultAcl();

		$state = $this->lib->updateAcl($user->getId(), $defaultAcl);
		
		if ($state) {
			return $this->addResource($subscription->getId(), $user->getId(), $defaultAcl['categories'], $this->resource, $defaultAcl['eventslimit']);
		}

		return false;
	}
}
