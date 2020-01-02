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
<div class="es-cards__item"
	data-album-item="<?php echo $album->uuid(); ?>"
	data-album-id="<?php echo $album->id; ?>"
	data-album-nextstart="<?php echo isset($nextStart) ? $nextStart : '-1' ; ?>"
	data-album-uid="<?php echo $lib->uid;?>"
	data-album-type="<?php echo $lib->type;?>"
>
	<div class="es-card es-card--album-item<?php echo !$photos ? ' is-empty' : ''; ?>">
		<div class="es-card__hd">
			<?php echo $this->includeTemplate('site/albums/items/cover', array('album' => $album, 'photos' => $photos)); ?>
		</div>
		<div class="es-card__bd es-card--border">
			<div class="es-card__title">
				<a href="<?php echo $album->getPermalink();?>" data-album-view-button><?php echo $album->_('title'); ?></a>
			</div>

			<div class="es-card__meta">
				<?php if ($album->tags) { ?>
					<div class="t-lg-mb--sm"><?php echo JText::_('COM_EASYSOCIAL_TAGGED_IN_THIS_ALBUM');?>:</div>
					<ul class="g-list-inline">
						<?php foreach ($album->tags as $tag) { ?>
							<?php $user = ES::user($tag->uid); ?>
							<?php if (!$user->isBlock()) { ?>
							<li class="t-lg-mr--md">
								<?php echo $this->html('avatar.user', $user, 'sm'); ?>
							</li>
							<?php } ?>
						<?php } ?>
					</ul>
				<?php } ?>
			</div>

			<?php if ($album->getLocation() && $this->config->get('photos.location')) { ?>
				<?php $location = $album->getLocation(); ?>
				<div class="es-card__meta"><i class="fa fa-map-marker-alt"></i>
					<u data-popbox="module://easysocial/locations/popbox"
					data-lat="<?php echo $location->latitude; ?>"
					data-lng="<?php echo $location->longitude; ?>"
					data-location-provider="<?php echo $this->config->get('location.provider'); ?>">
						<a href="<?php echo $location->getMapUrl(); ?>" target="_blank">
							<?php echo $location->getAddress(); ?>
						</a>
					</u>
				</div>
			<?php } ?>
		</div>
		<div class="es-card__ft es-card--border">
			<div class="t-lg-pull-left">
				<ul class="g-list-inline">
					<li class="t-lg-mr--lg">
						<i class="fa fa-user"></i>&nbsp; <?php echo $this->html('html.user', $album->user_id); ?>
					</li>

					<li class="t-lg-mr--lg">
						<i class="fa fa-heart"></i>&nbsp; <?php echo $album->getLikesCount();?>
					</li>
					<li class="t-lg-mr--lg">
						<i class="fa fa-comment"></i>&nbsp; <?php echo $album->getCommentsCount();?>
					</li>
					<li class="t-lg-mr--lg">
						<i class="far fa-image"></i>&nbsp; <?php echo $album->getTotalPhotos();?>
					</li>
					<li class="t-lg-mr--lg">
						<i class="fa fa-eye"></i>&nbsp; <?php echo $album->hits;?>
					</li>
					<?php if ($album->hasDate()) { ?>
					<li>
						<i class="fa fa-calendar"></i>&nbsp; <?php echo $this->html('string.date', $album->getAssignedDate(), "COM_EASYSOCIAL_ALBUMS_DATE_FORMAT"); ?>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>
	</div>

</div>
