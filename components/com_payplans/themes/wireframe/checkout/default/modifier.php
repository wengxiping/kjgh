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
<?php if ($modifiers) { ?>

<?php foreach ($modifiers as $modifier) { ?>
<tr class="<?php echo $modifier->isDiscount() ? 'discountable-amount pp-modifiers' : '';?>
		<?php echo $modifier->isTax() ? 'taxable-amount pp-modifiers' : '';?>
		<?php echo $modifier->isNonTaxable() ? 'nontaxable-amount pp-modifiers' : '';?>
	"
	data-pp-modifier-discount
>
	<td>
		<?php echo JText::_($modifier->message);?>
	</td>
	<td class="t-text--right">
		<span class="pp-modifiers polarity">
			(<?php echo $modifier->isNegative() ? '-' : '+';?>)
		</span>
		<?php $modifierAmount = str_replace('-', '', $modifier->_modificationOf); ?>
		<?php echo $this->html('html.amount', $modifierAmount, $invoice->getCurrency()); ?>
	</td>
</tr>
<?php } ?>

<?php } ?>
