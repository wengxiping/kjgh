/**
 * ------------------------------------------------------------------------
 * JA Conf Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

(function($){
  $(document).ready(function(){
    if($('.has-parallax').length > 0){
      $('.has-parallax .parallax-item').paroller(); 
     }
	
		// Add Placeholder form contact
		var formContact = $('.com_contact');
		if (formContact.length > 0) {
			$('#jform_contact_name', formContact).attr('placeholder',Joomla.JText._('COM_CONTACT_CONTACT_EMAIL_NAME_LABEL'));
			$('#jform_contact_email', formContact).attr('placeholder',Joomla.JText._('COM_CONTACT_EMAIL_LABEL'));
			$('#jform_contact_emailmsg', formContact).attr('placeholder',Joomla.JText._('COM_CONTACT_CONTACT_MESSAGE_SUBJECT_LABEL'));
			$('#jform_contact_message', formContact).attr('placeholder',Joomla.JText._('COM_CONTACT_CONTACT_ENTER_MESSAGE_LABEL'));
			
			if($('.ie8').length > 0) {
				$("input[placeholder], textarea[placeholder]", formContact).each(function(i, e){
					if($(e).val() == "") {
						$(e).val($(e).attr("placeholder"));
					}
					$(e).blur(function(){
					if($(this).val()=="")
						$(this).val($(e).attr("placeholder"));
					}).focus(function() {
					if($(this).val() == $(e).attr("placeholder"))
						$(this).val("");
					});
				});
			}
		}
  });
})(jQuery);