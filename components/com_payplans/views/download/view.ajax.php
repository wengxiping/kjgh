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

class PayPlansViewDownload extends PayPlansSiteView
{
	/**
	 * Allow users to submit a request to download their data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function request()
	{
		PP::requireLogin();

		// Ensure that this feature is available
		if (!$this->config->get('users_download')) {
			throw new Exception("Feture is not available");
		}
				
		$table = PP::table('Download');

		// Make sure that we don't have duplicates
		$table->load(array('user_id' => $this->my->id));

		$table->user_id = $this->my->id;
		$table->state = PP_DOWNLOAD_REQ_NEW;
		$table->created = PP::date()->toSql();

		$state = $table->store();

		return $this->resolve();
	}
}