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
<div class="pp-tree">
	<div>
		<span class="t-lg-pr--md"><a href="javascript:void(0);" data-menulit-all><?php echo JText::_('COM_PP_MENULIST_SELECT_ALL'); ?></a></span> |
		<span class="t-lg-pl--md"><a href="javascript:void(0);"data-menulit-none><?php echo JText::_('COM_PP_MENULIST_SELECT_NONE'); ?></a></span>
	</div>
	<hr />
<?php foreach ($menus as $menu) { ?>
	<div class="">
		<b><?php echo $menu->title; ?></b>
	</div>

	<?php if ($menu->links) { ?>
		<?php foreach ($menu->links as $item) { ?>
		<?php
			$checked = '';
			if ($selected) {
				$checked = in_array($item->value, $selected) ? ' checked="checked"' : '';
			}
		?>
		<div class="tree-control">
			<label for="<?php echo $item->value;?>" class="checkbox">
				<input data-menu-item type="checkbox" id="<?php echo $item->value;?>" value="<?php echo $item->value;?>" name="<?php echo $name;?>[]"<?php echo $checked;?> />
				<div class="tree-title">
					<?php echo str_repeat( '<span class="gi"></span>' , $item->level );?> <?php echo $item->text . ' (Alias: ' . $item->alias . ')';?>
					<?php echo (!$item->published) ? '<span class="o-label o-label--warning-o">' . JText::_('COM_PP_MENULIST_UNPUBLISHED') . '</span>' : ''; ?>
				</div>
			</label>
		</div>
		<?php } ?>
	<?php } ?>

<?php } ?>
</div>
