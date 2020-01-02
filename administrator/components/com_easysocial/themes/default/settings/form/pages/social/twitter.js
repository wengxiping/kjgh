EasySocial.require()
.done(function($) {

    var oauthURIinput = $('[data-oauthuri-input]');
    var oauthURIbutton = $('[data-oauthuri-button]');

    oauthURIbutton.on('click', function() {

		// change tooltip display word
		$(this).attr('data-original-title', '<?php echo JText::_('COM_ES_COPIED_TOOLTIP')?>').tooltip('show');

    	// retrieve the input id
		var oauthInputId = $(this).siblings().attr('id');
		var selectedText = document.getElementById(oauthInputId);

		selectedText.select();
		document.execCommand("Copy");
    });

    // change back orginal value after mouse out
    oauthURIbutton.on('mouseout', function() {

		// change tooltip display word
		$(this).attr('data-original-title', '<?php echo JText::_('COM_ES_COPY_TOOLTIP')?>').tooltip('show');
    });    
});
