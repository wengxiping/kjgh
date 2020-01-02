<?php
/**
 * @package    InviteX
 * @copyright  Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license    GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link       http://www.techjoomla.com
 */

defined('_JEXEC') or die('Restricted access');

$cominvitexHelper = new cominvitexHelper;
$inv_userID = $cominvitexHelper->getUserID();
$inv_user = JFactory::getUser($inv_userID);
$js_define_file = JPATH_SITE . '/components/com_invitex/views/invites/tmpl/js_defines.php';

if (file_exists($js_define_file))
{
	require_once $js_define_file;
}
?>
<script type="text/javascript" src="<?php echo JURI::root(true) . '/media/com_invitex/js/bootstrap-tokenfield.min.js';?>"></script>
<script type="text/javascript" src="<?php echo JURI::root(true) . '/media/com_invitex/js/invite.js';?>"></script>
<script type="text/javascript" src="<?php echo JURI::root(true) . '/media/com_invitex/js/jquery.quicksearch.js';?>"></script>
<script type="text/javascript">
inv_method_title=techjoomla.jQuery(".inv_selected_method_active").attr("title");
techjoomla.jQuery(".inv_method_title").html("<b>"+inv_method_title+"</b>");
techjoomla.jQuery('#invitex_mail').on('tokenfield:createtoken', function (e) {

				var data = e.attrs.value.split('|')
				e.attrs.value = data[1] || data[0]
				e.attrs.label = data[1] ? data[0] + ' (' + data[1] + ')' : data[0]

})
techjoomla.jQuery('#invitex_mail').on('tokenfield:createdtoken', function (e) {
	  		var val = trim(e.attrs.value);
    // Über-simplistic e-mail validation
    var re = /\S+@\S+\.\S+/
    var valid = re.test(val)
    if (!valid) {
		response=-1
		//alert("Not a valid email address");
      techjoomla.jQuery(e.relatedTarget).addClass('invalid');
      push_hidden_mailvalues(response,val);

      return;

    }

		//validate if he is entering his own email Validate if he is inviting to self and other fields like
		if(self_email)
		{
			if(val.indexOf(self_email) > -1)
			{
				alert("You can't send invitation to yourself.");
				techjoomla.jQuery(e.relatedTarget).addClass('invalid');
				response=-1
				push_hidden_mailvalues(response,val);
				return;
			}
		}

		var response=1;

		var invite_domains=invite_domains_str.split(',');

		//Validate domain and other fields like
		if(allow_domain_validation==1)
		{
			if (invite_domains instanceof Array)
			{
				if(invite_domains)
				{
					for(var i=0;i<invite_domains.length;i++)
					{
						var split = invite_domains[i].split('.');
						response=-1;


						if (val.indexOf(invite_domains[i]) > -1)
						{
							response =1;
							break;
						}
					}
				}
			}
			else
			{

				var domains=new Array();
				domains.push(invite_domains);
				response=-1;
				if (val.indexOf(domains[0]) > -1)
				{
					response =1;
				}
			}
			if(response==-1)
			{
				alert("This domain is not allowed Please use domain mentioned above");
				techjoomla.jQuery(e.relatedTarget).addClass('invalid');
			}

		}




	push_hidden_mailvalues(response,val);
  })

techjoomla.jQuery('#invitex_mail').on('tokenfield:edittoken', function (e) {
var val = trim(e.attrs.value);
remove_hidden_mailvalues(val);
})


techjoomla.jQuery('#invitex_mail').on('tokenfield:removedtoken', function (e) {
	//Remove values from hidden fields
    remove_hidden_mailvalues(e.attrs.value);
  });


  techjoomla.jQuery('#invitex_mail').on('tokenfield:createtoken', function (e) {
				var data = e.attrs.value.split('|')
				e.attrs.value = data[1] || data[0]
				e.attrs.label = data[1] ? data[0] + ' (' + data[1] + ')' : data[0]

})
techjoomla.jQuery('#invitex_mail').on('tokenfield:createdtoken', function (e) {
	  		var val = trim(e.attrs.value);
    // Über-simplistic e-mail validation
    var re = /\S+@\S+\.\S+/
    var valid = re.test(val)
    if (!valid) {
		response=-1
		alert("Not a valid email address");
      techjoomla.jQuery(e.relatedTarget).addClass('invalid');
      push_hidden_mailvalues(response,val);

      return;

    }

		//validate if he is entering his own email Validate if he is inviting to self and other fields like
		if(self_email)
		{
			if(val.indexOf(self_email) > -1)
			{
				alert("You can't send invitation to yourself.");
				techjoomla.jQuery(e.relatedTarget).addClass('invalid');
				response=-1
				push_hidden_mailvalues(response,val);
				return;
			}
		}

		var response=1;

		var invite_domains=invite_domains_str.split(',');

		//Validate domain and other fields like
		if(allow_domain_validation==1)
		{
			if (invite_domains instanceof Array)
			{
				if(invite_domains)
				{
					for(var i=0;i<invite_domains.length;i++)
					{
						var split = invite_domains[i].split('.');
						response=-1;


						if (val.indexOf(invite_domains[i]) > -1)
						{
							response =1;
							break;
						}
					}
				}
			}
			else
			{

				var domains=new Array();
				domains.push(invite_domains);
				response=-1;
				if (val.indexOf(domains[0]) > -1)
				{
					response =1;
				}
			}
			if(response==-1)
			{
				alert("This domain is not allowed Please use domain mentioned above");
				techjoomla.jQuery(e.relatedTarget).addClass('invalid');
			}

		}




	push_hidden_mailvalues(response,val);
  })

techjoomla.jQuery('#invitex_mail').on('tokenfield:edittoken', function (e) {
var val = trim(e.attrs.value);
remove_hidden_mailvalues(val);
})


techjoomla.jQuery('#invitex_mail').on('tokenfield:removedtoken', function (e) {
	//Remove values from hidden fields
    remove_hidden_mailvalues(e.attrs.value);
  });

	try {
		jQuery('#invitex_mail').tokenfield();
	} catch(e) {
		techjoomla.jQuery('#invitex_mail').tokenfield();
	}


	function remove_hidden_mailvalues(val)
	{
		var field_to_push;
		 var label = val.split(',')
		 var hidden_labels = ["invitex_correct_mails", "invitex_wrong_mails"];
		for	(index = 0; index < hidden_labels.length; index++)
		{
			field_to_push=hidden_labels[index];
			if(techjoomla.jQuery("#"+field_to_push).val())
			{
				var hidden_values = techjoomla.jQuery("#"+field_to_push).val().split(',');

				if (techjoomla.jQuery.inArray(val, hidden_values)!='-1')
				{
					var index = hidden_values.indexOf(val);
					if(index!=-1)
					{
					   hidden_values.splice(index, 1);
					}

					val_to_push=hidden_values.join(",");
					techjoomla.jQuery("#"+field_to_push).val(val_to_push);
					break;
				}


			}
		}
	}



	function push_hidden_mailvalues(response,val)
	{


		if(response == 1)
		{

			ad_class="selection";
			field_to_push="invitex_correct_mails";
		}
		else
		{
			ad_class="selection li_edit";
			field_to_push="invitex_wrong_mails";
		}

		if(techjoomla.jQuery("#"+field_to_push).val())
		{
			var hidden_values = techjoomla.jQuery("#"+field_to_push).val().split(',');
			if (techjoomla.jQuery.inArray(val, hidden_values)=='-1') {
				hidden_values.push(val);
			}
			val_to_push=hidden_values.join(",");
		}

		else
		{
			val_to_push=val;
		}

		techjoomla.jQuery("#"+field_to_push).val(val_to_push);

	}

</script>

