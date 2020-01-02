<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<style type="text/css">
body .minicolors-swatch.minicolors-sprite.minicolors-input-swatch {
	top: 7px;
}
</style>

<div data-colorpicker>
	<input type="text" name="<?php echo $name;?>" class="o-form-control minicolors hex minicolors-input" value="<?php echo $value; ?>" style="padding-left: 30px;" />

	<?php if ($revert) { ?>
	<a href="javascript:void(0);" class="btn btn-pp-default-o" data-colorpicker-revert data-color="<?php echo $revert;?>">
		<i class="fa fa-undo"></i>
	</a>
	<?php } ?>
</div>

<?php if ($loadScript) { ?>
<script type="text/javascript">
PayPlans.ready(function($) {
	$('[data-colorpicker-revert]').on('click', function() {
		var button = $(this);
		var revert = button.data('color');
		var input = button.parent().find('input');

		input.val(revert);
		input.trigger('paste.minicolors');
	});
});
</script>
<?php } ?>
