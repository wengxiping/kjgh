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
if(defined('_JEXEC')===false) die();
?>
<div class="row-fluid">

	<h2>
		<?php echo JText::_('COM_PAYPLANS_PAYMENT_ERROR'); ?>
	</h2>
	<div><hr ></div>

	<div class="text-center">
		<h4>
			<?php echo JText::_('COM_PAYPLANS_PAYMENT_ERROR_MSG'); ?>
		</h4>
		
		<div>
			<?php echo JText::_('COM_PAYPLANS_PAYMENT_ERROR_TRY_TO')?>
			<a class="pp-button" href="<?php echo PPR::_('index.php?option=com_payplans&view=plan&task=subscribe'); ?>">
				<?php echo JText::_('COM_PAYPLANS_PAYMENT_ERROR_SUBSCRIBE_AGAIN');?>
			</a>
		</div>
		<p>
			<?php echo implode("\n", $appCompleteHtml);?>
			<?php echo JText::_('COM_PAYPLANS_PAYMENT_ERROR_CONTACT_TO_ADMIN')?>
			<?php echo PayplansHtml::_('email.link', JText::_('COM_PAYPLANS_ELEMENT_EMAIL'));?>
		</p>
		
	</div>
</div>
<?php 
