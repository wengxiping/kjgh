<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if (count($users) > 1) { ?>
	<div class="o-avatar-group-<?php echo $count;?>">
		<?php foreach ($users as $user) { ?>
		<div class="<?php echo $class;?>">
			<img src="<?php echo $user->getAvatar(); ?>" alt="<?php echo $this->html('string.escape', $user->getName());?>" width="<?php echo $width;?>" height="<?php echo $height;?>" />
		</div>
		<?php } ?>
	</div>
<?php } else { ?>
	<?php $user = $users[0]; ?>
	<div class="o-avatar-group-1 <?php echo $this->config->get('layout.avatar.style') == 'rounded' ? 'o-avatar-rounded' : '';?>">
		<div class="<?php echo $class ;?>">
			<img src="<?php echo $user->getAvatar(); ?>" alt="<?php echo $this->html('string.escape', $user->getName());?>" width="<?php echo $width;?>" height="<?php echo $height;?>" />
		</div>
	</div>
<?php } ?>
