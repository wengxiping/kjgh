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
<div class="row">
	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_TRANSACTION_DETAILS'); ?>
	
			<div class="panel-body">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_ID', '', 5, false); ?>

					<div class="o-control-input col-md-7">
						<?php echo $payment->getId();?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_TRANSACTION_EDIT_GATEWAY_TYPE', '', 5, false); ?>

					<div class="o-control-input col-md-7">
						<?php echo $payment->gateway->getTitle();?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_TRANSACTION_EDIT_CREATED_DATE', '', 5, false); ?>

					<div class="o-control-input col-md-7">
						<?php echo PP::date($payment->getCreatedDate())->format(JText::_('DATE_FORMAT_LC2')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PAYPLANS_PAYMENT_EDIT_MODIFIED_DATE', '', 5, false); ?>

					<div class="o-control-input col-md-7">
						<?php echo PP::date($payment->getModifiedDate())->format(JText::_('DATE_FORMAT_LC2')); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-lg-6">
		<?php echo $this->output('admin/transaction/form/user', array('purchaser' => $payment->purchaser)); ?>
	</div>
</div>