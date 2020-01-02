<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<script type="text/javascript">
$(document).ready(function() {

	<?php if (ES_INSTALLER == 'full') { ?>
		$('[data-installation-form]').submit();
	<?php } ?>

	<?php if (ES_INSTALLER == 'launcher') { ?>

	var loading = $('[data-checking]');
	var form = $('[data-installation-form]');
	var submit = $('[data-installation-submit]');

	submit.addClass('hide');

	// Validate api key
	$.ajax({
		type: 'POST',
		url: '<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&ajax=1&controller=license&task=verify',
	}).done(function(result) {

		// Hide the loading
		loading.addClass('hide');

		// User is not allowed to install
		if (result.state == 400) {

			// Set the error message
			$('[data-api-errors]').removeClass('hide');
			$('[data-error-message]').html(result.message);
			$('[data-source-method]').addClass('hide');
			return false;
		}

		// Valid licenses
		if (result.state == 200) {
			var submit = $('[data-installation-submit]');
			var licenses = $('[data-licenses]');
			var licensePlaceholder = $('[data-licenses-placeholder]');

			submit.removeClass('hide');

			// If there are multiple licenses, we need to request them to submit
			if (result.licenses.length > 1) {
				licenses.removeClass('hide');

				var output = $('<div>').html(result.html);
				output.find('select')
					.css('font-size', '13px')
					.css('padding', '6px')
					.css('width', '100%');

				licensePlaceholder.append(output);

				// Change the behavior of form submission
				submit.on('click', function() {
					form.submit();
				});
				return;
			}

			// If the user only has 1 license, just submit this immediately.
			licensePlaceholder.append(result.html);
			form.submit();
		}
	});
	<?php } ?>
});
</script>
<form action="index.php?option=com_easysocial" method="post" name="installation" data-installation-form>
	<div class="hide alert alert-danger" data-source-errors data-api-errors>
		<h3 class="text-center">No License Found</h3>

		<p class="text-center" style="margin: 20px 0;" data-error-message>
			Sorry, but the API key that you have provided does not have a valid subscription. Please try again, or if you still have technical difficulties, please contact our support team.
		</p>

		<div class="text-center">
			<a href="https://stackideas.com/forums" class="btn btn-danger" target="_blank">Contact Support</a>
		</div>
	</div>

	<div class="form-inline hide" data-licenses>
		<div>
			<h3 style="text-decoration: underline;">Please Select A License</h3>
			<p style="margin: 25px 0;">Our system detected multiple licenses from your account. In order to proceed with the installation, please choose a license to associate with your installation</p>
			<div data-licenses-placeholder></div>
		</div>
	</div>

	<div class="installation-methods">
		<?php if (ES_INSTALLER == 'launcher') { ?>
		<div class="text-center" data-checking>
			<b class="ui loader" style="width: 48px; height: 48px;"></b>&nbsp;
			<b style="display: block; color: #666;margin-top: 20px;font-size: 24px;">Checking for valid licenses ...</b>
		</div>
		<input type="hidden" name="method" value="network" />
		<?php } ?>

		<?php if (ES_INSTALLER == 'full' || ES_BETA) { ?>
		<input type="hidden" name="method" value="directory" />
		<?php } ?>
	</div>

	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="active" value="<?php echo $active; ?>" />
	<input type="hidden" name="update" value="<?php echo $update;?>" />
</form>
