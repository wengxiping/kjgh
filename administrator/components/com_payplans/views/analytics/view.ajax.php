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

class PayPlansViewAnalytics extends PayPlansAdminView
{
	/**
	 * Display confirmation dialog to rebuild the statistics
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function confirmRebuildStat()
	{
		// Get total days to rebuild
		$statistics = PP::statistics();
		$totalDays = $statistics->getDaysToProcess();
		$rebuildLimit = $statistics->getRebuildLimit();

		$theme = PP::themes();
		$theme->set('totalDays', $totalDays);
		$theme->set('rebuildLimit', $rebuildLimit);
		$output = $theme->output('admin/analytics/dialogs/confirm.rebuildstat');

		$this->resolve($output);
	}
}
