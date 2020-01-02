/** 
 *------------------------------------------------------------------------------
 * @package       T3 Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2013 JoomlArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       JoomlArt, JoomlaBamboo, (contribute to this project at github 
 *                & Google group to become co-author)
 * @Google group: https://groups.google.com/forum/#!forum/t3fw
 * @Link:         http://t3-framework.org 
 *------------------------------------------------------------------------------
 */

 jQuery(document).ready(function($) {

 	if($('#jform_contact_email_copy').length) {
 		$('#jform_contact_email_copy').parents('.control-group').addClass('inline');
 	}


 	////////////////////////////////
    // equalheight for col
    ////////////////////////////////
    var ehArray = ehArray2 = [],
      i = 0;

    $('.equal-height').each (function(){
      var $ehc = $(this);
      if ($ehc.has ('.equal-height')) {
        ehArray2[ehArray2.length] = $ehc;
      } else {
        ehArray[ehArray.length] = $ehc;
      }
    });

    for (i = ehArray2.length -1; i >= 0; i--) {
      ehArray[ehArray.length] = ehArray2[i];
    }

    var equalHeight = function() {
      for (i = 0; i < ehArray.length; i++) {
        var $cols = ehArray[i].children().filter('.col'),
          maxHeight = 0,
          equalChildHeight = ehArray[i].hasClass('equal-height-child');

      // reset min-height
        if (equalChildHeight) {
          $cols.each(function(){$(this).children().first().css('min-height', 0)});
        } else {
          $cols.css('min-height', 0);
        }
        $cols.each (function() {
          maxHeight = Math.max(maxHeight, equalChildHeight ? $(this).children().first().innerHeight() : $(this).innerHeight());
        });
        if (equalChildHeight) {
          $cols.each(function(){$(this).children().first().css('min-height', maxHeight + 2)});
        } else {
          $cols.css('min-height', maxHeight + 2);
        }
      }
      // store current size
      $('.equal-height > .col').each (function(){
        var $col = $(this);
        $col.data('old-width', $col.width()).data('old-height', $col.innerHeight());
      });
    };

    equalHeight();

    // monitor col width and fire equalHeight
    setInterval(function() {
      $('.equal-height > .col').each(function(){
        var $col = $(this);
        if (($col.data('old-width') && $col.data('old-width') != $col.width()) ||
            ($col.data('old-height') && $col.data('old-height') != $col.innerHeight())) {
          equalHeight();
          // break each loop
          return false;
        }
      });
    }, 500);

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
 })