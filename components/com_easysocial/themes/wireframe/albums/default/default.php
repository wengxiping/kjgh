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
<div class="es-albums<?php echo (empty($albums)) ? '' : ' has-albums'; ?> is-<?php echo $lib->type;?>" data-albums>

	<?php echo $this->render('module', 'es-albums-between-months'); ?>

	<?php if ($data) { ?>
		<?php foreach ($data as $date => $albums) { ?>
			<div class="es-snackbar">
				<i class="fa fa-calendar"></i>&nbsp; <?php echo $date;?>
			</div>

			<?php if (!empty($albums)) { ?>
			<div class="es-cards es-cards--2">
				<?php foreach ($albums as $album) { ?>
					<?php echo $this->includeTemplate('site/albums/items/item', array('album' => $album, 'photos' => $album->photos)); ?>
				<?php } ?>
			</div>
			<?php } ?>

		<?php } ?>

		<?php if ($pagination) { ?>
			<?php echo $pagination->getListFooter('site');?>
		<?php } ?>

	<?php } else { ?>

		<?php if (!$coreAlbum) { ?>
			<div class="content-hint no-albums-hint">
				<?php echo JText::_($emptyText); ?>

				<?php if ($lib->canCreateAlbums()) { ?>
				<div>
					<a class="btn btn-es-primary btn-large" href="<?php echo $lib->getCreateLink();?>"><?php echo JText::_('COM_EASYSOCIAL_ALBUMS_CREATE_ALBUM'); ?></a>
				</div>
				<?php } ?>
			</div>
		<?php } else { ?>
			<?php echo $coreAlbum->renderItem(); ?>
		<?php } ?>

	<?php } ?>

</div>
