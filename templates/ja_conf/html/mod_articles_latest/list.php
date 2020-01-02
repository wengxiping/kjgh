<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_latest
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
require_once(__DIR__.'/../../helper.php');
$moduleHeading = $params->get('module-heading');
$modMenu = $params->get('mod-menu');
$modAvatar = $params->get('mod-avatar');
$modCreated = $params->get('mod-created');
$modShow = $params->get('show-readmore');
$modTitle = $params->get('full-title');

?>
<div class="latestnews list">
	
	<div class="section-title visible-xs">
    <?php if($moduleHeading) : ?>
	    <div class="title-intro">
	      <?php echo $moduleHeading; ?>
	    </div>
    <?php endif; ?>

    <?php if($module->showtitle): ?>
			<h3 class="title-lead h1"><?php echo $module->title ?></h3>
		<?php endif; ?>
	</div>

	<div class="row">
		<?php $i=0; foreach ($list as $item) : ?>

		<?php if($i==0): ?>
			<div class="col-sm-6 col-left">
		<?php elseif ($i == (round(count($list) / 2)) && $i!=0): ?>
			</div>
			<div class="col-sm-6 col-right">
				<?php if(!$modTitle && ($moduleHeading || $module->showtitle)) : ?>
				<!-- Title Show On Desktop/Tablet -->
		  	<div class="section-title hidden-xs">
			    <?php if($moduleHeading) : ?>
			    <div class="title-intro">
			      <?php echo $moduleHeading; ?>
			    </div>
			    <?php endif; ?>

			    <?php if($module->showtitle): ?>
						<h3 class="title-lead h1"><?php echo $module->title ?></h3>
					<?php endif; ?>
				</div>
				<!-- // Title Show On Desktop/Tablet -->
				<?php endif; ?>
		<?php endif; ?>

			<div class="article-item <?php if(!$modAvatar) echo 'no-avatar' ;?>">
				<?php $images = json_decode($item->images); ?>

				<div class="img-wrap">
					<img src="<?php echo $images->image_intro ;?>" alt="<?php echo $item->title; ?>" />
				</div>

				<div class="article-detail" itemscope itemtype="https://schema.org/Article">
					<h3>
						<a href="<?php echo $item->link; ?>" itemprop="url">
							<span itemprop="name">
								<?php echo $item->title; ?>
							</span>
						</a>
					</h3>

					<?php $articleField = new JRegistry($item->attribs); ?>

					<?php if($articleField->get('position')) : ?>
						<div class="article-field">
							<?php echo $articleField->get('position') ;?>
						</div>
					<?php endif ;?>
					

					<?php if($modCreated) :?>
						<div class="created-by">
							<?php echo JText::sprintf('COM_CONTENT_WRITTEN_BY', '').' '.'<span>'.$item->author.'</span>'; ?>
						</div>
					<?php endif ;?>

					<?php $customs = JATemplateHelper::getCustomFields($item->created_by, 'user');?>

					<?php if($modAvatar) :?>
					<div class="avatar-image">
						<?php if(!empty($customs['avatar'])): ?>
							<img src="<?php echo $customs['avatar'] ?>" alt="<?php echo $item->created_by; ?>" />
						<?php else: ?>
							<img src="images/joomlart/avatar/default.png" alt="<?php echo $item->created_by; ?>"  />
						<?php endif; ?>
					</div>
					<?php endif; ?>

					<?php if($item->introtext) :?>
						<div class="intro">
							<?php echo $item->introtext; ?>
						</div>
					<?php endif ;?>

					<div class="more-link">
						<a href="<?php echo $item->link; ?>" title="<?php echo $item->title; ?>">
							<?php echo Jtext::_('TPL_MORE_INFO'); ?>
							<span class="fas fa-arrow-right"></span>
						</a>
					</div>
				</div>
			</div>

		<?php if(($i + 1)==count($list)): ?>
			</div> 
		<?php endif; ?>
		<?php $i++ ;endforeach; ?>
	</div>

	<?php if($modShow) :?>
	<div class="mod-action text-center">
			<a class="btn btn-lg btn-linear" href="<?php  echo JRoute::_("index.php?Itemid={$modMenu}"); ?>" title="View More">
				<?php echo Jtext::_('TPL_VIEW_MORE_SPEAKERS') ;?>
			</a>
	</div>
	<?php endif ;?>
</div>
