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
<form class="o-form-horizontal" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" data-pp-form>

	<div class="row">
		<div class="col-lg-6">
			<div class="panel">
				<?php echo $this->html('panel.heading', 'COM_PP_TRANSACTION_DETAILS'); ?>
		
				<div class="panel-body">
					<div class="o-form-group">
						<?php echo $this->html('form.label', 'COM_PP_ID', '', 5, false); ?>

						<div class="o-control-input col-md-7">
							<?php echo $resource->resource_id;?>
						</div>
					</div>
					
					<div class="o-form-group">
						<?php echo $this->html('form.label', 'Value', '', 5, false); ?>

						<div class="o-control-input col-md-7">
							<?php echo $resource->value;?>
						</div>
					</div>

					<div class="o-form-group">
						<?php echo $this->html('form.label', 'Title', '', 5, false); ?>

						<div class="o-control-input col-md-7">
							<?php echo $resource->title;?>
						</div>
					</div>

					<div class="o-form-group">
						<?php echo $this->html('form.label', 'Subscriptions', '', 5, false); ?>

						<div class="o-control-input col-md-7">
							<?php echo $this->html('form.usersubscriptions', 'subscription_ids', $resource->subscription_ids, '', '', $resource->user_id); ?>
						</div>
					</div>

					<div class="o-form-group">
						<?php echo $this->html('form.label', 'Count', '', 5, false); ?>

						<div class="o-control-input col-md-7">
							<?php echo $this->html('form.text', 'count', $resource->count, '', '', array('class' => 't-text--center', 'size' => 8)); ?>
						</div>
					</div>

					<div class="o-form-group">
						<?php echo $this->html('form.label', 'User', '', 5, false); ?>

						<div class="o-control-input col-md-7">
							#<?php echo $resource->user_id;?> (<?php echo $user->getName();?> &mdash; <?php echo $user->getEmail();?>)
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-6">
			&nbsp;
		</div>
	</div>

	<?php echo $this->html('form.action', 'resource', 'store'); ?>
	<?php echo $this->html('form.hidden', 'id', $resource->resource_id); ?>
</form>