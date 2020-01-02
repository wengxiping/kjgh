// Reload parent's window
window.opener.location 	= "<?php echo $redirect;?>";

// We cannot just close the window.
// This timeout is to fix window close issue in chrome.
setTimeout(function(){
    window.close();
}, 1);


