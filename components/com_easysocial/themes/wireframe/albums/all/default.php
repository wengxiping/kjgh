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
<div class="es-container es-media-browser layout-album <?php echo !$albums ? '' : ' has-albums'; ?> is-<?php echo $lib->type;?>" data-albums data-es-container>

	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->output('site/albums/all/mobile.filters'); ?>
	<?php } ?>

	<div class="es-content">

		<div class="es-list-sorting">
			<?php echo $this->html('form.popdown', 'sorting', $sorting, array(
				$this->html('form.popdownOption', 'latest', 'COM_ES_SORT_BY_LATEST', '', false, $sortItems->latest->attributes, $sortItems->latest->url),
				$this->html('form.popdownOption', 'alphabetical', 'COM_ES_SORT_BY_ALPHABETICALLY', '', false, $sortItems->alphabetical->attributes, $sortItems->alphabetical->url),
				$this->html('form.popdownOption', 'popular', 'COM_ES_SORT_BY_MOST_VIEWS', '', false, $sortItems->popular->attributes, $sortItems->popular->url),
				$this->html('form.popdownOption', 'likes', 'COM_ES_SORT_BY_MOST_LIKES', '', false, $sortItems->likes->attributes, $sortItems->likes->url)
			)); ?>
		</div>

		<div class="es-snackbar">
			<h1 class="es-snackbar__title"><?php echo JText::_('COM_EASYSOCIAL_ALBUMS_PHOTO_ALBUMS'); ?></h1>
		</div>

		<div>
			<?php echo $this->render('module', 'es-albums-before-contents'); ?>

			<div class="es-list-result" data-wrapper>
				<?php echo $this->html('html.loading'); ?>

				<div class="es-albums" data-contents>
					<?php echo $this->includeTemplate('site/albums/items/default'); ?>
				</div>

			</div>

			<?php echo $this->render('module', 'es-albums-after-contents'); ?>
		</div>
	</div>
</div>
