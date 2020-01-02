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
<form class="o-form-horizontal" name="adminForm" id="adminForm" class="pointsForm" method="post" enctype="multipart/form-data">
	<div class="row">
		<div class="col-lg-6">
			<div class="panel">
				<?php echo $this->html('panel.heading', 'COM_PP_HEADING_REPORTS_PDF_INVOICE'); ?>

				<div class="panel-body">

					<div class="o-form-group">
						<?php echo $this->html('form.label', 'COM_PP_REPORTS_PDF_INVOICE_TYPE'); ?>

						<div class="o-control-input col-md-7">
							<?php echo $this->html('form.lists', 'type', '', 'type', 'data-export-invoice-type', $exportTypes); ?>
						</div>
					</div>

					<div class="o-form-group" data-invoice-key>
						<?php echo $this->html('form.label', 'COM_PP_REPORTS_PDF_INVOICE_KEY'); ?>

						<div class="o-control-input col-md-7">
							<?php echo $this->html('form.text', 'invoice_key', ''); ?>
						</div>
					</div>

					<div class="o-form-group t-hidden" data-invoice-transactiondate>
						<?php echo $this->html('form.label', 'COM_PP_EXPORT_PDF_TRANSACTION_DATE_RANGE'); ?>
						
						<div class="o-control-input col-md-7">
							<?php echo $this->html('form.dateRange'); ?>
						</div>
					</div>

					<div class="o-form-group t-hidden" data-invoice-limit>
						<?php echo $this->html('form.label', 'COM_PP_EXPORT_REPORTS_LIMIT'); ?>

						<div class="o-control-input col-md-7">
							<?php echo $this->html('form.text', 'limit', '50', '', '', array('class' => 't-text--center', 'size' => 8, 'postfix' => 'Items')); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php echo $this->html('form.action', 'reports', 'downloadPdf'); ?>
</form>
