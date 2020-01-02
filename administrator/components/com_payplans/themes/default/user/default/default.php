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
<form method="post" name="adminForm" id="adminForm" data-table-grid>
	<div class="app-filter-bar">
		 <div class="app-filter-bar__cell">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.search', $states->search); ?>
			</div>
		</div>

		<?php if ($this->tmpl != 'component') { ?>
		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.plans', 'plan_id', $states->plan_id, array()); ?>
			</div>
		</div>
		<?php } ?>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit', $states->limit); ?>
			</div>
		</div>
	</div>
		

	<div class="panel-table">
		<table class="app-table table table-striped">
			<thead>
				<tr>
					<?php if ($this->tmpl != 'component') { ?>
						<th width="1%" class="center">
							<?php echo $this->html('grid.checkAll'); ?>
						</th>
					<?php } ?>
					
					<th>
						<?php echo $this->html('grid.sort', 'name', 'COM_PP_TABLE_COLUMN_NAME', $states); ?>
					</th>
					
					<th width="15%">
						<?php echo $this->html('grid.sort', 'username', 'COM_PP_TABLE_COLUMN_USERNAME', $states); ?>
					</th>

					<th width="15%">
						<?php echo $this->html('grid.sort', 'email', 'COM_PP_TABLE_COLUMN_EMAIL', $states); ?>
					</th>

					<?php if ($this->tmpl != 'component') { ?>
					<th class="center" width="10%">
						<?php echo JText::_('COM_PP_TABLE_COLUMN_PLANS');?>
					</th>
					<?php } ?>

					<th width="5%" class="center">
						<?php echo $this->html('grid.sort', 'id', 'COM_PP_TABLE_COLUMN_ID', $states); ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if ($users) { ?>
					<?php $i = 0; ?>
					<?php foreach ($users as $user) { ?>
					<tr>
						<?php if ($this->tmpl != 'component') { ?>
							<th class="center">
								<?php echo $this->html('grid.id', $i, $user->getId()); ?>
							</th>
						<?php } ?>

						<td>
							<a href="index.php?option=com_payplans&view=user&layout=form&id=<?php echo $user->getId();?>" 
								data-pp-user-item
								data-title="<?php echo $this->html('string.escape', $user->getDisplayName());?>"
								data-id="<?php echo $user->getId();?>"
							>
								<?php echo $user->getDisplayName();?>
							</a>
						</td>

						<td>
							<?php echo $user->getUserName();?>
						</td>

						<td>
							<?php echo $user->getEmail();?>
						</td>

						<?php if ($this->tmpl != 'component') { ?>
							<?php $totalPlans = 0; ?>
							<?php foreach ($user->getSubscriptions() as $subscription) { ?>
								<?php $order = $subscription->getOrder(); ?>
								<?php if ($order->getStatus() != 0 ) { ?>
									<?php $totalPlans++; ?>
								<?php } ?>
							<?php } ?>
							<td class="center">
								<a href="index.php?option=com_payplans&view=user&layout=form&activeTab=subscriptions&id=<?php echo $user->getId();?>">
									<?php echo JText::sprintf('COM_PP_VIEW_PLANS', $totalPlans);?>
								</a>
							</td>
						<?php } ?>
						
						<td class="center">
							<?php echo $user->getId();?>
						</td>
					</tr>
					<?php $i++; ?>
					<?php } ?>
				<?php } ?>

				<?php if (!$users) { ?>
					<?php echo $this->html('grid.emptyBlock', 'COM_PAYPLANS_ADMIN_BLANK_USER_MSG', $this->tmpl == 'component' ? 5 : 6); ?>
				<?php } ?>
			</tbody>

			<?php echo $this->html('grid.pagination', $pagination, $this->tmpl == 'component' ? 5 : 6); ?>
		</table>
	</div>

	<?php echo $this->html('form.action', 'subscription'); ?>
	<?php echo $this->html('form.ordering', $states->ordering, $states->direction); ?>
	<?php echo $this->html('form.hidden', 'apply_plan_id', '', array('data-apply-plan-id' => '')); ?>
</form>
 
