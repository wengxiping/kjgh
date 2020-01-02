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
<?php if ($activeCategory && $this->config->get('pages.layout.categoryheaders', true)) { ?>
<div class="t-lg-mb--xl">
	<?php echo $this->html('miniheader.pageCategory', $activeCategory); ?>
</div>
<?php } ?>

<?php echo $this->html('listing.loader', 'card', 4, 2, array('snackbar' => true, 'sortbar' => true)); ?>

<div data-result>

	<?php if ($featuredPages) { ?>
		<div class="es-snackbar">
			<div class="es-snackbar__cell">
				<h2 class="es-snackbar__title"><?php echo JText::_('COM_EASYSOCIAL_PAGES_FEATURED_PAGES');?></h2>
			</div>

			<div class="es-snackbar__cell">
				<a href="<?php echo ESR::pages(array('filter' => 'featured')); ?>" class="t-pull-right"><?php echo JText::_('COM_ES_VIEW_ALL'); ?></a>
			</div>
		</div>

		<?php echo $this->loadTemplate('site/pages/default/items.list', array('pages' => $featuredPages, 'heading' => 'COM_EASYSOCIAL_PAGES_FEATURED_PAGES', 'pagination' => false)); ?>
	<?php } ?>

	<?php if ($pages && $browseView && $sortItems) { ?>
		<div class="es-list-sorting-wrapper">
			<div class="es-list-sorting">
				<?php echo $this->html('form.popdown', 'sorting_test', $ordering, array(
					$this->html('form.popdownOption', 'latest', 'COM_ES_SORT_BY_LATEST', '', false, $sortItems->latest->attributes, $sortItems->latest->url),
					$this->html('form.popdownOption', 'name', 'COM_ES_SORT_BY_ALPHABETICALLY', '', false, $sortItems->name->attributes, $sortItems->name->url),
					$this->html('form.popdownOption', 'popular', 'COM_ES_SORT_BY_POPULARITY', '', false, $sortItems->popular->attributes, $sortItems->popular->url)
				)); ?>
			</div>
		</div>
	<?php } ?>

	<?php echo $this->html('listing.loader', 'card', 4, 2, array('snackbar' => true)); ?>

	<?php echo $this->html('html.snackbar', 'COM_EASYSOCIAL_PAGES'); ?>

	<div class="es-list-result">
		<div class="<?php echo !$pages && !$featuredPages ? 'is-empty' : '';?>" data-list>
			<?php if ($pages) { ?>
				<?php echo $this->loadTemplate('site/pages/default/items.list', array('pages' => $pages, 'heading' => 'COM_EASYSOCIAL_PAGES', 'pagination' => $pagination)); ?>
			<?php } ?>

			<?php echo $this->html('html.emptyBlock', $emptyText, 'fa-briefcase'); ?>
		</div>
	</div>
</div>
