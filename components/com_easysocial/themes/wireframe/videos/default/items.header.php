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
<?php if ($this->isMobile() && $cluster) { ?>
<a class="btn btn-es-default-o btn-sm t-lg-mb--lg" href="<?php echo $cluster->getPermalink();?>">&larr; <?php echo JText::sprintf('COM_EASYSOCIAL_BACK_TO_' . strtoupper($cluster->getType()));?></a>
<?php } ?>

<?php if ($customFilter || $hashtags) { ?>
	<?php if ($customFilter) { ?>
		<div class="es-snackbar">
			<div class="es-snackbar__cell">
				<?php echo $customFilter->title;?>
			</div>
			<div class="es-snackbar__cell">
				<a href="javascript:void(0);" data-video-create-filter data-id="<?php echo $customFilter->id; ?>" data-cluster-type="<?php echo $type; ?>" data-uid="<?php echo $uid; ?>" class="t-lg-pull-right">
					<?php echo JText::_('COM_ES_EDIT'); ?>
				</a>
			</div>
		</div>
	<?php } ?>

	<?php if (!$customFilter && $hashtags) { ?>
		<div class="es-snackbar">
			<div class="es-snackbar__cell">
				<?php echo ES::tagFilter()->getLinks($hashtags, 'videos', $uid, $type); ?>
			</div>
		</div>
	<?php } ?>

<?php } else { ?>

	<?php if (isset($filter) && $filter == 'pending') { ?>
		<div class="es-snackbar"><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_PENDING_TITLE');?></div>
		<p class="pending-info"><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_PENDING_INFO');?></p>
	<?php } else { ?>

		<?php if ((isset($isFeatured) && $isFeatured) || (isset($filter) && $filter == 'featured')) { ?>
			<div class="es-snackbar">
				<?php echo JText::_("COM_EASYSOCIAL_VIDEOS_FEATURED_VIDEOS");?>

				<?php if ((isset($featuredVideoLink) && $featuredVideoLink)) { ?>
				<div class="es-snackbar__cell">
					<a href="<?php echo $featuredVideoLink; ?>" class="t-pull-right"><?php echo JText::_('COM_EASYSOCIAL_DASHBOARD_VIEW_ALL_LISTS'); ?></a>
				</div>
				<?php } ?>

			</div>
		<?php } else { ?>
			<div class="es-snackbar">
				<?php echo JText::_("COM_EASYSOCIAL_VIDEOS");?>
			</div>
		<?php } ?>

	<?php } ?>
<?php } ?>
