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
<div class="row">
	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_CONFIG_DISCOUNTS_GENERAL'); ?>
				
			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'discounts_referral', 'COM_PP_CONFIG_DISCOUNTS_ENABLE_REFERRAL'); ?>

				<?php echo $this->html('settings.toggle', 'enableDiscount', 'COM_PP_CONFIG_DISCOUNTS_ENABLE_DISCOUNTS'); ?>
	
				<?php echo $this->html('settings.toggle', 'multipleDiscount', 'COM_PP_CONFIG_DISCOUNTS_ALLOW_COMBINING_DISCOUNTS'); ?>

				<?php echo $this->html('settings.textbox', 'allowedMaxPercentDiscount', 'COM_PPC_ONFIG_DISCOUNTS_MAX_DISCOUNTS', '', array('postfix' => '%', 'size' => 7), '', 'text-center'); ?>
			</div>
		</div>
	</div>

	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_CONFIG_SOCIAL_DISCOUNTS_GENERAL'); ?>
				
			<div class="panel-body">

				<?php echo $this->html('settings.toggle', 'discounts_twitter', 'PLG_PAYPLANSSOCIALDISCOUNT_ENABLE_TWITTER_FOLLOW', '', array('data-pp-twitter' => '')); ?>

				<div class="o-form-group <?php echo $this->config->get('discounts_twitter') ? '' : 't-hidden';?>" data-pp-discounts-twitter>
					<?php echo $this->html('form.label', 'PLG_PAYPLANSSOCIALDISCOUNT_TWITTER_FOLLOW_PAGEURL'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.text', 'discounts_twitter_url', $this->config->get('discounts_twitter_url')); ?>
					</div>
				</div>

				<div class="o-form-group <?php echo $this->config->get('discounts_twitter') ? '' : 't-hidden';?>" data-pp-discounts-twitter>
					<?php echo $this->html('form.label', 'PLG_PAYPLANSSOCIALDISCOUNT_TWITTER_FOLLOW_DISCOUNT'); ?>

					<div class="o-control-input col-md-7">
						<?php echo $this->html('form.discounts', 'discounts_twitter_code', $this->config->get('discounts_twitter_code')); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>