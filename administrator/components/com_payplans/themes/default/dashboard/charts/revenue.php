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
<div class="panel">
	<div class="db-panel__hd">
		<div class="db-panel__hd-title">
			<?php echo JText::_('Revenue'); ?>
			<a href="index.php?option=com_payplans&view=analytics&layout=sales" class="btn btn-pp-default-o t-lg-pull-right t-lg-mt--md">
				<?php echo JText::_('COM_PP_VIEW_MORE');?>
			</a>
		</div>
		<div class="db-panel__hd-text"><?php echo JText::_('This is an overview of your revenue for the past 7 days'); ?></div>
	</div>

	<div data-dashboard-content-tab class="is-loading" style="padding: 20px;">
		<div class="o-loader with-text"><?php echo JText::_('COM_PP_RETRIEVE_CHART_DATA'); ?></div>
		<canvas id="chart-revenue" style="height: 250px;"></canvas>
	</div>
</div>
