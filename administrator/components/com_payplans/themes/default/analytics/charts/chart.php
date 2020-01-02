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
<div class="t-lg-p--xl">
	<div data-pp-analytics-chart-label></div>

	<div data-pp-analytics-chart class="is-loading">
		<div class="o-loader with-text"><?php echo JText::_('COM_PP_RETRIEVE_CHART_DATA'); ?></div>
		<canvas id="chart-revenue" style="max-width: 100%;height: 250px;"></canvas>
	</div>
</div>
