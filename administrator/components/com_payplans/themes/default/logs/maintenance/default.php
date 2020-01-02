<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
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
				<?php echo $this->html('panel.heading', 'Legacy Files', 'PayPlans detected several legacy files below and they need to be converted to a proper and safe file format'); ?>

				<div class="panel-body">
					<?php foreach ($files as $file) { ?>
						<code style="padding: 2px;display:block;margin-bottom: 10px;"><?php echo $file; ?></code>
					<?php } ?>
				</div>
			</div>
		</div>

		<div class="col-lg-6">
			<div class="panel" data-migration-result>
				<?php echo $this->html('panel.heading', 'Result', 'This section contains the result of the export process'); ?>

				<div class="panel-body">
					<p class="t-hidden" data-result-complete>
						Maintenance completed. If you are still redirected to this page and are seeing the same list of files, please ensure that the permission of the files is writable by the server.<br /><br />

						Alternatively, please contact our <a href="https://stackideas.com/forums" target="_blank">support team for any assistance</a>.

					</p>
				</div>
			</div>
		</div>
	</div>

	<?php echo $this->html('form.action', 'log', 'fixLegacy'); ?>
</form>