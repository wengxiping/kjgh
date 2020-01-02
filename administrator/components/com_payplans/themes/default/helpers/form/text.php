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
<?php if ($size) { ?>
<div class="row">
	<div class="col-sm-<?php echo $size;?>">
<?php } ?>

	<?php if ($prefix || $postfix) { ?>
	<div class="o-input-group">
	<?php }?>

		<?php if ($prefix) { ?>
		<span class="o-input-group__prepend">
			<span class="o-input-group-text"><?php echo JText::_($prefix); ?></span>
		</span>
		<?php } ?>

		<input type="text" name="<?php echo $name;?>" class="o-form-control <?php echo $classes;?>" <?php echo $attributes;?> value="<?php echo $value;?>" id="<?php echo $id; ?>" placeholder="<?php echo JText::_($placeholder);?>" />

		<?php if ($postfix) { ?>
		<span class="o-input-group__append">
			<span class="o-input-group-text">
				<?php echo JText::_($postfix); ?>
			</span>
		</span>
		<?php } ?>

	<?php if ($prefix || $postfix) { ?>
	</div>
	<?php } ?>

<?php if ($size) { ?>
	</div>
</div>
<?php } ?>

