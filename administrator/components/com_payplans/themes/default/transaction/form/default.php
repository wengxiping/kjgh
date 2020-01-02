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
<form class="o-form-horizontal" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<div class="wrapper accordion">
		<div class="tab-box tab-box-alt">
			<div class="tabbable">

				<ul class="nav nav-tabs nav-tabs-icons">
					<li class="<?php echo !$activeTab ? 'active' : '';?>">
						<a href="#details" data-toggle="tab"><?php echo JText::_('COM_PP_DETAILS'); ?></a>
					</li>

					<?php if ($transactionParams) { ?>
					<li class="<?php echo $activeTab == 'params' ? 'active' : '';?>">
						<a href="#params" data-toggle="tab"><?php echo JText::_('COM_PP_TRANSACTION_DATA'); ?></a>
					</li>
					<?php } ?>
				</ul>

				<div class="tab-content">
					<div id="details" class="tab-pane <?php echo !$activeTab ? 'active' : '';?>">
						<?php echo $this->output('admin/transaction/form/details'); ?>
					</div>

					<?php if ($transactionParams) { ?>
					<div id="params" class="tab-pane <?php echo $activeTab == 'params' ? 'active' : '';?>">
						<?php echo $this->output('admin/transaction/form/params'); ?>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>

	<?php echo $this->html('form.action', 'transaction'); ?>
	<?php echo $this->html('form.hidden', 'from', base64_encode($from)); ?>
</form>