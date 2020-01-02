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
<!-- Email Body : BEGIN -->
<table role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 680px;" class="email-container">
	<tr>
		<td bgcolor="#ffffff">
			<table role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<td style="padding: 24px 24px 0; text-align: left;">
						<h1 style="margin: 0; font-family: sans-serif; font-size: 22px; line-height: 27px; color: #666666; font-weight: normal;"><?php echo nl2br(JText::_('COM_PP_DOWNLOADS_EMAIL_HEADING')); ?></h1>
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
								<p style="margin-bottom: 40px;">
									<?php echo JText::sprintf("COM_PAYPLANS_EMAILS_GDPR_DOWNLOAD_DESCRIPTION", $downloadLink); ?>
								</p>

								<table role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" align="left">
									<tbody><tr><td>
										<table border="0" cellspacing="0" cellpadding="0">
											<tbody><tr><td>
												<table border="0" cellspacing="0" cellpadding="0">
													<tbody>
														<tr>
															<td align="center" style="border-radius: 3px;" bgcolor="#54C063">
																<a href="<?php echo $downloadLink;?>" target="_blank" style="font-size: 16px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; text-decoration: none;border-radius: 3px; padding: 12px 18px; border: 1px solid #54C063; display: inline-block;">
																	<?php echo JText::_('COM_PP_DOWNLOAD'); ?> &rarr;
																</a>
															</td>
														</tr>
													</tbody>
												</table>
											</td></tr></tbody>
										</table>
									</td></tr></tbody>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</table>
		</td>
	</tr>
</table>
<!-- Email Body : END -->