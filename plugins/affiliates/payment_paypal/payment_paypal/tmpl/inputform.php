<?php 

/*------------------------------------------------------------------------
# com_invoices - Invoices for Joomla
# ------------------------------------------------------------------------
# author				Germinal Camps
# copyright 			Copyright (C) 2012 JoomlaInvoices.com. All Rights Reserved.
# @license				http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: 			http://www.JoomlaInvoices.com
# Technical Support:	Forum - http://www.JoomlaFinances.com/forum
-------------------------------------------------------------------------*/

defined('_JEXEC') or die('Restricted access');
$doc    = JFactory::getDocument();
$doc->addStyleSheet("components/com_jblance/css/customer/account.css");
?>


<div class="control-group">
<label class="control-label left-label-title" for="payment_paypal_email"><span style='color:red'>*</span><?php echo JText::_( 'PAYPAL_EMAIL' ); ?></label>
<div class="controls">
  <input class="inputbox detail-info" type="text" name="payment_options[payment_paypal][email]" id="payment_paypal_email" size="80" maxlength="250" value="<?php if (isset($vars->payment_paypal)) echo $vars->payment_paypal->email; ?>" />
  <span class="help-block font-color"><?php echo $vars->params->get('description');?></span>
</div>

</div>