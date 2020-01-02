function verifyMtSubs(key) {
    jQuery('button#verify_access_key').prop('disabled',true);
    jQuery('button#verify_access_key').html('Checking...');
    jQuery('.subs-verified').html('');
    jQuery.getJSON("index.php?option=com_mtree&task=ajax&task2=checksubscription&format=json",{
            key: key
        })
        .done(function(data) {
            if(data.success) {
                jQuery('.subs-info').fadeOut('fast');

                jQuery('button#verify_access_key').css('display','none');
                console.log('✅  Verified! Expiry: ' + data.data.expiry);
                jQuery('#subs-name').html(data.data.first_name + ' ' + data.data.last_name).fadeIn('slow');
                jQuery('#subs-url').html(data.data.site_url).fadeIn('slow');
                jQuery('#subs-expiry').html(data.data.expiry).fadeIn('slow');
                jQuery('input[name=access_key]').prop('disabled',true);

                jQuery('#subs-status').fadeOut('fast');
                if(data.data.status == true) {

                    jQuery('#subs-status').html("Active");

                    console.log('active');
                } else {
                    jQuery('#subs-status').html("Expired");
                    console.log('expired');
                }
                jQuery('#subs-status').fadeIn('slow');

                jQuery('.subs-verified').html('✅  Verified');
            } else {
                jQuery('.subs-verified').html('❗  Verification failed.');
            }
        }).fail(function(data) {
            jQuery('button#verify_access_key').prop('disabled',false);
            jQuery('button#verify_access_key').html('Verify Again');
            jQuery('.subs-verified').html('❗  Verification failed.');
            console.log('.fail');
        });

    return false;
}

function pulse(el){
    el.fadeOut('fast');el.fadeIn('slow');
}