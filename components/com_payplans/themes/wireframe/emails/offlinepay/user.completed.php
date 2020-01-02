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
<!-- Email Body : BEGIN -->
<table role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 680px;" class="email-container">
	<tr>
		<td bgcolor="#ffffff">
			<table role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<td style="padding: 24px 24px 0; text-align: left;">
						<h1 style="margin: 0; font-family: sans-serif; font-size: 22px; line-height: 27px; color: #666666; font-weight: normal;"><?php echo nl2br(JText::_('COM_PP_OFFLINE_PAY_USER_HEADING')); ?></h1>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td dir="ltr" bgcolor="#ffffff" height="100%" valign="top" width="100%" style="padding: 20px 24px 24px; font-family: sans-serif; font-size: 14px; color: #555555; text-align: center;">
			
			<table role="presentation" aria-hidden="true" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:660px;">
				<td bgcolor="#f6f9fb" align="center" style="padding: 24px;">
					<table role="presentation" aria-hidden="true" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:660px;">
						<tr>
							<td valign="top" width="100%">
								<?php echo nl2br(JText::sprintf('COM_PP_OFFLINE_PAY_USER_CONTENTS', $buyer->getName())); ?>
							</td>
						</tr>
						<tr>
							<td valign="top" width="100%">
								<br />								
								<?php echo JText::_('COM_PP_BANK_DETAILS') . " :"; ?>
								<br />
								<p style="color: #999999;line-height:1.5;text-align:left;margin: 0;padding: 15px 0 10px;" class="stack-column">
									<?php echo JText::_('COM_PP_PAYMENT_BANK_NAME');?>: <?php echo $bankName;?>
									<br />
									<?php echo JText::_('COM_PP_PAYMENT_BANK_ACCOUNT_NAME');?>: <?php echo $accountName;?>
									<br />
									<?php echo JText::_('COM_PP_PAYMENT_BANK_ACCOUNT_NUMBER');?>: <?php echo $bankAccount;?>
									<br />
									<?php echo JText::_('COM_PP_PAYMENT_BANK_INVOICE_REFERENCE_NUMBER');?>: <?php echo $invoice->getKey();?>
								</p>
							</td>
						</tr>
					</table>
				</td>
			</table>
		</td>
	</tr>
</table>
<!-- Email Body : END -->