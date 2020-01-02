<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if ($anchorLink) { ?>
<a href="<?php echo $category->getPermalink();?>" class="o-avatar <?php echo $this->config->get('layout.avatar.style') == 'rounded' ? 'o-avatar--rounded' : '';?> <?php echo $class;?>" data-id="<?php echo $category->id;?>">
<?php } ?>

	<img src="<?php echo $category->getAvatar();?>" alt="<?php echo $this->html('string.escape', $category->getTitle());?>" width="<?php echo $width;?>" height="<?php echo $height;?>" />

<?php if ($anchorLink) { ?>
</a>
<?php } ?>
