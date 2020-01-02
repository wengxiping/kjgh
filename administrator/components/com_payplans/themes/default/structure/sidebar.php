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
<div class="app-sidebar app-sidebar-collapse" data-sidebar>

	<ul class="app-sidebar-nav list-unstyled">
		<?php foreach ($menus as $item) { ?>
		<li class="sidebar-item menuItem <?php echo $item->active ? 'active' : ''; ?>" data-sidebar-item>
			<a href="<?php echo $item->link; ?>" data-sidebar-parent data-childs="<?php echo isset($item->childs) ? count($item->childs) : 0;?>">
				<i class="fa <?php echo $item->class; ?>"></i><span><?php echo $item->title; ?></span>
				<span class="badge"></span>
			</a>


			<?php if (isset($item->childs) && $item->childs) { ?>
			<ul class="dropdown-menu<?php echo $item->active ? ' in' : '';?>" id="menu-<?php echo $item->uid;?>" data-sidebar-child>
				
				<?php foreach ($item->childs as $child) { ?>
					<li class="<?php echo $child->active ? 'active' : '';?>">
						<a href="<?php echo $child->link;?>">
							<span><?php echo JText::_($child->title); ?></span>
						</a>
						<span class="badge"><?php echo $child->count > 0 ? $child->count : ''; ?></span>
					</li>
				<?php } ?>
			</ul>
			<?php } ?>
		</li>
		<?php } ?>
	</ul>

</div>