function moveUpItem(selectid){
	jQuery('#'+selectid+ ' option:selected').each(function(){
	jQuery(this).insertBefore(jQuery(this).prev());
});
	jQuery('#'+selectid+' option').each(function(){
	jQuery(this).attr('selected', 'selected');
	});
}
function moveDownItem(selectid){
	jQuery('#'+selectid+ ' option:selected').each(function(){
		jQuery(this).insertAfter(jQuery(this).next());
	});
	jQuery('#'+selectid+' option').each(function(){
		jQuery(this).attr('selected', 'selected');
	});
}


function allselections()
{

   var e = document.getElementById('selectionsemail');
   var s = document.getElementById('selectionssocial');
   e.disabled = true;
   s.disabled = true;
   var i = 0;
   var j = 0;
   var n = e.options.length;
   var m = s.options.length;
   for (i = 0; i < n; i++) {
	e.options[i].disabled = true;
	e.options[i].selected = true;
	}
    for (j = 0; j < m; j++) {
	s.options[j].disabled = true;
	s.options[j].selected = true;
	}
}

function enableselections()
{
	var e = document.getElementById('selectionsemail');
   	var s= document.getElementById('selectionssocial');
	e.disabled = false;
	s.disabled = false;
	var i = 0;
	var j = 0;
	var n = e.options.length;
	var m = s.options.length;
	for (i = 0; i < n; i++) {
	e.options[i].disabled = false;
	}
    for (j = 0; j < m; j++) {
	s.options[j].disabled = false;
	}

}

function trim(str)
{
	return ltrim(rtrim(str));
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

function autoup(url,confirm_msg)
{

  var r=confirm(confirm_msg);
 	if (r==true){
			var xmlhttp;
			if (window.XMLHttpRequest) {
  				// code for IE7+, Firefox, Chrome, Opera, Safari
  				xmlhttp=new XMLHttpRequest();
			}
			else{
  				// code for IE6, IE5
  				xmlhttp=new ActiveXObject('Msxml2.XMLHTTP.3.0');
			}
			xmlhttp.onreadystatechange=function()
			{
					if (xmlhttp.readyState==4 && xmlhttp.status==200)
					{
              alert(xmlhttp.responseText);
					}
			}
			xmlhttp.open('GET',url,true);
			xmlhttp.send(null);
  }
}
function populateUsers(success_msg)
{
	jQuery.ajax({
						url: 'index.php?option=com_invitex&tmpl=component&task=populateUsers',
						type: 'POST',
						dataType: 'json',
						error: function(){
							alert('Error loading document');
						},
						success: function(response)
						{
									jQuery('#populate_msg').attr("class" , "label label-success");
									jQuery('#populate_msg').text(success_msg);
									jQuery('#populate_button').attr("disabled", "disabled");

						}
	});
}

//~ jQuery( document ).ready(function() {
	//~ var value=jQuery('.alow_invite_after_login').val();
    //~ show_invite_after_login_div(value)
//~ });
//~
//~ function show_invite_after_login_div(value)
//~ {
	//~ if(value==1)
	//~ {
		//~ jQuery('.invite_after_login_div').show();
	//~ }
	//~ else
	//~ jQuery('.invite_after_login_div').hide();
//~ }

function toggle_display_point_integration()
{
	var val=jQuery("#jformpt_option option:selected").val();
	jQuery(".point_system_integration_display").hide();
	jQuery("#jform_point_label_inv_sent").hide();
	jQuery("#jform_inviter_point-lbl").hide();
	jQuery("#jform_invitee_point-lbl").hide();
	jQuery("#jform_point_system_integration_display_for_alta_id").hide();
	jQuery("#jform_point_system_integration_display_for_alpha_id").hide();
	jQuery("#jform_inviter_point_after_invite-lbl").hide();

	if(val=="espt")
	{
		jQuery("#easysocialpoint_note_desc").show();

	}
	else if(val=="jspt")
	{
		jQuery("#jomsocialpoint_note_desc").show();
	}
	else if(val=="alta")
	{
		jQuery(".point_system_integration_display_for_alta").show();
		jQuery("#jform_point_label_inv_sent").show();
		jQuery("#jform_inviter_point-lbl").show();
		jQuery("#jform_invitee_point-lbl").show();
		jQuery("#jform_point_system_integration_display_for_alta_id").show();
		jQuery("#jform_inviter_point_after_invite-lbl").show();
	}
	else if(val=="alpha")
	{
		jQuery(".point_system_integration_display_for_alpha").show();
		jQuery("#jform_point_label_inv_sent").show();
		jQuery("#jform_inviter_point-lbl").show();
		jQuery("#jform_invitee_point-lbl").show();
		jQuery("#jform_point_system_integration_display_for_plpha_id").show();
		jQuery("#jform_inviter_point_after_invite-lbl").show();
	}
}
