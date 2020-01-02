<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<tr>
	<td style="text-align: center;padding: 40px 10px 0;">
		<div style="margin-bottom:15px;">
			<div style="font-family:Arial;font-size:32px;font-weight:normal;color:#333;display:block; margin: 4px 0">
				<?php echo JText::_('COM_ES_EMAILS_USER_ACCOUNT_BANNED'); ?>
			</div>
		</div>
	</td>
</tr>

<tr>
	<td style="text-align: center;font-size:12px;color:#888">
		<p style="text-align:left;padding: 0 30px;">
			<?php echo JText::_('COM_EASYSOCIAL_EMAILS_HELLO'); ?> <?php echo $userName; ?>,
		</p>

		<p style="text-align:left;padding: 0 30px;">
			<?php echo JText::_('COM_ES_EMAILS_USER_ACCOUNT_BANNED_CONTENT');?>
		</p>

		<table role="presentation" aria-hidden="true" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:660px;">
				<td bgcolor="#f6f9fb" align="center" style="padding: 24px;">
					<table role="presentation" aria-hidden="true" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:660px;">
						<tbody>
							<tr>
								<td valign="top" width="64">
									<span style="display:block;width:64px;border-radius:50%; -moz-border-radius:50%; -webkit-border-radius:50%;background:#fff">
										<a href="<?php echo $userProfileLink;?>"><img src="<?php echo $avatar;?>" alt="<?php echo $this->html( 'string.escape' , $name );?>" style="border-radius:50%; -moz-border-radius:50%; -webkit-border-radius:50%;background:#fff;vertical-align:middle;" width="64" height="64"/></a>
									</span>
								</td>
								<td valign="top" style="padding: 0 16px;">
									<table align="left" style="font-size: 14px;margin: 0 auto 10px 20px; text-align:left;color:#798796" align="">
										<tr>
											<td style="padding: 5px 0;">
												<a href="<?php echo $userProfileLink;?>" style="color:#00aeef;text-decoration:none;"><strong><?php echo $name;?></strong></a>
											</td>
										</tr>
										<tr>
											<td style="padding: 5px 0;">
												<?php echo $profileType; ?>
											</td>
										</tr>
										<tr>
											<td style="margin-bottom: 30px;">
												<?php echo JText::_('COM_EASYSOCIAL_EMAILS_FRIENDS');?>: <?php echo $totalFriends;?>
												<span style="font-size:9px;">&bull;</span>
												<?php echo JText::_('COM_EASYSOCIAL_EMAILS_FOLLOWERS');?>: <?php echo $totalFollowers;?>
											</td>
										</tr>
										<tr>
											<td style="padding: 5px 0;">
											 	<?php echo JText::_('COM_ES_EMAILS_USER_BANNED_REASON');?>: <?php echo $reason; ?>
											</td>
										</tr>

										<tr>
											<td style="padding: 5px 0;">
												<a href="<?php echo $userProfileLink;?>" style="color:#00aeef;text-decoration:none;"><?php echo JText::_( 'COM_EASYSOCIAL_EMAILS_VIEW_PROFILE' );?> &rarr;</a>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</table>
		</td>
		</tr>
		</table>
	</td>
</tr>
