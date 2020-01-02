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
<div class="panel-chart-label t-lg-mb--lg">
	<div class="o-media">
		<div class="o-media__image o-media--top">
			<div class="db-icon db-icon--3">
				<i class="fas fa-user"></i>
			</div>
		</div>
		<div class="o-media__body">
			<div>
				<?php echo JText::_('DASHBOARD_STATISTICS_ACTIVE_SUBSCRIPTIONS'); ?>
			</div>
			<div class="panel-chart-label__amount">
				<b><?php echo $totalActive; ?></b>
			</div>
		</div>
	</div>
	<div class="o-media">
		<div class="o-media__image o-media--top">
			<div class="db-icon db-icon--4">
				<i class="fas fa-user"></i>
			</div>
		</div>
		<div class="o-media__body">
			<div>
				<?php echo JText::_('DASHBOARD_STATISTICS_EXPIRE_SUBSCRIPTIONS'); ?>
			</div>
			<div class="panel-chart-label__amount">
				<b><?php echo $totalExpire; ?></b>
			</div>
		</div>
	</div>
</div>