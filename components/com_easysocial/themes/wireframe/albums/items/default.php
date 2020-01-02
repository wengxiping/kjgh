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
<div>
	<?php if (!empty($albums)) { ?>
	<div class="es-cards es-cards--2">
		<?php foreach ($albums as $album) { ?>
			<?php $albumPhotos = isset($photos[$album->id]) ? $photos[$album->id] : array(); ?>
			<?php echo $this->includeTemplate('site/albums/items/item', array('album' => $album, 'photos' => $albumPhotos)); ?>
		<?php } ?>
	</div>
	<?php } ?>

	<?php echo $pagination->getListFooter('site');?>

	<?php if (!$albums) { ?>
	<div class="content-hint no-albums-hint">
		<?php echo JText::_('COM_EASYSOCIAL_NO_ALBUM_AVAILABLE_' . strtoupper($filter)); ?>

		<?php if ($filter != 'favourite') { ?>
		<div>
			<a href="<?php echo $lib->getCreateLink();?>" class="btn btn-es-primary btn-large"><?php echo JText::_('COM_EASYSOCIAL_ALBUMS_CREATE_ALBUM'); ?></a>
		</div>
		<?php } ?>
	</div>
	<?php } ?>
</div>
