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
<div class="es-search-mini-result-list__item" data-search-item
	data-search-item-id="<?php echo $item->id; ?>"
	data-search-item-type="<?php echo $item->utype; ?>"
	data-search-item-typeid="<?php echo $item->uid; ?>"
	data-search-custom-name="<?php echo $item->title; ?>"
	data-search-custom-avatar="<?php echo $item->image; ?>"
	>

	<a href="<?php echo $item->link; ?>">
		
		<?php if ($item->utype == 'EasySocial.Users') { ?>
		<span class="pull-left t-lg-mr--md">
			<?php echo $this->html('avatar.user', ES::user($item->uid), 'xs', false, false, '', false); ?>
		</span>
		<?php } else { ?>
		<span class="o-avatar o-avatar--xs pull-left t-lg-mr--md">
			<img class="" src="<?php echo $item->image; ?>" />
		</span>
		<?php } ?>
		

		<span class="es-search-mini-result-name">
			<?php if (JString::strlen($item->title) > 20) { ?>
				<?php echo JString::substr($item->title, 0, 20); ?> <?php echo JText::_('COM_EASYSOCIAL_ELLIPSES'); ?>
			<?php } else { ?>
				<?php echo $item->title; ?>
			<?php } ?>
		</span>
	</a>
</div>
