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
<dialog>
	<width>800</width>
	<height>600</height>
	<selectors type="json">
	{
		"{closeButton}" : "[data-close-button]",
		"{copy}" : "[data-copy-clipboard]",
		"{clipboardMessage}": "[data-clipboard-message]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{closeButton} click": function() {
			this.parent.close();
		},

		"{copy} click": function(element) {
			var temp = PayPlans.$('<input>');
			var value = element.data('value');

			PayPlans.$('body').append(temp);
			temp.val(value).select();

			document.execCommand('copy');
			temp.remove();

			this.clipboardMessage().show();
			var self = this;

			this.clipboardMessage()
				.delay(1000)
				.fadeOut('slow');

		}
	}
	</bindings>
	<title><?php echo JText::_('Available Variables'); ?></title>
	<content>

		<div style="display: none;position: fixed;background: #000;bottom: 10%;left: 40%;color: #fff;padding: 5px 10px;border-radius: 20px;opacity: 0.9;" data-clipboard-message><?php echo JText::_('Copied to your clipboard');?></div>

		<ul class="nav nav-tabs t-lg-mb--xl" style="margin-left: 0;">
			<li class="active">
				<a href="#config" data-toggle="tab">
					<?php echo JText::_('Config'); ?>
				</a>
			</li>
			<li>
				<a href="#plan" data-toggle="tab">
					<?php echo JText::_('Plan'); ?>
				</a>
			</li>
			<li>
				<a href="#subscription" data-toggle="tab">
					<?php echo JText::_('Subscription'); ?>
				</a>
			</li>
			<li>
				<a href="#invoice" data-toggle="tab">
					<?php echo JText::_('Invoice'); ?>
				</a>
			</li>
			<li>
				<a href="#transaction" data-toggle="tab">
					<?php echo JText::_('Transaction'); ?>
				</a>
			</li>
			<li>
				<a href="#user" data-toggle="tab">
					<?php echo JText::_('User'); ?>
				</a>
			</li>
			<?php if ($apps) { ?>
				<?php foreach ($apps as $app) { ?>
					<?php echo $app[0]; ?>
				<?php } ?>
			<?php } ?>
		</ul>

		<div class="tab-content">
			<div class="tab-pane active" id="config">
				<?php echo $this->output('admin/rewriter/dialogs/table', array('data' => $items['CONFIG'])); ?>
			</div>

			<div class="tab-pane" id="plan">
				<?php echo $this->output('admin/rewriter/dialogs/table', array('data' => $items['PLAN'])); ?>
			</div>

			<div class="tab-pane" id="subscription">
				<?php echo $this->output('admin/rewriter/dialogs/table', array('data' => $items['SUBSCRIPTION'])); ?>
			</div>

			<div class="tab-pane" id="invoice">
				<?php echo $this->output('admin/rewriter/dialogs/table', array('data' => $items['INVOICE'])); ?>
			</div>

			<div class="tab-pane" id="transaction">
				<?php echo $this->output('admin/rewriter/dialogs/table', array('data' => $items['TRANSACTION'])); ?>
			</div>

			<div class="tab-pane" id="user">
				<?php echo $this->output('admin/rewriter/dialogs/table', array('data' => $items['USER'])); ?>
			</div>

			<?php if ($apps) { ?>
				<?php foreach ($apps as $app) { ?>
					<?php echo $app[1]; ?>
				<?php } ?>
			<?php } ?>
		</div>

	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-pp-default btn-sm"><?php echo JText::_('COM_PP_CLOSE_BUTTON'); ?></button>
	</buttons>
</dialog>