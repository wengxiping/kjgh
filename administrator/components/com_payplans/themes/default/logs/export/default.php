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
				<?php echo $this->html('panel.heading', 'COM_PP_MIGRATOR_LOG_MIGRATION'); ?>

				<div class="panel-body">
					<?php echo JText::_('COM_PP_MIGRATOR_LOG_MIGRATION_EXPLANATION'); ?>
				</div>
			</div>
		</div>

		<div class="col-lg-6">
			<div class="panel" data-migration-result>
				<?php echo $this->html('panel.heading', 'Result', 'This section contains the result of the export process'); ?>

				<div class="panel-body">
					<ul class="list-unstyled" data-progress-list>
					</ul>
				</div>
			</div>
		</div>
	</div>

	<?php echo $this->html('form.action', 'log', 'export'); ?>
</form>