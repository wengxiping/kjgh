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
		<?php echo $this->output('admin/app/generic/form', array('app' => $app)); ?>
	</div>

	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_APP_PARAMETERS'); ?>

			<div class="panel-body">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_APP_UPGRADE_UPGRADE_TO'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.plans', 'app_params[upgrade_to]', $appParams->get('upgrade_to'), true, true); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_APP_UPGRADE_IS_TRIAL_ALLOWED'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.toggler', 'app_params[willTrialApply]', $appParams->get('willTrialApply', true)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
