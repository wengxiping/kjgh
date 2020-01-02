var sms_num =1;
var field_lenght_sms=0;
var field_lenght_manual=0;
var inv_method_title='';
var facebook_inv_method='request-dialog';

if(typeof(techjoomla) == 'undefined') {
	var techjoomla = {};
}

if(typeof techjoomla.jQuery == "undefined")
{
	techjoomla.jQuery = jQuery;
}

techjoomla.jQuery(function (){
		/*Set Invite Method*/
		inv_method_title=techjoomla.jQuery(".inv_selected_method_active").attr("title");
		techjoomla.jQuery(".inv_method_title").html("<b>"+inv_method_title+"</b>");

		techjoomla.jQuery("#inv_jsfriend-search-filter").focus(function(){
			this.value='';
			techjoomla.jQuery("#inv_jsfriend-search-filter").quicksearch("#inv_js_InvitationTabContainer #inv_js_invitation_list #inv_friend_li", {
				delay : 300,
				noResults: '#noresults',
				stripeRows: ['odd', 'even'],
			});
		});

		var characters= techjoomla.jQuery(".thCheckbox:checked").length;
		techjoomla.jQuery("#select_count").append("Selcted contacts: <strong>"+ characters+"</strong>");

		techjoomla.jQuery(".thCheckbox").click(function(){
			techjoomla.jQuery("#select_count").html('');
			characters= $(".thCheckbox:checked").length;
			techjoomla.jQuery("#select_count").append("Selected contacts:  <strong>"+ characters+"</strong>");
		});

		jQuery('#invitex_mail').on('tokenfield:createtoken', function (e){
			var data = e.attrs.value.split('|');
			e.attrs.value = data[1] || data[0];
			e.attrs.label = data[1] ? data[0] + ' (' + data[1] + ')' : data[0];
		})

		.on('tokenfield:createdtoken', function (e){
			var val = trim(e.attrs.value);
			/*Über-simplistic e-mail validation*/
			var re = /\S+@\S+\.\S+/;
			var valid = re.test(val);

			if (!valid){
				response=-1;
				/*alert(Joomla.JText._('COM_INVITEX_NOT_VALID_EMAIL'))*/
				techjoomla.jQuery(e.relatedTarget).addClass('invalid');
				push_hidden_mailvalues(response,val);
				return;
			}

			/*validate if he is entering his own email Validate if he is inviting to self and other fields like*/
			if(self_email)
			{
				if(val.indexOf(self_email) > -1)
				{
					alert(Joomla.JText._('COM_INVITEX_SELF_INVITATION_ERROR'));
					techjoomla.jQuery(e.relatedTarget).addClass('invalid');
					response=-1;
					push_hidden_mailvalues(response,val);
					return;
				}
			}

			var response=1;
			var invite_domains=invite_domains_str.split(',');

			/*Validate domain and other fields like*/
			if(allow_domain_validation==1)
			{
				if(invite_domains instanceof Array)
				{
					if(invite_domains)
					{
						for(var i=0;i<invite_domains.length;i++)
						{
							/*var split = invite_domains[i].split('.');*/
							invite_domains[i] = invite_domains[i].trim();
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
					domains[0]=domains[0].trim();

					if (val.indexOf(domains[0]) > -1)
					{
						response =1;
					}
				}

				if(response==-1)
				{
					alert(Joomla.JText._('COM_INVITEX_DOMAIN_NOT_ALLOWED'));
					techjoomla.jQuery(e.relatedTarget).addClass('invalid');
				}
			}

			push_hidden_mailvalues(response,val);
		})

		.on('tokenfield:edittoken', function (e){
			var val = trim(e.attrs.value);
			remove_hidden_mailvalues(val);
		})

		.on('tokenfield:removedtoken', function (e){
			/*Remove values from hidden fields*/
			remove_hidden_mailvalues(e.attrs.value);
		})

		if (jQuery('#invitex_mail').length)
		{
			jQuery('#invitex_mail').tokenfield({
			createTokensOnBlur: true,
			minWidth: 150
			});
		}

		jQuery("#acceptInviteConsent").click(function (){
			const token = jQuery(this).attr("token");
			userConsent(1, 'userConsent');
		});

		jQuery("#declineInviteConsent").click(function (){
			const token = jQuery(this).attr("token");
			userConsent(0, 'userConsent');
		});

		jQuery("#revokeInviteConsent").click(function (){
			userConsent(0, 'revokeUserConsent');
		});
});

function userConsent(consent, task)
{
	const token = jQuery("#consentToken input").attr('name');

	jQuery.ajax({
		url: invitex_root_url+"index.php?option=com_invitex&controller=invites&task="+task+"&tmpl=component&format=raw&consent="+consent+"&"+token+"=1",
		type: 'POST',
		cache: false,
		dataType: 'json',
		success: function(data)
		{
			if (data.success == 1)
			{
				if (data.redirectUrl != '')
				{
					window.location = data.redirectUrl;
				}
				else
				{
					location.reload();
				}
			}
		},
		error: function (e)
		{
			let msg = Joomla.JText._('COM_INVITEX_SOMETHING_WENT_WRONG');
			Joomla.renderMessages({'error':[msg]});
		}
	});
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

			if (techjoomla.jQuery.inArray(val, hidden_values)=='-1')
			{
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

		techjoomla.jQuery(function() {
			var ad_class='';

			techjoomla.jQuery("#invitex_search").focus(function(){
				this.value='';
				techjoomla.jQuery("#invitex_search").quicksearch("#invitex_invitee_info .invitex_info", {
					delay : 300,
					noResults: '#noresults',
					stripeRows: ['odd', 'even'],
				});
			});

			techjoomla.jQuery("#selections").click(function(){
				techjoomla.jQuery("#invitex_mail" ).focus();
						techjoomla.jQuery(window).keydown(function(event){
								if(event.keyCode == 13) {
								event.preventDefault();
								return false;
					}
					});
			});



			techjoomla.jQuery(".contacts_check").unbind("click").click( function(){
				var remove_selected=0;
				//show selected contact in new tabs
				show_count();

			   });



			show_count();
	});





  //////////////
//email validation completely in javscript

	function changeval_txtarea(value1)
	{
		inv_messagae_type_preview_msg=value1
		techjoomla.jQuery("textarea.personal_message").text(value1);
		techjoomla.jQuery("textarea.personal_message").value(value1);

	}


	function mpreview(rpath,rmessage_type)
	{

		if(document.getElementById("personal_message"))
			var message	=	document.getElementById("personal_message").value;
		else
			var message	='';

		if(typeof(rmessage_type)==='undefined')
		{

			import_type=inv_messagae_type_preview;
		}
		else
		{
			import_type=rmessage_type;
		}


		if(document.getElementById("guest_name"))
		{

			var guestuser	=	document.getElementById("guest_name").value;

		}


		message = message.replace(/\n\r?/g, '<br />');
		var message=JSON.stringify(message);
		document.cookie = "MessageCookie="+message+"; ";
		if (typeof api_used_global !== 'undefined') {
						rpath=rpath+"&api_used="+api_used_global;

			// variable is undefined
		}

		window.open(rpath+"&msg_type="+import_type+"&guest_user="+guestuser, "mywindow","menubar=1,resizable=1,width=700,height=500,scrollbars=1");
	}

	function chk_form(thisform)
	{

		var count	=	document.getElementById("count").value;
		var maxics	=	document.getElementById("maxics").value;
		var i,j=0;
		for(i=0; i<count; i++)
		if(document.getElementById("contact_"+i).checked)
		j++;

		if(document.getElementById("invite_limit")){
			if(j > document.getElementById("invite_limit").value)
			{
				alert("You can send out " +document.getElementById("invite_limit").value+ " invites!");
				return false;
			}
		}
		if(maxics<j)
		alert('You can\'t submit more than ' + maxics + ' invitations.');
		else if(!j)
		alert(Joomla.JText._('COM_INVITEX_EMPTY_EMAIL_MSG'));
		else
		thisform.submit();
	}

	function ltrim(s)
	{
		var l=0;
		while(l < s.length && s[l] == ' ')
		{	l++; }
		return s.substring(l, s.length);
	}

	function rtrim(s)
	{
		var r=s.length -1;
		while(r > 0 && s[r] == ' ')
		{	r-=1;	}
		return s.substring(0, r+1);
	}

	function trim(str)
	{
		return ltrim(rtrim(str));
	}

	function upload_manual(self_email,formName,user_is_a_guest)
	{
		//if guest..then set guest name in hidden field
		if(user_is_a_guest==1)
		{
			var valid_guest = set_guest_name(formName);
			if(!valid_guest)
			{
				return false;
			}
		}

		validate_manual();

	}

	function validate_manual()
	{
		var wrong_emails_str = document.getElementById("invitex_wrong_mails").value;
		var correct_emails_str = document.getElementById("invitex_correct_mails").value;

		var wrong_emails_cnt=0;

		if(!correct_emails_str && !wrong_emails_str)
		{
			alert(Joomla.JText._('ATLEAST_ONE'));
			return false;
		}

		if(trim(wrong_emails_str))
		{
			 wrong_emails_arr = wrong_emails_str.split(",");
			 var wrong_emails_cnt = wrong_emails_arr.length;
		}
		if(trim(correct_emails_str))
		{
		 correct_emails_arr = correct_emails_str.split(",");
		var  correct_emails_cnt = correct_emails_arr.length;
		}


		if(document.getElementById("invite_limit")){
			if(correct_emails_cnt > document.getElementById("invite_limit").value)
			{
				 alert(document.getElementById("invite_limit").value+ " " + Joomla.JText._('INVITES_LEFT_MSG'));

				 return false;
			}
		}

		if(wrong_emails_cnt)
		{
			alert(Joomla.JText._('INCORRECT_EMAILS_REMOVED'))
			return;
		}

		document.manualform.submit();
}

	function load_more_contacts(api_used,api_message_type,table_id)
	{
		var limit=document.getElementById("limit").value;
		var offset=document.getElementById("offset").value;
		document.getElementById("offset").value=Number(offset)+Number(limit);
		offset=document.getElementById("offset").value;
		var start=offset;
		var end=limit;

		techjoomla.jQuery.ajax({
			url: invitex_root_url+'index.php?option=com_invitex&controller=invites&task=get_contacts',
			type:'POST',
			async:true,
			dataType:'json',
			data:{offset:offset,limit:limit},
			timeout:15000,
			beforeSend:function(){
				/* Disable buttons. */
				techjoomla.jQuery('#load_more').prop('disabled', true);
				techjoomla.jQuery('#send_invites').prop('disabled', true);
				techjoomla.jQuery("#ajax-loading").show();
			},
			complete:function(){
				/* Enable buttons. */
				techjoomla.jQuery('#load_more').prop('disabled', false);
				techjoomla.jQuery('#send_invites').prop('disabled', false);
				techjoomla.jQuery("#ajax-loading").hide();
			},
			error:function(){
				alert(Joomla.JText._('COM_INVITEX_ERROR_LOADING_DOC'))
			},
			success: function(data)
			{
				if(data)
				{
					var contacts_info=[];
					contacts_info=data;
					if(contacts_info.length!=0)
					{
						var totalCount = techjoomla.jQuery("#invitex_invitee_info .invitex_info").length;
						var rc= document.getElementById(table_id).getElementsByTagName('tr').length;
						for(var i=0 ; i<contacts_info.length ; i++)
						{
							var newRow='<div class="invitex_info col-md-6 col-sm-12" id="invitex_info_'+totalCount+'">' ;
							newRow += '<div class="pull-left invitex-margin-right-15">' ;
							newRow += '<input type="checkbox" name="contacts['+contacts_info[i].name+']" id="contact_'+totalCount+ '" value="'+contacts_info[i].id+'" checked class="contacts_check" onclick="show_count()"/>';
							newRow += '</div>';
							if(contacts_info[i].hasOwnProperty('picture_url'))
							{
								newRow += '	<div class="picture pull-left" width="10%">'
								if(contacts_info[i].picture_url)
								{
									newRow += '<img src="'+contacts_info[i].picture_url+'" alt="" title="" width="50" height="50" style="display: block; margin: 0 auto; padding: 0.25em;" align="left" />';
								}
								else
								{
									newRow += '<img src="components/com_invitex/images/apis/anonymous.png" alt="NO IMAGE" title="NO IMAGE" width="50" height="50" />';
								}
								newRow += '</div>';
							}
							newRow += '<div class="info pull-left"><label for"contact_'+totalCount+'">';
							if(contacts_info[i].hasOwnProperty('name'))
							newRow += '<b>'+contacts_info[i].name + '</b><br />' + contacts_info[i].id;
							if(api_message_type=='email')
							newRow += contacts_info[i].id;
							newRow +='</label></div><div class="clearfix">&nbsp;</div></div>';
							techjoomla.jQuery("#"+table_id).append(newRow);
							rc++;
							totalCount++;
						}
						show_count();
					}
					else
					{
						document.getElementById('load_more').value=no_more_contacts_msg;
					}
				}
				else
				{
					document.getElementById('load_more').value=no_more_contacts_msg;
				}
			}
		});
	}

	function show_count()
	{

		techjoomla.jQuery("#selected_contact" ).html('');
		techjoomla.jQuery(".contacts_check:checked").each(function(){
			techjoomla.jQuery(this).parent().parent().clone().appendTo( "#selected_contact" );
			techjoomla.jQuery("#selected_contact .contacts_check").removeClass('contacts_check');
			techjoomla.jQuery("#selected_contact").find('input:checkbox').addClass('selected_contacts_check').attr('onclick','removeClone(this)');
		});

		techjoomla.jQuery("#select_count").html('');
		characters= techjoomla.jQuery("#selected_contact .selected_contacts_check:checked").length;
		techjoomla.jQuery("#selcted_contacts_title").html("<strong>("+ characters+")</strong>");
		return 1;
	}

	function display_method(li_id)
	{
		if(li_id!='sms_apis')
		{
			techjoomla.jQuery("#sms_active_tab").hide();
		}
		else
		{
			var form_name='sms_connect_form';
			var api_used='plug_techjoomlaAPI_sms';
			var api_message_type='sms';
			showinvitebuttondiv(li_id,'invite_sms_api');
			techjoomla.jQuery("#invite_sms_api form").attr("id",form_name);
			techjoomla.jQuery("#invite_sms_api form").attr("name",form_name);
			techjoomla.jQuery("#invite_sms_api form #api_used").attr("value",api_used);
			techjoomla.jQuery("#invite_sms_api form #api_message_type").attr("value",api_message_type);
			techjoomla.jQuery("#invite_sms_api").show();
			techjoomla.jQuery("#sms_apis").show();

		}

		techjoomla.jQuery(".invitex_ul li[name='inv_methods']").each(function()
		{
			var each_li_id=techjoomla.jQuery(this).attr('id'); // This is your rel value
			if(li_id == each_li_id)
			{
				techjoomla.jQuery(this).removeClass().addClass("invitex_active_li");
				techjoomla.jQuery(this).addClass("invitextopborder");
				techjoomla.jQuery('#'+each_li_id+'_content').css("display", "block");
			}
			else{
				techjoomla.jQuery(this).removeClass().addClass("invitex_li");
				techjoomla.jQuery('#'+each_li_id+'_content').css("display", "none");
			}
		});

	}

	function set_guest_name(formName)
	{

		var guest_name='';
		if(document.getElementById("guest_name" ))
		{
			guest_name=document.getElementById("guest_name" ).value;
		}
		if(guest_name)
		{
			techjoomla.jQuery('.guest_name_post').val(guest_name);
		}
		else
		{
			alert(Joomla.JText._('COM_INVITEX_GUEST_NAME_ERROR_MSG'))
			return false;
		}

		var Captcha_text=techjoomla.jQuery('#guest_info').serialize();
		var valid= '';
		techjoomla.jQuery.ajax({
					url: invitex_root_url+"index.php?option=com_invitex&controller=invites&task=isCaptchaCorrect",
					type: "POST",
					dataType: "json",
					data:Captcha_text,
					async:false,
					success: function(msg)
					{
						if(msg==1)
						{
							valid=1;
						}
						else
						{
							alert(Joomla.JText._('COM_INVITEX_CAPTCHA_ERROR_MSG'))
							valid=-1;
						}
					},
					error: function(){
						alert(Joomla.JText._('COM_INVITEX_CAPTCHA_ERROR_MSG'))
						valid=-1;
					}

				});
				if(valid==-1)
				{
					return false;
				}
				return true;
	}

	//function for sms validation and form submit.
	function upload_sms(invites_left_msg,formName,user_is_a_guest)
	{
		//if guest..then set guest name in hidden field
		var sms_cnt=0;
		if(user_is_a_guest==1)
		{
			var valid_guest = set_guest_name(formName);
			if(!valid_guest)
			{
				return false;
			}
		}
		var validateflag = document.formvalidator.isValid(document.forms[formName]);
			if(validateflag)
			{

				techjoomla.jQuery('.sms_repeating_block').each(function()
				{
					sms_cnt++;
				});

				//user limitation
				if(document.getElementById("invite_limit")){
					if(sms_cnt > document.getElementById("invite_limit").value)
					{
						 alert(document.getElementById("invite_limit").value+ " " + invites_left_msg);
						 return false;
					}
				}
				document.forms[formName].submit();
			}
		else
		{
			return false;
		}
	}


	function checkforalpha(el)
	{
		var i =0 ;
		for(i=0;i<el.value.length;i++){
		  if((el.value.charCodeAt(i) > 64 && el.value.charCodeAt(i) < 92) || (el.value.charCodeAt(i) > 96 && el.value.charCodeAt(i) < 123)) { alert(Joomla.JText._('COM_INVITEX_ENTER_NUMERICS')); el.value = el.value.substring(0,i); break;}
		}
	}

	function showinvitebuttondiv(thisli,id)
	{
		techjoomla.jQuery(".icon_ul").find("img").each(function(){
		techjoomla.jQuery(this).attr("class","inv_select_invite_img");
		});
		var class_nm=techjoomla.jQuery(thisli).attr("class");
		img=techjoomla.jQuery(thisli).find("img");
		//For openinviter
		if(class_nm=='inv_other_mmethods_li')
		{
			inv_method_title=techjoomla.jQuery(thisli).attr("title");
			techjoomla.jQuery(".inv_method_title").html("<b>"+inv_method_title+"</b>");
		}
		else
		{

			inv_method_title=techjoomla.jQuery(img).attr("title");
			techjoomla.jQuery(".inv_method_title").html("<b>"+inv_method_title+"</b>");
		}

		techjoomla.jQuery(img).attr("class","inv_selected_method_active");

		techjoomla.jQuery(img).attr("class","inv_selected_method_active");
		techjoomla.jQuery.each(techjoomla.jQuery('.tab-pane'),function(){

			document.getElementById(this.id).style.display="none";
		});

		if(id=='sms_apis')
		{
			var form_name='sms_connect_form';
			var api_used='plug_techjoomlaAPI_sms';
			var api_message_type='sms';
			techjoomla.jQuery("#invite_sms_api form").attr("id",form_name);
			techjoomla.jQuery("#invite_sms_api form").attr("name",form_name);
			techjoomla.jQuery("#invite_sms_api form #api_used").attr("value",api_used);
			techjoomla.jQuery("#invite_sms_api form #api_message_type").attr("value",api_message_type);
			techjoomla.jQuery("#invite_sms_api").show();
			techjoomla.jQuery("#sms_apis").show();
			return;
		}

		techjoomla.jQuery.each(techjoomla.jQuery('.tab-pane'),function(){
			if(this.id==id)
				document.getElementById(this.id).style.display="block";
			 else
			document.getElementById(this.id).style.display="none";
		});

	}


	function upload(val,formName,user_is_a_guest)
	{
		switch (val)
		{
			case 'invitex':
				if (document.getElementById("email_box").value == "") {
				alert(Joomla.JText._('COM_INVITEX_NOT_VALID_EMAIL'));
				return false;
				}
				if (document.getElementById("password_box").value == "") {
				alert(Joomla.JText._('COM_INVITEX_PASSWORD_ERROR_MSG'));
				return false;
				}
				if (document.getElementById("provider_box").value == "") {
				alert(Joomla.JText._('COM_INVITEX_EMPTY_PROVIDER_MSG'));
				return false;
				}
				if(user_is_a_guest==1)
				{
					var valid_guest = set_guest_name(formName);
						if(!valid_guest)
						{
							return false;
						}
				}
				document.emailimportform.submit();
				break;

				case 'social_invitex':
				if (document.getElementById("social_email").value == "") {
				alert(Joomla.JText._('COM_INVITEX_NOT_VALID_EMAIL'));
				return false;
				}
				if (document.getElementById("social_password").value == "") {
				alert(Joomla.JText._('COM_INVITEX_PASSWORD_ERROR_MSG'));
				return false;
				}
				if (document.getElementById("social_provider").value == "") {
				alert(Joomla.JText._('COM_INVITEX_EMPTY_PROVIDER_MSG'));
				return false;
				}
				if(user_is_a_guest==1)
				{
					var valid_guest = set_guest_name(formName);
						if(!valid_guest)
						{
							return false;
						}
				}
				document.socialimportform.submit();
				break;

				case 'csvupload':
				default:
				if (document.getElementById("csvfile").value == "") {
				alert(Joomla.JText._('COM_INVITEX_EMPTY_CSV_MSG'));
				return false;
				}
				if(user_is_a_guest==1)
				{
					var valid_guest = set_guest_name(formName);
						if(!valid_guest)
						{
							return false;
						}
				}
				document.csvform.submit();
				break;
		}
	}

	function display_api(thisli,form_name,img_path,img_name,api_used,api_message_type,user_is_a_guest,method_title)
	{
		/*Set Global var for using it in preview*/
		inv_messagae_type_preview=api_message_type;
		api_used_global=api_used;
		if(api_message_type=="sms")
		{
			showinvitebuttondiv(thisli,'invite_sms_api');
			techjoomla.jQuery("#invite_sms_api form").attr('id',form_name);
			techjoomla.jQuery("#invite_sms_api form").attr('name',form_name);
			techjoomla.jQuery("#invite_sms_api form #api_used").attr('value',api_used);
			techjoomla.jQuery("#invite_sms_api form #api_message_type").attr('value',api_message_type);

			var connect_btn= "<button id=\"form_connect_btn\" type=\"button\" class=\"btn btn-primary btn-large\" onclick=\"upload_sms(\'"+Joomla.JText._('INVITES_LEFT_MSG')+"\',\'"+form_name+"\',\'"+user_is_a_guest+"\')\"  >'"+send_invite_button_text+"'</button>";
			techjoomla.jQuery("#sms_connect_btn_div").html(connect_btn);

			techjoomla.jQuery("#invite_sms_api").show();
			techjoomla.jQuery("#sms_apis").show();
		}
		else
		{
			showinvitebuttondiv(thisli,invite_apis_form);
			techjoomla.jQuery("#invite_apis_form form").attr('id',form_name);
			techjoomla.jQuery("#invite_apis_form form").attr('name',form_name);
			techjoomla.jQuery("#invite_apis_form form #api_used").attr('value',api_used);
			techjoomla.jQuery("#invite_apis_form form #api_message_type").attr('value',api_message_type);

			var to_ste_name_guest="";
			if(user_is_a_guest==1){
				to_ste_name_guest="onclick=\"return(set_guest_name('"+form_name+"'))\" ";
				if(!to_ste_name_guest)
				{
					return false;
				}
			}

			var connect_image="<img  src=\'"+img_path+"large/"+img_name+"\' />";
			var connect_btn= "<button id=\"form_connect_btn\" type=\"submit\" class=\"btn btn-primary btn-large\" "+to_ste_name_guest+"  >'"+connect_invite_button_text+"'</button>";
			techjoomla.jQuery("#connect_btn_image_div").html(connect_image);
			techjoomla.jQuery(".inv_method_title").html(method_title);
			techjoomla.jQuery("#connect_btn_div").html(connect_btn);
			techjoomla.jQuery("#invite_apis_form").show();

			/* Start - Added in v2.9.7 for FB plugin*/
			if (api_used === 'plug_techjoomlaAPI_facebook' && facebook_inv_method === 'send-dialog')
			{
				var newHtml = "<a id=\"form_connect_btn\" onclick=\"sendFBRequest()\" class=\"btn btn-primary\">" + connect_invite_button_text + "</a>";
				techjoomla.jQuery(".social_email_label_personal_message").hide();
				techjoomla.jQuery(".social_email_personal_message").hide();
				techjoomla.jQuery("#form_connect_btn").hide();
				techjoomla.jQuery("#invtex_msg_preview").hide();
				techjoomla.jQuery("#form_dynamic_html").show();
				techjoomla.jQuery("#form_dynamic_html").html(newHtml);
			}
			else
			{
				techjoomla.jQuery(".social_email_label_personal_message").show();
				techjoomla.jQuery(".social_email_personal_message").show();
				techjoomla.jQuery("#form_connect_btn").show();
				techjoomla.jQuery("#invtex_msg_preview").show();
				techjoomla.jQuery("#form_dynamic_html").hide();
			}
			/* End - Added in v2.9.7*/
		}
	}

	function send_invitation()
	{
		document.inv_js_invitation_form.submit();
	}

	function toggleAll(element,form_name)
	{
		id=element.id;
		var form=document.forms[form_name];
		for(z=0; z<form.length;z++)
		{

			if(form[z].type == 'checkbox')
			{
				form[z].checked=element.checked;
			}
		}
		show_count();
	}

	function selectAll()
	{
		techjoomla.jQuery('#invitex_invitee_info .contacts_check').each(function(){
			techjoomla.jQuery(this).prop('checked', true);
		});
		show_count();
	}

	function diselectAll()
	{
		techjoomla.jQuery('#invitex_invitee_info .contacts_check').each(function(){
			techjoomla.jQuery(this).prop('checked', false);
		});
		show_count();
	}

	function selectAllFriends()
	{
		techjoomla.jQuery('#inv_js_invitation_list input').each(function(){
			techjoomla.jQuery(this).prop('checked', true);
		});
	}

	function deselectAllFriends()
	{
		techjoomla.jQuery('#inv_js_invitation_list input').each(function(){
			techjoomla.jQuery(this).prop("checked", false);
		});
	}

	function addClone_inv(rId,rClass,remove_button_nm, btn_icon)
	{
		var pre;
		var field_cnt;
		if(rId=='com_invitex_repeating_block_manual')
		{
			pre=field_lenght_manual;
			field_lenght_manual++;
			field_cnt=field_lenght_manual;


		}
		if(rId=='com_invitex_repeating_block_sms')
		{
			pre=field_lenght_sms;
			field_lenght_sms++;
			field_cnt=field_lenght_sms;
		}




		var num=techjoomla.jQuery("."+rClass).length;
		//var removeButton="<div class=' span3' >";
		var removeButton ="<button class='btn btn-small btn-danger' type='button' id='remove"+num+"'";
		removeButton+="onclick=\"removeClone_inv('invitex_container"+num+"','invitex_container');\" title= 'Remove' >";
		removeButton+='<i class="'+btn_icon+'"></i></button>';
		//removeButton+="</div>";

		var newElem=techjoomla.jQuery('#'+rId).clone().attr('id',rId+num);

		techjoomla.jQuery(newElem).children('.com_invitex_repeating_block').children('.control-group').children('.control').children().each(function()
		{
			var kid=techjoomla.jQuery(this);
			if(kid.attr('id')!=undefined)
			{
				var idN=kid.attr('id');
				kid.attr('id',idN+num).attr('id',idN+num);
				kid.attr('value','');
			}

			kid.attr('value','');

		});

		newnum = num+1;
		newElem.find('select[name=\"sms[1][sms_user_phno_code]\"]').attr({'name': 'sms['+newnum+'][sms_user_phno_code]','value':'' });
		newElem.find('input[name=\"sms[1][sms_user_name]\"]').attr({'name': 'sms['+newnum+'][sms_user_name]','value':'' });
		newElem.find('input[name=\"sms[1][sms_user_phno]\"]').attr({'name': 'sms['+newnum+'][sms_user_phno]','value':'' });
		techjoomla.jQuery('.'+rClass+':last').after(newElem);

		techjoomla.jQuery('div.'+rClass + ":last "+ ' .clone-button-div').append(removeButton);
		techjoomla.jQuery('input[name="sms['+newnum+'][sms_user_name]"]').val('');
		techjoomla.jQuery('input[name="sms['+newnum+'][sms_user_phno]"]').val('');
	}


	function removeClone_inv(rId,rClass,ids)
	{
		if(ids==undefined)
			techjoomla.jQuery('#'+rId).remove();
		else
			techjoomla.jQuery('#'+'invitex_container'+ids).remove();

	}

	function removeClone(this_clone)
	{
		if(techjoomla.jQuery(this_clone).prop('checked') == false)
		{
			techjoomla.jQuery(this_clone).parent().parent().remove();
			techjoomla.jQuery('#all_contact').find('#'+this_clone.id).removeAttr('checked');
		}

		techjoomla.jQuery("#select_count").html('');
		show_count();

	}

	function changevalguest(guestvalue){

			document.getElementById("guest").value=guestvalue;
	}

	function submit_adv_form(){
	//if guest..then set guest name in hidden field
	if(parseInt(isGuest)==1)
	{
		var valid_guest = set_guest_name('advanced_manualform');
		if(!valid_guest)
		{
			return false;
		}
	}
	var escapeLoop = 0;
	jQuery("input[id*='invitee_name']").filter(':visible').each(function () {
		var cValue = jQuery(this).val();
		if (cValue === '') {
			alert(Joomla.JText._('COM_INVITEX_EMPTY_NAME_MSG'))
			jQuery(this).focus();
			escapeLoop = 1;
			return false;
		}
	});

	if (escapeLoop === 1) {
		return false;
	}

	if (jQuery("input[id*='invitee_name']").filter(':visible').length === 0) {
		alert(Joomla.JText._('ATLEAST_ONE'))
		return false;
	}

	var escapeLoop = 0;
	jQuery("input[id*='invitee_email']").filter(':visible').each(function () {
		var cValue = jQuery(this).val();
		if (cValue === '')
		{
			alert(Joomla.JText._('COM_INVITEX_EMPTY_EMAIL_MSG'));
			jQuery(this).focus();
			escapeLoop = 1;
			return false;
		}
		else
		{
			var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
			if( !emailReg.test( jQuery(this).val() ) )
			{
				alert(Joomla.JText._('COM_INVITEX_NOT_VALID_EMAIL'));
				jQuery(this).focus();
				escapeLoop = 1;
				return false;
			}
		}
	});

	if (escapeLoop === 1) {
		return false;
	}

	document.advanced_manualform.submit();
}

function addAsFriend(friendToAdd)
{
	techjoomla.jQuery.ajax({
		url: invitex_root_url+'index.php?option=com_invitex&controller=invites&task=add_friend&action=add_friend&fuid='+friendToAdd,
		type:'POST',
		error:function(){
			alert(Joomla.JText._('COM_INVITEX_ERROR_LOADING_DOC'));
		},
		success:function(data)
		{
			var results = JSON.parse(data);

			if (results.msg == 'success')
			{
				techjoomla.jQuery("#friendtoadd"+results.invitee_user).hide();
				techjoomla.jQuery("#friendtoadd"+results.invitee_user).after("<b><p>"+ Joomla.JText._('CONNECTED')+"</p></b>");
			}
		}
	});
}

function unsubscribe(variable,value)
{
	techjoomla.jQuery.ajax({
		url: invitex_root_url+'index.php?option=com_invitex&task=unSubscribeConfirm',
		type:'POST',
		dataType:'json',
		data:{variable:variable,value:value},
		timeout:15000,
		error:function(){
			alert(Joomla.JText._('COM_INVITEX_ERROR_LOADING_DOC'))
		},
		success:function(data){
			if(data==1){
				techjoomla.jQuery('.before_unsub').hide();
				techjoomla.jQuery('.after_unsub').show();
			}
		}
	});
}

techjoomla.jQuery(document).ready(function() {
		if (/Android|webOS|iPhone|iPad|iPod|pocket|psp|kindle|avantgo|blazer|midori|Tablet|Palm|maemo|plucker|phone|BlackBerry|symbian|IEMobile|mobile|ZuneWP7|Windows Phone|Opera Mini/i.test(navigator.userAgent))
		{
			techjoomla.jQuery(".invitex-facebook-button").parent().hide();
		}

		/* JS to select first invitation method in the available invitation methods list on page load*/
		techjoomla.jQuery(".invitex-wrapper .inv-tabs li.active, .invitex_active_li").trigger('click');
});
