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
<div class="panel">
	<?php echo $this->html('panel.heading', 'COM_PP_APP_GENERAL'); ?>

	<div class="panel-body">
		<div class="o-form-group">
			<?php echo $this->html('form.label', 'COM_PP_APP_GENERAL_TITLE', '', 3, true, true); ?>

			<div class="o-control-input">
				<?php echo $this->html('form.text', 'title', $app->getTitle()); ?>
			</div>
		</div>

		<div class="o-form-group">
			<?php echo $this->html('form.label', 'COM_PP_APP_GENERAL_PUBLISH_STATE'); ?>

			<div class="o-control-input">
				<?php echo $this->html('form.toggler', 'published', $app->getPublished()); ?>
			</div>
		</div>

		<div class="o-form-group">
			<?php echo $this->html('form.label', 'COM_PP_APP_GENERAL_APPLY_ON_ALL_PLANS'); ?>

			<div class="o-control-input">
				<?php echo $this->html('form.toggler', 'core_params[applyAll]', $app->getId() ? $app->getApplyAll() : true, 'core_params[applyAll]', array('data-app-all-plans' => '')); ?>
			</div>
		</div>

		<div class="o-form-group <?php echo $app->getApplyAll() ? 't-hidden' : ''; ?>" data-app-selected-plans>
			<?php echo $this->html('form.label', 'COM_PP_APP_GENERAL_APPLY_ON_SELECTED_PLANS'); ?>

			<div class="o-control-input">
				<?php echo $this->html('form.plans', 'appplans', $app->getPlans(), true, true, array('data-plans-input' => '')); ?>
			</div>
		</div>

		<div class="o-form-group">
			<?php echo $this->html('form.label', 'COM_PP_APP_GENERAL_DESCRIPTION'); ?>

			<div class="o-control-input">
				<?php echo $this->html('form.textarea', 'description', $app->getDescription(), '', array('rows' => 5)); ?>
			</div>
		</div>
	</div>

</div>