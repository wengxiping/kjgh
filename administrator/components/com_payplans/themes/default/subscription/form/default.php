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
	<div class="wrapper accordion">
		<div class="tab-box tab-box-alt">
			<div class="tabbable">

				<ul class="nav nav-tabs nav-tabs-icons">
					<li class="<?php echo !$activeTab ? 'active' : '';?>">
						<a href="#details" data-toggle="tab"><?php echo JText::_('COM_PP_DETAILS'); ?></a>
					</li>

					<?php foreach ($customDetails as $customDetail) { ?>
					<li class="<?php echo $activeTab == 'customdetails-' . $customDetail->id ? 'active' : '';?>">
						<a href="#customdetails-<?php echo $customDetail->id;?>" data-toggle="tab">
							<?php echo $customDetail->getTitle();?>
						</a>
					</li>
					<?php } ?>

					<?php if ($subscription->getId()) { ?>
					<li class="<?php echo $activeTab == 'invoices' ? 'active' : '';?>">
						<a href="#invoices" data-toggle="tab"><?php echo JText::_('COM_PP_INVOICES'); ?></a>
					</li>
					<li class="<?php echo $activeTab == 'transactions' ? 'active' : '';?>">
						<a href="#transactions" data-toggle="tab"><?php echo JText::_('COM_PP_TRANSACTIONS'); ?></a>
					</li>
					<li class="<?php echo $activeTab == 'logs' ? 'active' : '';?>">
						<a href="#logs" data-toggle="tab"><?php echo JText::_('COM_PP_LOGS'); ?></a>
					</li>
					<?php } ?>
				</ul>

				<div class="tab-content">
					<div id="details" class="tab-pane <?php echo !$activeTab ? 'active' : '';?>">
						<?php echo $this->output('admin/subscription/form/details'); ?>
					</div>

					<?php foreach ($customDetails as $customDetail) { ?>
					<div id="customdetails-<?php echo $customDetail->id;?>" class="tab-pane <?php echo $activeTab == 'customdetails-' . $customDetail->id ? 'active' : '';?>">
						<?php $output = $customDetail->renderForm($subscription->getParams()); ?>
						<?php if ($output === false) { ?>
							<div class="o-alert o-alert--error"><?php echo JText::_('COM_PP_CUSTOM_DETAILS_XML_ERROR'); ?></div>
						<?php } else { ?>
							<?php echo $output; ?>
						<?php } ?>
					</div>
					<?php } ?>

					<?php if ($subscription->getId()) { ?>
					<div id="invoices" class="tab-pane <?php echo $activeTab == 'invoices' ? 'active' : '';?>">
						<?php echo $this->output('admin/subscription/form/invoices'); ?>
					</div>

					<div id="transactions" class="tab-pane <?php echo $activeTab == 'transactions' ? 'active' : '';?>">
						<?php echo $this->output('admin/subscription/form/transactions'); ?>
					</div>

					<div id="logs" class="tab-pane <?php echo $activeTab == 'logs' ? 'active' : '';?>">
						<?php echo $this->output('admin/logs/default/default', array('logs' => $logs, 'form' => false, 'editable' => false, 'pagination' => false, 'renderFilterBar' => false, 'sortable' => false)); ?>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<?php echo $this->html('form.action', 'subscription', 'store'); ?>
	<?php echo $this->html('form.hidden', 'subscription_id', $subscription->getId()); ?>
</form>

