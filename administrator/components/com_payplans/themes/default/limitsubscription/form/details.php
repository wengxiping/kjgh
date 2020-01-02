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
					<?php echo $this->html('form.label', 'COM_PP_APP_LIMITSUBSCRIPTION_LIMIT'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.text', 'app_params[limit]', $appParams->get('limit')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_APP_LIMITSUBSCRIPTION_SUBSCRIPTION_STATUS'); ?>

					<div class="o-control-input">
						<?php echo $this->html('form.status', 'app_params[consider_status][]', $appParams->get('consider_status'), 'subscription', '', true, '', array(PP_NONE)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
