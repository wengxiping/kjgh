<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if ($image) { ?>
<div class="t-lg-mb--md">
	<div class="pp-img-holder">
		<div class="pp-img-holder__remove" >
			<a href="javascript:void(0);" data-remove-image>
				<i class="fa fa-times"></i>
			</a>
		</div>
		<img src="<?php echo rtrim(JURI::root(), '/').$image; ?>" width="120" data-image-source" />
	</div>
</div>
<?php } ?>

<div>
	<input type="file" name="<?php echo $name;?>" id="<?php echo $name;?>" <?php echo $attributes;?> />
</div>