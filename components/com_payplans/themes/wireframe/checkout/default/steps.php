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
<?php if ($this->config->get('checkout_display_steps')) { ?>
<div class="pp-stepbar t-lg-mb--xl t-lg-mt--xl">
	<div class="pp-stepbar__item is-completed">
		<a href="<?php echo PPR::_('index.php?option=com_payplans&view=plan');?>" class="pp-step">
			<div class="pp-step__no">
				<i class="fas fa-shopping-cart"></i>
			</div>	
		</a>
	</div>

	<div class="pp-stepbar__item <?php echo $step == 'info' ? 'is-active' : '';?> <?php echo $step == 'payment' ? 'is-completed' : '';?>">
		<a class="pp-step" href="javascript:void(0);">
			<div class="pp-step__no">1</div>
			<div class="pp-step__desc">
				<?php echo JText::_('COM_PP_CHECKOUT_STEPS_CONFIRMATION');?>
			</div>
		</a>
	</div>

	<div class="pp-stepbar__item <?php echo $step == 'payment' ? 'is-active' : '';?>">
		<div class="pp-step">
			<div class="pp-step__no">2</div>
			<div class="pp-step__desc"><?php echo JText::_('COM_PP_CHECKOUT_STEPS_PAYMENT');?></div>
		</div>
	</div>
</div>
<?php } ?>