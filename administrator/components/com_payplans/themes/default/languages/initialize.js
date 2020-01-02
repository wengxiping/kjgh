
jQuery(document).ready(function($) {

	PayPlans.ajax('admin/views/languages/getLanguages', {})
		.done(function() {
			window.location = '<?php echo rtrim(JURI::root(), '/');?>/administrator/index.php?option=com_payplans&view=languages';
		})
		.fail(function(html, message) {
			$('[data-languages-wrapper]').addClass('hide');
			$('[data-initialize-error]').removeClass('hide');
			$('[data-initialize-error]').html(html);
            return;			
		});
});