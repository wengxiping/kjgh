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
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_CONFIG_LAYOUT_DASHBOARD'); ?>
	
			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'layout_toolbar', 'COM_PP_CONFIG_LAYOUT_TOOLBAR'); ?>

				<?php echo $this->html('settings.toggle', 'layout_pending_orders', 'COM_PP_CONFIG_LAYOUT_HIDE_PENDING_ORDERS'); ?>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_LAYOUT_TOOLBAR_COLOR'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.colorpicker', 'layout_toolbar_color', $this->config->get('layout_toolbar_color'), '#333333');?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_LAYOUT_TOOLBAR_TEXT_COLOR'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.colorpicker', 'layout_toolbar_textcolor', $this->config->get('layout_toolbar_textcolor'), '#FFFFFF');?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_LAYOUT_TOOLBAR_ACTIVE_COLOR'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.colorpicker', 'layout_toolbar_activecolor', $this->config->get('layout_toolbar_activecolor'), '#5C5C5C');?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_LAYOUT_TOOLBAR_BORDER_COLOR'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.colorpicker', 'layout_toolbar_bordercolor', $this->config->get('layout_toolbar_bordercolor'), '#333333');?>
					</div>
				</div>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_CONFIG_LAYOUT_PLANS'); ?>
	
			<div class="panel-body">
				
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_LAYOUT_TOTAL_PLANS_PER_ROW'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.lists', 'row_plan_counter', $this->config->get('row_plan_counter'), '', '', array(
									array('title' => '1', 'value' => '1'),
									array('title' => '2', 'value' => '2'),
									array('title' => '3', 'value' => '3'),
									array('title' => '4', 'value' => '4')
								)); ?>
					</div>
				</div>					
			</div>
		</div>
	</div>

	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_CONFIG_LAYOUT_CHECKOUT'); ?>
	
			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'checkout_display_logo', 'COM_PP_CHECKOUT_DISPLAY_LOGO'); ?>

				<?php echo $this->html('settings.toggle', 'checkout_display_steps', 'COM_PP_CHECKOUT_DISPLAY_STEPS'); ?>
			</div>
		</div>
	</div>
</div>