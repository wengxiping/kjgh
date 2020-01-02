<?php if (isset($redirectUrl) && $redirectUrl) { ?>

	PayPlans.ready(function($) {

		window.onload = function(){
			setTimeout(function() {
				window.location.href = "<?php echo PPR::_($redirectUrl, false);?>";
			}, 300);
		}
	});
<?php } ?>