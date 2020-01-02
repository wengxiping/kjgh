<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
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
					<li class="<?php echo !$activeTab || $activeTab == 'details' ? 'active' : '';?>">
						<a href="#details" data-toggle="tab" data-id="details"><?php echo JText::_('COM_PP_DETAILS'); ?></a>
					</li>
					<li class="<?php echo $activeTab == 'transactions' ? 'active' : '';?>">
						<a href="#transactions" data-toggle="tab" data-id="transactions"><?php echo JText::_('COM_PP_TRANSACTIONS'); ?></a>
					</li>
					<li class="<?php echo $activeTab == 'logs' ? 'active' : '';?>">
						<a href="#logs" data-toggle="tab" data-id="logs"><?php echo JText::_('COM_PP_LOGS'); ?></a>
					</li>
				</ul>

				<div class="tab-content">
					<div id="details" class="tab-pane <?php echo !$activeTab || $activeTab == 'details' ? 'active' : '';?>">
						<?php echo $this->output('admin/payment/form/details'); ?>
					</div>

					<div id="transactions" class="tab-pane <?php echo $activeTab == 'transactions' ? 'active' : '';?>">
						<?php echo $this->output('admin/payment/form/transactions'); ?>
					</div>

					<div id="logs" class="tab-pane <?php echo $activeTab == 'logs' ? 'active' : '';?>">
						<?php echo $this->output('admin/logs/default/default', array('logs' => $logs, 'pagination' => false, 'editable' => false, 'renderFilterBar' => false, 'sortable' => false, 'form' => false)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php echo $this->html('form.action', 'payment', 'store'); ?>
	<?php echo $this->html('form.hidden', 'payment_id', $payment->getId()); ?>
	<?php echo $this->html('form.activeTab', $activeTab); ?>
</form>