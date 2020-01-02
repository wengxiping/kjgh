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
	<span class="span6">
		<?php echo JText::_("COM_PAYPLANS_ENTER_DISCOUNT_CODE"); ?>
	</span>
	<span class="span6">			
		<div class="input-append">
				<input class="span9 input-medium" id="app_discount_code_id" type="text" name="app_discount_code" size="9" value=""/>
				<button type="button" id="app_discount_code_submit" class="btn" data-loading-text="wait..." title = "<?php  echo JText::_("COM_PAYPLANS_PRODISCOUNT_APPLY_TOOLTIP"); ?>" onClick="payplans.discount.apply(<?php echo $invoice->getId();?>);"><?php  echo JText::_("COM_PAYPLANS_APP_DISCOUNT_APPLY"); ?></button>
		</div>
		<div id="app-discount-apply-error" class="text-error">&nbsp;</div>
	</span>
</div>
<?php 