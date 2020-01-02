<?php
/**
 * ------------------------------------------------------------------------
 * JA Morgan Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die('Restricted access');

$cp_version = '1.0';
?>
<?php if ($status!=''):?>
<script type="text/javascript">
	alert('<?php echo $status;?>');
</script>
<?php endif;?>
<div id="<?php echo $params->get('class_suffix','')?>ja-form">
	<div class="<?php echo $params->get('class_suffix','')?>form-info">
		<p><?php echo $params->get('intro_text', JText::_('CONTACT_US'))?></p>
	</div>
	<form  action="#" name="contact" method="post" id="contact" class="form-validate">
		<ul class="<?php echo $params->get('class_suffix','')?>form-list">
			<li class="<?php echo $params->get('class_suffix','')?>wide col-sm-6 clearfix" id="row_name">
				<div class="<?php echo $params->get('class_suffix','')?>input-box">
					<div id="error_name" class="<?php echo $params->get('class_suffix','')?>jl_error"><?php if(isset($error['name']))echo $error['name'] ?></div>
					<input  id="contact_name" type="text" name="name" value="<?php if ($name!='')echo $name; else echo ''; ?>" maxlength="60" size="40" placeholder="<?php echo $senderlabel;?>"/>  
				</div>
			</li>
			<li class="<?php echo $params->get('class_suffix','')?>wide col-sm-6 clearfix" id="row_email">
				<div class="<?php echo $params->get('class_suffix','')?>input-box">
					<div id="error_email" class="<?php echo $params->get('class_suffix','')?>jl_error"><?php if(isset($error['email']))echo $error['email'] ?></div>
					<input class="input-text" id="contact_email" type="text" name="email" value="<?php if ($email!='')echo $email; else echo ''; ?>" maxlength="64" size="40" placeholder="<?php echo $email_label;?>"/>  
					<div class="<?php echo $params->get('class_suffix','')?>small"><?php echo JText::_('NOTICE_REQUEST_USER_REAL_EMAIL');?> </div>
				</div>
			</li>
			<li class="<?php echo $params->get('class_suffix','')?>wide col-sm-12 clearfix" id="row_subject">
				<div class="<?php echo $params->get('class_suffix','')?>input-box">
					<div id="error_subject" class="<?php echo $params->get('class_suffix','')?>jl_error"><?php if(isset($error['error_subject']))echo $error['error_subject'] ?></div>
					<input class="input-text" id="contact_subject" name="subject"  value="<?php echo @$subject?>"  size="40" placeholder="<?php echo $subject_label;?>"/>
				</div>
			</li>
			<li class="<?php echo $params->get('class_suffix','')?>wide col-sm-12 clearfix" id="row_text">
				<div class="<?php echo $params->get('class_suffix','')?>input-box">
					<div id="error_text" class="<?php echo $params->get('class_suffix','')?>jl_error"><?php if(isset($error['error_text']))echo $error['error_text'] ?></div>
					<textarea class="textarea" id="contact_text" name="text" rows="10" cols="40" placeholder="<?php if($text!='') echo $text; else echo $message_label?>"></textarea>
				</div>
			</li>
			<?php if ($params->get( 'show_email_copy' ,0)) : ?>
			<li class="<?php echo $params->get('class_suffix','')?>wide col-sm-12">
				<div class="<?php echo $params->get('class_suffix','')?>input-box">
					<input type="checkbox" name="email_copy" id="contact_email_copy" value="1"  />
					<label for="contact_email_copy">
					<?php echo JText::_( 'SEND_ME_A_COPIED_EMAIL' ); ?>
					</label>
				</div>
			</li>
			<?php endif; ?>
			
			<?php if ($params->get( 'show_term' , 1)) : ?>
			<li class="<?php echo $params->get('moduleclass_sfx','')?>wide col-sm-12" id="row_term_condition">
				<div class="<?php echo $params->get('moduleclass_sfx','')?>input-box">
					<div id="error_term_condition" class="<?php echo $params->get('moduleclass_sfx','')?>jl_error"><?php if(isset($error['error_term_condition']))echo $error['error_term_condition'] ?></div>
					<input type="checkbox" name="term_condition" id="term_condition" value="1"  />
					<label>
					<!-- Incase already Translated -->
					<?php if (JText::_( 'SHOW_TERM_DEFAULT_TEXT' ) != 'SHOW_TERM_DEFAULT_TEXT'): ?>
						<?php echo JText::_( 'SHOW_TERM_DEFAULT_TEXT' ); ?><span class="required">(*)</span>
					<?php else: ?>
						<?php echo $params->get( 'show_term_text' , 'We have read and agreed with <a href="#">Terms of use</a> and <a href="#">privacy policy</a>'); ?>
					<?php endif; ?>
					</label>
				</div>
			</li>
			<?php endif; ?>
			
			<?php if ($captcha):
                if ($captcha_systems == 'invisible'): ?>
                    <script src="https://www.google.com/recaptcha/api.js"></script>
                <?php endif; ?>

                <?php if ($captcha_systems == 'recaptcha'): ?>
                <script src="https://www.google.com/recaptcha/api.js"></script>
                    <li class="<?php echo $params-> get('moduleclass_sfx','')?>wide col-sm-12">
                            <div class="g-recaptcha" data-sitekey="<?php echo $sitekey; ?>"></div>
                            <div id="error_captcha_code" class="<?php echo $params->get('moduleclass_sfx','')?>jl_error"><?php if(isset($error['captcha_code']))echo $error['captcha_code'] ?></div>
                    </li>
                <?php endif; ?>
                <?php if($captcha_systems != 'invisible' && $captcha_systems != 'recaptcha'): ?>
                    <li class="<?php echo $params->get('moduleclass_sfx','')?>wide col-sm-12" style="margin-top: 10px;">
				        <div class="<?php echo $params->get('moduleclass_sfx','')?>input-box">
				        <div id="error_captcha_code" class="<?php echo $params->get('moduleclass_sfx','')?>jl_error"><?php if(isset($error['captcha_code']))echo $error['captcha_code'] ?></div>
                    <?php
				        $mainframe->triggerEvent('onAfterDisplayForm');
			        ?>
			            </div>
			        </li>
                <?php endif; ?>
			<?php endif;?>
			
			<li>
				<div style="padding-top: 10px;">
                <?php if($captcha_systems == 'invisible'): ?>
                    <button
                        class="g-recaptcha"
                        data-sitekey="<?php echo $sitekey; ?>"
                        data-callback="JAQC_submit">
                        <?php echo JText::_("SEND_EMAIL"); ?>
                    </button>
                <?php endif; ?>
					<a <?php if($captcha_systems == 'invisible'): ?> style="display:none;" <?php endif; ?> href="javascript:void(0)" id="acsubmit" class="btn btn-primary">
						<?php echo JText::_("SEND_EMAIL"); ?> <span class="ion-ios-arrow-round-forward"></span>
					</a> 
				</div>
			</li>
		</ul>
		<input type="hidden" name="category" value="Error/Problems using site" />
		<input type="hidden" name="do_submit" value="1" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>
<script type="text/javascript">
	function JAQC_submit() {
	    console.log('= SUBMIT FORM =');
	    jQuery('.g-recaptcha').off().on('click', function(){
	        JAQC_submit();
	    });
	    jQuery('#ac-submit').trigger('click');
	}
jQuery(document).ready(function($){
	var captcha_code = 0;
	var cpversion = '<?php echo $captcha_systems; ?>';
	var maxchars = <?php echo $params->get('max_chars',1000);?>;
	var captcha = <?php echo intval($captcha); ?>;
	var emailabel = '<?php echo $email_label?>';
	var senderlabel = '<?php echo $senderlabel?>';
	var messagelabel = '<?php echo $message_label?>';
	var el = $('#ac-submit');

	el.on('click', function() {
		if ($("#system-message").length) {
			$("#system-message").html('');
		}
		var email = $('#contact_email').val();
		var ck=true;
		var errors = $('.error');
		if (errors.length)
		{
			errors.removeClass('error');
		}
		var regex=/^[a-zA-Z0-9._-]+@([a-zA-Z0-9.-]+\.)+[a-zA-Z0-9.-]{2,4}$/;
		if(!regex.test(email))
		{
			if((email=='')||(email==emailabel))
			{
				$('#error_email').html('<?php echo JText::_('ERROR_EMAIL_EMPTY')?>');
			}
			else
			{
				$('#error_email').html("<?php echo JText::_('ERROR_EMAIL_INVALID')?>");
			}
			$('#row_email').addClass('error');
			ck=false;
		}
		else
		{
			$('#error_email').html('');
		}
		var name = $('#contact_name').val();
		if((name=='')||(name==senderlabel))
		{
			$('#error_name').html('<?php echo  JText::_("ERROR_NAME_INVALID")?>');
			$('#row_name').addClass('error');
			ck = false;
		}
		else
		{
			$('#error_name').html('');
		}
		var subject = $('#contact_subject').val();
		if(subject=='')
		{
			$('#error_subject').html("<?php echo  JText::_("SUBJECT_REQUIRE")?>");
			$('#row_subject').addClass('error');
			ck = false;
		}
		else
		{
			$('#error_subject').html('');
		}
		<?php if ($params->get( 'show_term' , 1)) : ?>
			var termandcondition = $('#term_condition').prop('checked');
			if(!termandcondition) {
				$('#error_term_condition').html("<?php echo  JText::_("TERM_REQUIRE")?>");
				$('#row_term_condition').addClass('error');
				ck = false;
			} else {
				$('#error_term_condition').html('');
			}
		<?php endif; ?>
		var message = $('#contact_text').val();
		if((message.length>maxchars) ||(message.length < 5)||(message==messagelabel))
		{
			
			$('#error_text').html('<?php $error_message = JText::_('ERROR_MESSAGE_INVALID').$params->get('max_chars','5'); echo addslashes($error_message);?>');
			$('#row_text').addClass('error');
			ck = false;
		}
		else
		{
			$('#error_text').html('');
		}
		if(captcha)
		{
            if (cpversion === 'recaptcha' || cpversion === 'invisible') {
				var response = grecaptcha.getResponse();
				console.log(response);
				//recaptcha failed validation
				if(response.length == 0){
					$('#error_captcha_code').html("<?php echo JText::_('EMPTY_CAPTCHA')?>");
					ck = false;
				} else {
					$('#error_captcha_code').html("");     
				}
			} else {
				if ($('#captcha_code').length){
					captcha_code = $('#captcha_code').val();
					if((captcha_code=='')||(captcha_code=='Type the code shown'))
					{
						$('#error_captcha_code').html("<?php echo JText::_('EMPTY_CAPTCHA')?>");
						ck = false;
					} else 
						$('#error_captcha_code').html('');
				} else if($('#recaptcha_response_field').length){
					captcha_code = $('#recaptcha_response_field').val();
					if((captcha_code=='')||(captcha_code=='Type the code shown'))
					{
						$('#error_captcha_code').html("<?php echo JText::_('EMPTY_CAPTCHA')?>");
						ck = false;
					} else 
						$('#error_captcha_code').html("");
				} else if ($('#mathguard_answer').length) {
					captcha_code = $('#mathguard_answer').val();
					console.log(captcha_code);
					if((captcha_code=='')||(captcha_code=='Type the code shown'))
					{
						$('#error_captcha_code').html("<?php echo JText::_('EMPTY_CAPTCHA')?>");
						ck = false;
					} else 
						$('#error_captcha_code').html("");
				}
			}
		}
		
		if(ck)
		{
			if ($("#contact_email_copy").length) {
				var email_copy_check = document.getElementById("contact_email_copy").checked;
			} else {
				var email_copy_check = 0;
			}
			send_email_ajax(captcha_code, cpversion);
		}
		return ck;
	});
});

function send_email_ajax(captcha_code, cpversion)
{
	if (!jQuery) {
		return;
	}
	var $ = jQuery;
	var text_rep = $("#contact_text").val().replace(new RegExp( "\\n", "g" ),"~");
	var xurl = "<?php echo JURI::base();?>modules/<?php echo $module->module; ?>/admin/helper.php?japaramaction=sendEmail";
	var recaptcha_response_field = '';
	if($("#recaptcha_response_field").length){
		recaptcha_response_field = $("#recaptcha_response_field").val();
	}
	var recaptcha_challenge_field = '';
	if($("#recaptcha_challenge_field").length){
		recaptcha_challenge_field = $("#recaptcha_challenge_field").val();
	}
	var email_copy = 0;
	if($("#contact_email_copy:checked").length) {
		email_copy = 1;
	}
	
	var request = {
		"email_copy" : email_copy,
		"name" : $("#contact_name").val(),
		"email" : $("#contact_email").val(),
		"subject" : $("#contact_subject").val(),
		"text" : text_rep,
		"captcha_code" : captcha_code,
		"<?php echo JSession::getFormToken() ?>" : 1,
		"recaptcha_response_field" : recaptcha_response_field,
		"recaptcha_challenge_field" : recaptcha_challenge_field
	};
	
	if(cpversion == 'recaptcha' || cpversion == 'invisible') {
		var response = grecaptcha.getResponse();
		//recaptcha failed validation
		if(response.length != 0){
			request['g-recaptcha-response'] = response;
		}
	}

	$.ajax({
		url: xurl,
		method: 'post',
		dataType: 'json',
		data: request,
		success: function(result) {
			var contentHTML="";
			if (result.successful) {
				contentHTML += "<dd class=\"success message\"><ul><li>"+result.successful+"</li></ul></dd>";
				$("#ac-submit").addClass("sm");
				$("#ac-submit").attr('disabled', "disabled");
				$('#contact_name').val('');
				$('#contact_email').val('');
				$('#contact_subject').val('');
				$('#contact_text').val('');
				if($("#captcha").length){
					var myURI = $("#captcha").attr('src');
					$('#captcha').attr('src', myURI+'?sid=<?php echo md5(time());?>');
					$('#captcha_code').val('');
				}
				if($("#recaptcha_image").length){
					Recaptcha.reload();
				}
				if(cpversion == 'recaptcha' || cpversion == 'invisible') {
				    grecaptcha.reset();
				}
			}
			if (result.error) {
				contentHTML += "<dd class=\"error message\"><ul><li>"+result.error+"</li></ul></dd>";
				if($("#captcha").length){
					var myURI = $("#captcha").attr('src');
					$('#captcha').attr('src', myURI+'?sid=<?php echo md5(time());?>');
					$('#captcha_code').val('');
				}
				if($("#recaptcha_image").length){
					Recaptcha.reload();
				}
				if(cpversion == 'recaptcha' || cpversion == 'invisible') {
				    grecaptcha.reset();
				}
			}
			var msgobj = null;
			if ($("#system-message").length) {
				msgobj = $("#system-message");
			} else {
				var ms_container = [
					'<div id="system-message-container">',
						'<dl id="system-message">',
						'</dl>',
					'</div>'
				].join('');
				
				$('#ja-form').find('.form-info').append(ms_container);
				msgobj = $('#system-message');
			}
			msgobj.html(contentHTML);
		},
		error: function(result) {
			alert('<?php echo JText::_('ERROR_SEND_MAIL') ?>');
		}
	});
}
</script>