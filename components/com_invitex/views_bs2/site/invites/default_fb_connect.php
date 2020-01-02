<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die( 'Restricted access' );

$app_id = JFactory::getApplication()->input->get('app_id');
?>
<html xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml">
	<body>
		<style type="text/css">
			.contentdiv{background: -moz-linear-gradient(center top , #FFFFFF, #EDEDED) repeat scroll 0 0 transparent;
			border: 1px solid #B7B7B7;
			color: #606060;
			padding: 30px;
			margin:10%;
			width: 605px;}
		</style>
		<div id="fb-root" class=" fb_reset">
			<script src="https://connect.facebook.net/en_US/all.js" async=""></script>
		</div>
		<script>
			var graphApiInitialized = false;
				window.fbAsyncInit = function() {
					FB.init({
						appId  : '<?php echo $app_id?>',
						status : true, // check login status
						cookie : true, // enable cookies to allow the server to access the session
						xfbml  : true,  // parse XFBML
						oauth: true    });
					graphApiInitialized = true;
				};
				(function() {
					var e = document.createElement('script');
					e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
					e.async = true;
					document.getElementById('fb-root').appendChild(e);
				}());
		</script>
		<form id="form1">
			<?php echo JHtml::_('form.token'); ?>
			<input type="hidden" name="option" value="com_invitex"/>
			<input type="hidden" name="task" value="FBRequestReview"/>
			<input type="hidden" name="request_ids" value="<?php echo JFactory::getApplication()->input->get('request_ids');?>"/>
		</form>
		<div visible="false" runat="server" id="errorDiv" style="height:78px;" class="well">
			<center>
				<?php echo JText::_("FB_REQUEST_APP_NOT_AUTHORISED_MSG");?><br>
				<table>
					<tbody>
						<tr>
							<td style="width:10px">
							</td>
							<td>
								<script>
									function OnLoginCallbackFunc2(response) {
									if (response.authResponse != null) {
									document.getElementById('form1').submit()}
									}
									function OnPopupFunc1() {
									if (graphApiInitialized == false) {
										setTimeout('OnPopupFunc1()', 100);
										return;
									}
									FB.login(OnLoginCallbackFunc2, {scope: ''});
									}
								</script>
								<a id="LoginLink3" onclick="OnPopupFunc1()" style="cursor:pointer;"><img alt="" src="<?php echo JURI::root().'media/com_invitex/images/fb-connect.png' ?>"></a>
							</td>
							<td style="width:75px"></td>
						</tr>
					</tbody>
				</table>
			</center>
		</div>
	</body>
</html>
