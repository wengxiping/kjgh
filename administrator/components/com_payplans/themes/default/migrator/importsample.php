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
<div class="row o-form-horizontal">
	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_MIGRATOR_IMPORT_NOTES'); ?>

			<div class="panel-body">
				<div class="alert alert-error mt-20">
					<?php echo JText::_('COM_PP_MIGRATOR_IMPORT_NOTES_EXPLANATION');?>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_MIGRATOR_IMPORT_SAMPLE_TYPE', 'typeId'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.lists', 'typeId', '', 'typeId', 'data-import-sample-type', $importTypes); ?>
					</div>
				</div>

				<div class="mt-20 text-right">
					<button class="btn btn-primary btn-sm" data-import-sample><?php echo JText::_('COM_PP_MIGRATOR_IMPORT_NOW'); ?></button>
				</div>
			</div>
		</div>
	</div>
</div>
