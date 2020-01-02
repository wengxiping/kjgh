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
<html xmlns="http://www.w3.org/1999/xhtml" >
<style>
		.fontlist
		{
			color: #323334;
		   font-family: Arial,Verdana,Tahoma,Sans Serif;
			line-height: 25px;
			word-spacing: 3px;
		}
			.row
		{
			padding:10px;
		}
		.col
		{
			padding:10px;
		}
		.links {
			color: #2B6EAD;
			text-decoration: none;
		}
		#shadow{
			box-shadow:0px 0px 11px -4px grey;
		}
		

</style>
		<table class="fontlist" id='shadow' align="center" bgcolor="#f8f8f8" border="0" cellpadding="0" cellspacing="0" width="650">
			<tbody>
			<tr>
				<td  bgcolor="f8f8f8">
					<br>
					<p style="margin-left:25px;">Hello [[USER_REALNAME]],<br></p><p style="padding:10px; margin-left:15px;">
					Your subscription at was expired on [[SUBSCRIPTION_EXPIRATION_DATE]]. To get continue with our services renew your subscription or you can get new plan.<br><br>
					<b>Your details are as follows:</b>
					</p>
					<table  align="center">
						<tbody>
						<tr>
							<td class="col" align="right">
								Username:
							</td>						
							<td class="col">
								[[USER_USERNAME]]
							</td>
						</tr>
						<tr>
							<td style="padding:10px;" mce_style="padding:10px;" align="right">
								Plan Name:
							</td>						
							<td style="padding:10px;" mce_style="padding:10px;">
								[[PLAN_TITLE]]
							</td>
						</tr>
							<tr>
							<td class="col" align="right">
								Activation Date:
							</td>
							<td class="col">
								[[SUBSCRIPTION_SUBSCRIPTION_DATE]]
							</td>
						</tr>
						<tr>
							<td class="col" align="right">
								Expiration Date:
							</td>
							<td class="col">
								[[SUBSCRIPTION_EXPIRATION_DATE]]
							</td>
						</tr>
					</tbody></table>					
					<p align="center"><a target="_blank" style="text-decoration:none; color:lightgreen"  href="[[CONFIG_PLAN_RENEW_URL]]"><input value="Renew Here" style="padding:10px;border:5px black;background-color:#2B6EAD;color:white; border-radius:5px;" align="right" type="button"></a>
					</p>
					<p style="padding:10px; margin-left:15px;">You can also share your experiences with us. For updates and support visit us here. <a href="[[CONFIG_SITE_URL]]" class="links">[[CONFIG_SITE_URL]]</a><br></p>
					<p style="padding:10px; margin-left:15px;">
						<b>[[CONFIG_COMPANY_NAME]]</b><br>
						<br>
						<span style="font-size:12px;">
						[[CONFIG_COMPANY_ADDRESS]]						
						</span> 
					</p>
					
				</td>				
			</tr>
		</tbody></table>
</html>