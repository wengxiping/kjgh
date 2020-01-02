<?php
/**
 * ------------------------------------------------------------------------
 * JA Quick Contact Module for J3.x
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die('Restricted access');
?>
<?php if ($status!=''):?>
<script type="text/javascript">
	alert('<?php echo $status;?>');
</script>
<?php endif;?>
<div id="<?php echo $params->get('moduleclass_sfx','')?>ja-form" class="<?php echo $params->get('jastyle','style1'); ?>">
	<div class="<?php echo $params->get('moduleclass_sfx','')?>form-info">
		<h3><?php echo $params->get('intro_text','Contact Us')?></h3>
	</div>
	<form  action="#" name="ja_quicks_contact" method="post" id="ja_quicks_contact" class="form-validate">
		<ul class="<?php echo $params->get('moduleclass_sfx','')?>form-list">
			<li class="<?php echo $params->get('moduleclass_sfx','')?>wide clearfix" id="row_name">
				<label for="contact_name" class="required">
					&nbsp;<?php echo $senderlabel;?>:
				</label>
				<div class="<?php echo $params->get('moduleclass_sfx','')?>input-box">
					<div id="error_name" class="<?php echo $params->get('moduleclass_sfx','')?>jl_error"><?php if(isset($error['name']))echo $error['name'] ?></div>
					<input  id="contact_name" type="text" name="name" value="<?php if ($name!='')echo $name; else echo ''; ?>" maxlength="60" size="40" />
				</div>
			</li>
			<li class="<?php echo $params->get('moduleclass_sfx','')?>wide clearfix" id="row_email">
				<label id="contact_emailmsg" for="contact_email">
				&nbsp;<?php echo $email_label?>:
				</label>
				<div class="<?php echo $params->get('moduleclass_sfx','')?>input-box">
					<div id="error_email" class="<?php echo $params->get('moduleclass_sfx','')?>jl_error"><?php if(isset($error['email']))echo $error['email'] ?></div>
					<input class="input-text" id="contact_email" type="text" name="email" value="<?php if ($email!='')echo $email; else echo ''; ?>" maxlength="64" size="40" />
					<div class="<?php echo $params->get('moduleclass_sfx','')?>small"><?php echo JText::_('NOTICE_REQUEST_USER_REAL_EMAIL');?> </div>
				</div>
			</li>
			<li class="<?php echo $params->get('moduleclass_sfx','')?>wide clearfix" id="row_subject">
				<label id="contact_subjectmsg" class="required" for="contact_subject">
					&nbsp;<?php echo $subject_label?>:
				</label>
				<div class="<?php echo $params->get('moduleclass_sfx','')?>input-box">
					<div id="error_subject" class="<?php echo $params->get('moduleclass_sfx','')?>jl_error"><?php if(isset($error['error_subject']))echo $error['error_subject'] ?></div>
					<input class="input-text" id="contact_subject" name="subject"  value="<?php echo @$subject?>"  size="40"/>
				</div>
			</li>
			<li class="<?php echo $params->get('moduleclass_sfx','')?>wide clearfix" id="row_text">
				<label id="contact_textmsg" class="required" for="contact_text">
					&nbsp;<?php echo $message_label?>:
				</label>
				<div class="<?php echo $params->get('moduleclass_sfx','')?>input-box">
					<div id="error_text" class="<?php echo $params->get('moduleclass_sfx','')?>jl_error"><?php if(isset($error['error_text']))echo $error['error_text'] ?></div>
					<textarea class="textarea" id="contact_text" name="text" rows="10" cols="40"><?php if($text!='') echo $text; else echo ''?></textarea>
				</div>
			</li>
			<?php if ($params->get( 'show_email_copy' ,0)) : ?>
			<li class="<?php echo $params->get('moduleclass_sfx','')?>wide">
				<div class="<?php echo $params->get('moduleclass_sfx','')?>input-box">
					<input type="checkbox" name="email_copy" id="contact_email_copy" value="1"  />
					<label for="contact_email_copy">
					<?php echo JText::_( 'SEND_ME_A_COPIED_EMAIL' ); ?>
					</label>
				</div>
			</li>
			<?php endif; ?>

			<?php if ($params->get( 'show_term' , 1)) : ?>
			<li class="<?php echo $params->get('moduleclass_sfx','')?>wide" id="row_term_condition">
				<div class="<?php echo $params->get('moduleclass_sfx','')?>input-box">
					<div id="error_term_condition" class="<?php echo $params->get('moduleclass_sfx','')?>jl_error"><?php if(isset($error['error_term_condition']))echo $error['error_term_condition'] ?></div>
					<input type="checkbox" name="term_condition" id="term_condition" value="1"  />
					<label>
					<!-- Incase already Translated -->
					<?php if (JText::_( 'SHOW_TERM_DEFAULT_TEXT' ) != 'SHOW_TERM_DEFAULT_TEXT'): ?>
						<?php echo JText::_( 'SHOW_TERM_DEFAULT_TEXT' ); ?><span class="required">(*)</span>
					<?php else: ?>
						<?php echo $params->get( 'show_term_text' , 'We have read and agreed with <a href="#">Terms of use</a> and <a href="#">privacy policy</a>'); ?><span class="required">(*)</span>
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
                    <li class="<?php echo $params-> get('moduleclass_sfx','')?>wide">
                            <div class="g-recaptcha" data-sitekey="<?php echo $sitekey; ?>"></div>
                            <div id="error_captcha_code" class="<?php echo $params->get('moduleclass_sfx','')?>jl_error"><?php if(isset($error['captcha_code']))echo $error['captcha_code'] ?></div>
                    </li>
                <?php endif; ?>
                <?php if($captcha_systems != 'invisible' && $captcha_systems != 'recaptcha'): ?>
                    <li class="<?php echo $params->get('moduleclass_sfx','')?>wide" style="margin-top: 10px;">
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
                    <?php endif;?>
                    <a <?php if($captcha_systems == 'invisible'): ?> style="display:none;" <?php endif; ?> href="javascript:void(0)" id="ac-submit" class="button-img but-orange"><i class="fa fa-paper-plane">&nbsp;</i><span><?php echo JText::_("SEND_EMAIL"); ?></span></a>
                
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
	var maxchars = <?php echo $params->get('max_chars',1000);?>;
	var captcha = <?php echo intval($captcha)?>;
	var cpversion = '<?php echo $captcha_systems; ?>';
	var emailabel = '<?php echo $email_label?>';
	var senderlabel = '<?php echo $senderlabel?>';
	var messagelabel = '<?php echo $message_label?>';
	var el = $('#ac-submit');
	el.on('click', function(e) {
		var email = $('#contact_email').val();
		var ck=true;
		var errors = $('.error');
	    if (!errors || errors.length>0) {
	        errors.removeClass('error');
	    }
		var regex=/^[a-zA-Z0-9._-]+@([a-zA-Z0-9.-]+\.)+[a-zA-Z0-9.-]{2,4}$/;
		if(!regex.test(email)) {
			if((email=='')||(email==emailabel)) {
				$('#error_email').html('<?php echo JText::_('ERROR_EMAIL_EMPTY')?>');
			} else {
				$('#error_email').html("<?php echo JText::_('ERROR_EMAIL_INVALID')?>");
			}
			$('#row_email').addClass('error');
			ck=false;
		} else {
			$('#error_email').html('');
		}
		
		var name = $('#contact_name').val();
		if((name=='')||(name==senderlabel)) {
			$('#error_name').html('<?php echo  JText::_("ERROR_NAME_INVALID")?>');
			$('#row_name').addClass('error');
			ck = false;
		} else {
			$('#error_name').html('');
		}
		
		var subject = $('#contact_subject').val();
		if(subject=='') {
			$('#error_subject').html("<?php echo  JText::_("SUBJECT_REQUIRE")?>");
			$('#row_subject').addClass('error');
			ck = false;
		} else {
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
		if((message.length>maxchars) ||(message.length < 1)||(message==messagelabel)) {
			
			$('#error_text').html('<?php $error_message = JText::_('ERROR_MESSAGE_INVALID'). $params->get('max_chars','5'); echo addslashes($error_message);?>');
			$('#row_text').addClass('error');
			ck = false;
		} else {
			$('#error_text').html('');
		}
		
		if(captcha) {
			if (cpversion === 'recaptcha' || cpversion === 'invisible') {
                var response = grecaptcha.getResponse();
                //recaptcha failed validation
                if (response.length == 0) {
                    $('#error_captcha_code').html("<?php echo JText::_('EMPTY_CAPTCHA')?>");
                    ck = false;
                } else $('#error_captcha_code').html("");
			} else {
				if ($('#captcha_code').length){
					var captcha_code = $('#captcha_code').val();
				    if((captcha_code=='')||(captcha_code=='Type the code shown'))
				    {
					   $('#error_captcha_code').html("<?php echo JText::_('EMPTY_CAPTCHA')?>");
					   ck = false;
				    } else {
						$('#error_captcha_code').html('');
					}
			    } else if($('#recaptcha_response_field').length) {
					var captcha_code = $('#recaptcha_response_field').val();
					if((captcha_code=='')||(captcha_code=='Type the code shown'))
					{
					   $('#error_captcha_code').html("<?php echo JText::_('EMPTY_CAPTCHA')?>");
					   ck = false;
					} else 
						$('#error_captcha_code').html("");
				} else if ($('#mathguard_answer').length) {
					var captcha_code = $('#mathguard_answer').val();
					if((captcha_code=='')||(captcha_code=='Type the code shown')) {
					   $('#error_captcha_code').html("<?php echo JText::_('EMPTY_CAPTCHA')?>");
					   ck = false;
					} else {
						$('#error_captcha_code').html("");
					}
				}
			}
		}
		if(ck) {
			$("#ja_quicks_contact").submit();
		}
		return ck;
	});
});
</script>