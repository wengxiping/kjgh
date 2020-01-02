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
<div class="panel-charts">
	<div class="o-grid">
		<div class="o-grid__cell <?php echo $type != 'growth' ? ' o-grid__cell--2of3' : ''; ?>">
			<div class="data-line-chart" data-line-chart>
				<?php echo $this->output('admin/analytics/charts/chart'); ?>
			</div>
		</div>

		<?php if ($type != 'growth') { ?>
		<div class="o-grid__cell o-grid__cell--1of3" style="border-left: 1px solid #DEE3E9">
			<div class="" data-plan-chart>
				<?php echo $this->output('admin/analytics/charts/plans'); ?>	
			</div>
			
		</div>
		<?php } ?>
	</div>
</div>

<div class="panel-table t-lg-mt--no" data-chart-listings></div>