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
<?php if ($this->config->get('discounts_referral') && $referrals) { ?>
<div class="o-card o-card--borderless t-lg-mt--lg">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_REFERRALS');?></div>

	<div class="o-card__body">
		<p><?php echo JText::_('COM_PP_REFERRALS_DESC'); ?></p>

		<div class="o-form-group t-lg-mt--xl" data-pp-referral-wrapper>
			<div class="o-input-group">
				<input type="text" class="o-form-control" placeholder="<?php echo JText::_('COM_PP_CHECKOUT_REFERRAL_CODE_PLACEHOLDER');?>" data-pp-referral-code />
				<span class="o-input-group__append">

					<button class="btn btn-pp-default-o" type="button" data-pp-referral-apply>
						<?php echo JText::_('COM_PP_APPLY_BUTTON');?>
					</button>

					<button class="btn btn-pp-danger-o t-hidden" type="button" data-pp-referral-cancel>
						<i class="fa fa-times"></i>
					</button>
				</span>
			</div>

			<div class="t-text--danger" data-pp-referral-message></div>
		</div>
	</div>
</div>
<?php } ?>