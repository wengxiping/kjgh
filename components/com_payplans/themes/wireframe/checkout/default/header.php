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
<div class="pp-checkout-container__hd">
	<?php if ($this->config->get('checkout_display_logo')) { ?>
		<?php echo $this->output('site/checkout/default/logo'); ?>
	<?php } ?>

	<?php if ($this->config->get('checkout_display_steps')) { ?>
		<?php echo $this->output('site/checkout/default/steps', array('step' => $step)); ?>
	<?php } ?>

	<div class="pp-checkout-container__title">
		<?php echo JText::_($title);?>
	</div>
</div>