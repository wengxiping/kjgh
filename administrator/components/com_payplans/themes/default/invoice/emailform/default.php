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
<form method="post" id="adminForm" class="o-form-horizontal" data-form>
	<div class="row">
		<div class="col-lg-5">
			<div class="panel">
				<?php echo $this->html('panel.heading', 'Recipient Details', 'Enter the recipient details'); ?>

				<div class="panel-body">
					<div class="o-form-group">
						<label class="o-control-label">
							<?php echo JText::_('Recipient'); ?>
						</label>

						<div class="o-control-input col-md-9">
							<?php echo $this->html('form.text', 'recipient', $recipient, 'recipient'); ?>
						</div>
					</div>

					<div class="o-form-group">
						<label class="o-control-label">
							<?php echo JText::_('COM_PP_INVOICE_EDIT_SUBJECT'); ?>
						</label>

						<div class="o-control-input col-md-9">
							<?php echo $this->html('form.text', 'subject', 'An invoice is available to be viewed', 'title'); ?>
						</div>
					</div>

					<div class="o-form-group">
						<label class="o-control-label">
							<?php echo JText::_('CC'); ?>
						</label>

						<div class="o-control-input col-md-9">
							<?php echo $this->html('form.text', 'cc', '', 'cc'); ?>
						</div>
					</div>

					<div class="o-form-group">
						<label class="o-control-label">
							<?php echo JText::_('BCC'); ?>
						</label>

						<div class="o-control-input col-md-9">
							<?php echo $this->html('form.text', 'bcc', '', 'bcc'); ?>
						</div>
					</div>

					<div class="o-form-group">
						<label class="o-control-label">
							<?php echo JText::_('Attach Invoice'); ?>
						</label>

						<div class="o-control-input col-md-9">
							<?php echo $this->html('form.toggler', 'attach_invoice', true, 'attach_invoice'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-7">
			<div class="panel">
				<?php echo $this->html('panel.heading', 'E-mail Contents', 'In this section, you can enter the contents that should be included in the e-mail'); ?>

				<div class="panel-body">
					<div class="o-form-group">
						<?php echo $editor->display('contents', nl2br(JText::_('COM_PAYPLANS_INVOICE_EMAIL_LINK_BODY')), '100%', '200', '60', '20' ) ;?>
					</div>

					<div class="o-form-group">
						<?php echo $this->html('form.rewriter'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php echo $this->html('form.action', 'invoice', 'sendEmail'); ?>
	<?php echo $this->html('form.hidden', 'invoice_id', $invoice->getId()); ?>
	<?php echo $this->html('form.hidden', 'return', base64_encode($return)); ?>
</form>