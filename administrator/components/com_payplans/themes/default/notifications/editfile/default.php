<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
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
		<div class="col-lg-5">
			<div class="panel">
				<?php echo $this->html('panel.heading', 'COM_PP_NOTIFICATIONS_INFORMATION'); ?>

				<div class="panel-body">
					<div class="o-form-group">
						<?php echo $this->html('form.label', 'COM_PP_FILENAME', '', 3); ?>

						<div class="o-control-input col-md-7">
							<?php echo $this->html('form.text', 'filename', $data->name); ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-7">
			<div class="panel">
				<div class="panel-body">
					<div class="o-form-group">
						<?php echo $editor->display('source', $data->contents, '100%', '400px', 80, 20, false, null, null, null, array('syntax' => 'php', 'filter' => 'raw')); ?>
					</div>

					<div class="o-form-group">
						<div class="o-control-input col-md-9">
							<?php echo $this->html('form.rewriter'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php echo $this->html('form.hidden', 'file', $data->relative ? base64_encode($data->relative) : '');?>
	<?php echo $this->html('form.action', 'notifications'); ?>
</form>