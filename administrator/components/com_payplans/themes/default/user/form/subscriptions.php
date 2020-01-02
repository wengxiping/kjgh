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
<div class="panel-table">
	<table class="app-table table">
		<thead>
			<tr>
				<th width="10%">
					&nbsp;
				</th>

				<th class="center">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_PLAN'); ?>
				</th>

				<th class="center" width="10%">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_STATUS'); ?>
				</th>

				<th class="center" width="15%">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_SUBSCRIPTION_DATE'); ?>
				</th>

				<th class="center" width="15%">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_EXPIRE_DATE'); ?>
				</th>
				
				<th class="center" width="1%">
					<?php echo JText::_('COM_PP_TABLE_COLUMN_ID'); ?>
				</th>
			</tr>
		</thead>

		<tbody>
			<?php if ($subscriptions) { ?>
				<?php foreach ($subscriptions as $subscription) { ?>
					<?php $order = $subscription->getOrder();?>
					<?php if ($order->getStatus() == 0 ) {
						continue;
					} ?>
					<tr>
						<td>
							<a href="index.php?option=com_payplans&view=subscription&layout=form&id=<?php echo $subscription->getId();?>">
								<?php echo $subscription->getKey(); ?>
							</a>
						</td>
						<td class="center">
							<?php echo $subscription->getPlan()->getTitle();?>
						</td>
						<td class="center">
							<span class="o-label <?php echo $subscription->getStatusLabelClass();?>"><?php echo $subscription->getLabel();?></span>
						</td>
						<td class="center">
							<?php if ($subscription->getSubscriptionDate()) { ?>
								<?php echo $subscription->getSubscriptionDate();?>
							<?php } else { ?>
								&mdash;
							<?php } ?>
						</td>
						<td class="center">
							<?php if ($subscription->getExpirationDate()) { ?>
								<?php echo $subscription->getExpirationDate();?>
							<?php } else { ?>
								&mdash;
							<?php } ?>
						</td>
						<td class="center">
							<?php echo $subscription->getId();?>
						</td>
					</tr>
				<?php } ?>
			<?php } ?>

			<?php if (!$subscriptions) { ?>
				<?php echo $this->html('grid.emptyBlock', 'COM_PP_USER_EMPTY_SUBSCRIPTIONS', 6); ?>
			<?php } ?>
		</tbody>
	</table>
</div>