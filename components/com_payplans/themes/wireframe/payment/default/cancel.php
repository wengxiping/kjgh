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
<div class="o-grid-sm__cell o-grid-sm__cell--center">
	<a href="<?php echo PPR::_("index.php?option=com_payplans&view=payment&task=complete&action=cancel&payment_key=" . $payment->getKey() . '&tmpl=component'); ?>">
		&larr; <?php echo JText::_('COM_PP_CANCEL_BUTTON')?>
	</a>
</div>
