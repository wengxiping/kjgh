<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="mod-es mod-es-sidebar-polls <?php echo $this->lib->getSuffix();?>"
	data-es-video-filters
	data-uid="<?php echo $cluster ? $cluster->id : '' ?>"
	data-type="<?php echo $cluster ? $cluster->getType() : '' ?>"
	data-active="<?php echo !$filter ? 'all' : $filter;?>"
>

	<div data-sidebar class="es-sidebar">
		<?php echo $this->lib->render('module', 'es-videos-sidebar-top', 'site/dashboard/sidebar.module.wrapper'); ?>

		<?php if ($allowCreation) { ?>
		<a class="btn btn-es-primary btn-block t-lg-mb--xl" href="<?php echo $createLink;?>">
			<?php echo JText::_('COM_EASYSOCIAL_VIDEOS_ADD_VIDEO');?>
		</a>
		<?php } ?>

		<div class="es-side-widget">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_VIDEOS'); ?>

			<div class="es-side-widget__bd">
				<ul data-es-videos-filters="" class="o-tabs o-tabs--stacked">
					<li class="o-tabs__item has-notice <?php echo ($filter == '' || $filter == 'all') ? 'active' : '';?>" data-filter-item data-type="all">
						<a href="<?php echo $adapter->getAllVideosLink();?>"
							title="<?php echo $titles->all;?>"
							class="o-tabs__link">
							<span><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_FILTERS_ALL_VIDEOS');?></span>
						</a>
						<span class="o-tabs__bubble" data-counter><?php echo $counters->videos;?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>

					<li class="o-tabs__item has-notice <?php echo $filter == 'featured' ? 'active' : '';?>" data-filter-item data-type="featured">
						<a href="<?php echo $adapter->getAllVideosLink('featured');?>"
							title="<?php echo $titles->featured;?>"
							class="o-tabs__link">
							<span><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_FILTERS_FEATURED_VIDEOS');?></span>
						</a>
						<span class="o-tabs__bubble" data-counter data-total-featured><?php echo $counters->featured;?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>

					<?php if ($filtersAcl->mine) { ?>
					<li class="o-tabs__item has-notice <?php echo $filter == 'mine' ? 'active' : '';?>" data-filter-item data-type="mine">
						<a href="<?php echo ESR::videos(array('filter' => 'mine'));?>"
							title="<?php echo $titles->mine;?>"
							class="o-tabs__link">
							<span><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_FILTERS_MY_VIDEOS');?></span>
						</a>
						<span class="o-tabs__bubble" data-counter data-total-created><?php echo $counters->user;?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>
					<?php } ?>

					<?php if ($filtersAcl->pending) { ?>
					<li class="o-tabs__item has-notice <?php echo $filter == 'pending' ? 'active' : '';?>" data-filter-item data-type="pending">
						<a href="<?php echo ESR::videos(array('filter' => 'pending'));?>"
							title="<?php echo $titles->pending;?>"
							class="o-tabs__link">
							<span><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_FILTERS_PENDING_VIDEOS');?></span>
						</a>
						<span class="o-tabs__bubble" data-counter data-total-pending><?php echo $counters->pending;?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>

		<?php if ($canCreateFilter && $browseView) { ?>
		<hr class="es-hr" />
		<div class="es-side-widget" data-section data-type="custom-filters">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_CUSTOM_FILTERS'); ?>

			<div class="es-side-widget__bd">
				<div class="es-side-widget__filter">
					<ul class="o-tabs o-tabs--stacked" data-section-lists>
						<?php if ($customFilters) { ?>
							<?php foreach ($customFilters as $customFilter) { ?>
							<li class="o-tabs__item <?php echo $filter == 'customFilter' && $activeCustomFilter && $activeCustomFilter->id == $customFilter->id ? 'active' : '';?>" data-filter-item data-type="hashtag" data-tag-id="<?php echo $customFilter->id ?>">
								<a href="<?php echo $customFilter->permalink; ?>"
									title="<?php echo JText::_($customFilter->title); ?>"
									class="o-tabs__link"
								>
									<?php echo '#' . $customFilter->title; ?>
								</a>
							</li>
							<?php } ?>
						<?php } ?>
					</ul>

					<?php if (!$customFilters) { ?>
					<div class="t-text--muted">
						<?php echo JText::_('COM_EASYSOCIAL_NO_CUSTOM_FILTERS_AVAILABLE'); ?>
					</div>
					<?php } ?>
				</div>
			</div>

			<a href="<?php echo $createCustomFilterLink;?>" class="btn btn-es-primary-o btn-block t-lg-mt--xl"
				data-video-create-filter
				data-type="videos"
				data-uid="<?php echo $uid;?>"
				data-cluster-type="<?php echo $type;?>"
			>
				<?php echo JText::_('COM_ES_NEW_FILTER'); ?>
			</a>
		</div>
		<?php } ?>

		<?php if (($browseView || $isCluster || $isUserProfileView) && $categories) { ?>
		<hr class="es-hr" />
		<div class="es-side-widget">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_VIDEOS_CATEGORIES'); ?>

			<div class="es-side-widget__bd">
				<?php echo $this->lib->html('categories.sidebar', SOCIAL_TYPE_VIDEO, $activeCategory) ?>
			</div>
		</div>
		<?php } ?>

		<?php echo $this->lib->render('module', 'es-videos-sidebar-bottom', 'site/dashboard/sidebar.module.wrapper'); ?>
	</div>
</div>

<script type="text/javascript">
EasySocial
.require()
.script('site/videos/filter')
.done(function($) {

	var wrapper = $('[data-es-video-filters]'),
	uid = wrapper.data('uid'),
	type = wrapper.data('type')
	active = wrapper.data('active');

	$('body').addController(EasySocial.Controller.Videos.Filter, {
		"uid": uid,
		"type": type,
		"active": active
	});
});
</script>
