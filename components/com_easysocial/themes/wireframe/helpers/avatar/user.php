<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="o-avatar-status <?php echo $this->config->get('layout.avatar.style') == 'rounded' ? 'o-avatar-status--rounded' : '';?> <?php echo $user->isOnline() ? 'is-online' : 'is-offline';?>">

	<?php if ($showOnlineState) { ?>
		<div class="o-avatar-status__indicator"></div>
	<?php } ?>

	<?php if ($anchorLink) { ?>
		<a href="<?php echo $user->getPermalink();?>" class="o-avatar <?php echo $this->config->get('layout.avatar.style') == 'rounded' ? 'o-avatar--rounded' : '';?> <?php echo $class;?>"
			<?php echo $showPopbox ? 'data-popbox="module://easysocial/profile/popbox"' : '';?>
				data-user-id="<?php echo $user->id;?>"
			<?php if ($popboxPosition) { ?>
				data-popbox-position="<?php echo $popboxPosition;?>"
			<?php } ?>
		>
	<?php } else { ?>
		<div class="o-avatar<?php echo $class;?> <?php echo $this->config->get('layout.avatar.style') == 'rounded' ? 'o-avatar--rounded' : '';?>">
	<?php } ?>
		<img src="<?php echo $avatar;?>" alt="<?php echo $this->html('string.escape', $user->getName());?>" width="<?php echo $width;?>" height="<?php echo $height;?>" />
	<?php if ($anchorLink) { ?>
		</a>
	<?php } else { ?>
		</div>
	<?php } ?>
</div>
