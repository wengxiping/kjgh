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
		<div class="db-panel__hd-title"><?php echo JText::_('COM_PP_DASHBOARD_PLANS_OVERVIEW'); ?></div>
		<div class="db-panel__hd-text"><?php echo JText::_('COM_PP_DASHBAORD_PLANS_OVERVIEW_DESC'); ?></div>
	</div>

	<div data-dashboard-plans-tab class="is-loading" style="padding: 20px;">
		<div class="o-loader with-text"><?php echo JText::_('COM_PP_RETRIEVE_CHART_DATA'); ?></div>
		<div id="canvas-holder" style="min-height: 250px;">
			<canvas id="chart-area"></canvas>
		</div>

		<div class="o-empty" style="min-height:245px;">
			<div class="o-empty__content">
				<i class="o-empty__icon fas fa-exclamation-circle"></i>
				<div class="o-empty__text"><?php echo JText::_('COM_PP_CHART_NO_DATA');?></div>
			</div>
		</div>
	</div>
</div>
