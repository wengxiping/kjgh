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
			<?php echo $this->html('panel.heading', 'COM_PP_CONFIG_SETTINGS_SYSTEM_GENERAL'); ?>
			
			<div class="panel-body">

				<?php echo $this->html('settings.toggle', 'expert_use_jquery', 'COM_PP_RENDER_JQUERY'); ?>

				<?php echo $this->html('settings.toggle', 'expert_run_automatic_cron', 'COM_PP_ENABLE_AUTOMATED_CRON', '', 'data-pp-automated-cron'); ?>

				<div class="o-form-group <?php echo $this->config->get('expert_run_automatic_cron') ? '' : 't-hidden';?>" data-pp-cron-frequency>
					<?php echo $this->html('form.label', 'COM_PAYPLANS_CONFIG_CRON_FREQUENCY'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.lists', 'cronFrequency', $this->config->get('cronFrequency', 900), '', '', array(
								array('title' => 'COM_PAYPLANS_CONFIG_CRON_FREQUENCY_LOWEST', 'value' => 3600),
								array('title' => 'COM_PAYPLANS_CONFIG_CRON_FREQUENCY_LOW', 'value' => 1800),
								array('title' => 'COM_PAYPLANS_CONFIG_CRON_FREQUENCY_NORMAL', 'value' => 900),
								array('title' => 'COM_PAYPLANS_CONFIG_CRON_FREQUENCY_HIGH', 'value' => 300)
							)); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_ENCRYPTION_KEY'); ?>

					<div class="o-control-input col-md-7">
						<div class="o-input-group">
							<input type="text" name="expert_encryption_key" class="o-form-control" value="<?php echo $this->config->get('expert_encryption_key', 'AABBCCDD');?>" 
								<?php echo $this->config->get('expert_encryption_key') ? 'disabled="disabled"' : '';?> type="text" data-key-input />

							<?php if ($this->config->get('expert_encryption_key')) { ?>
							<span class="o-input-group__append">
								<button class="btn btn-pp-success-o t-hidden" type="button" data-key-update>
									<i class="fa fa-check"></i>	
								</button>
								<button class="btn btn-pp-default-o" type="button" data-key-edit><?php echo JText::_('COM_PP_EDIT_BUTTON');?></button>
							</span>
							<?php } ?>
						</div>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_WAIT_FOR_PAYMENT_BEFORE_EXPIRING'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.timer', 'expert_wait_for_payment', $this->config->get('expert_wait_for_payment', '000001000000')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_SYSTEM_AUTO_DELETE_INCOMPLETE_ORDERS'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.lists', 'expert_auto_delete', $this->config->get('expert_auto_delete', 'NEVER'), '', '', array(
								array('title' => 'COM_PAYPLANS_CONFIG_AUTO_DELETE_DUMMY_OPTION_NEVER', 'value' => "NEVER"),
								array('title' => 'COM_PAYPLANS_CONFIG_AUTO_DELETE_DUMMY_OPTION_ONE_DAY', 'value' => "000001000000"),
								array('title' => 'COM_PAYPLANS_CONFIG_AUTO_DELETE_DUMMY_OPTION_THREE_DAYS', 'value' => "000003000000"),
								array('title' => 'COM_PAYPLANS_CONFIG_AUTO_DELETE_DUMMY_OPTION_SEVEN_DAYS', 'value' => "000007000000"),
								array('title' => 'COM_PAYPLANS_CONFIG_AUTO_DELETE_DUMMY_OPTION_FIFTEEN_DAYS', 'value' => "000015000000"),
								array('title' => 'COM_PAYPLANS_CONFIG_AUTO_DELETE_DUMMY_OPTION_ONE_MONTH', 'value' => "000100000000"),
								array('title' => 'COM_PAYPLANS_CONFIG_AUTO_DELETE_DUMMY_OPTION_TWO_MONTH', 'value' => "000200000000"),
								array('title' => 'COM_PAYPLANS_CONFIG_AUTO_DELETE_DUMMY_OPTION_THREE_MONTH', 'value' => "000300000000"),
								array('title' => 'COM_PAYPLANS_CONFIG_AUTO_DELETE_DUMMY_OPTION_SIX_MONTH', 'value' => "000600000000"),
								array('title' => 'COM_PAYPLANS_CONFIG_AUTO_DELETE_DUMMY_OPTION_ONE_YEAR', 'value' => "010000000000")
							)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_CONFIG_LOGS'); ?>

			<div class="panel-body">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_PP_CONFIG_SYSTEM_LOGS_IGNORE_TYPE'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.autocomplete', 'blockLogging[]', $ignoreLogTypes, '', array('multiple' => true), array(
								array('title' => 'COM_PAYPLANS_CONFIG_BLOCK_LOGGING_FOR_PLAN', 'value' => "plan"),
								array('title' => 'COM_PAYPLANS_CONFIG_BLOCK_LOGGING_FOR_ORDER', 'value' => "order"),
								array('title' => 'COM_PAYPLANS_CONFIG_BLOCK_LOGGING_FOR_SUBSCRIPTION', 'value' => "subscription"),
								array('title' => 'COM_PAYPLANS_CONFIG_BLOCK_LOGGING_FOR_PAYMENT', 'value' => "payment"),
								array('title' => 'COM_PAYPLANS_CONFIG_BLOCK_LOGGING_FOR_APP', 'value' => "app"),
								array('title' => 'COM_PAYPLANS_CONFIG_BLOCK_LOGGING_FOR_CONFIG', 'value' => "config"),
								array('title' => 'COM_PAYPLANS_CONFIG_BLOCK_LOGGING_FOR_CRON', 'value' => "cron"),
								array('title' => 'COM_PAYPLANS_CONFIG_BLOCK_LOGGING_FOR_GROUP', 'value' => "group")
							)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
