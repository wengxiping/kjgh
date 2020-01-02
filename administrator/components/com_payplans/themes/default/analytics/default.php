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
?>
<form method="post" name="adminForm" id="adminForm">

	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<div class="app-filter-bar__search-input-group">
				<?php echo $this->html('filter.daterange', $dateRange); ?>
			</div>
		</div>
		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left"></div>
	</div>

	<div id="pp-analytics-stats" class="pp-dashboard-display">
		<?php echo $this->output('admin/analytics/charts/default'); ?>
	</div>

	<?php echo $this->html('form.action', 'analytics'); ?>
</form>
