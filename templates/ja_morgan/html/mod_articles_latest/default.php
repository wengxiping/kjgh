<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_latest
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$modShow = $params->get('show-link');
$modCat = $params->get('title-category');
$modMenu = $params->get('link-category');

?>

<div class="wrap-last-article">
	<?php if($modShow) :?>
		<div class="view-all">
	        <a href="<?php  echo JRoute::_("index.php?Itemid={$modMenu}"); ?>" title="View More">
	            <?php echo $modCat ;?>
	            <span class="icon ion-ios-arrow-round-forward"></span>
	        </a>
		</div>
	<?php endif ;?>

	<div class="latest-news <?php echo $moduleclass_sfx; ?> mod-list">
		<div class="row equal-height equal-height-child">
			<?php foreach ($list as $item) : ?>
				<?php 
					$images = json_decode($item->images);
			?>
				<div class="col-sm-6 col-md-3 col">
					<div class="article-detail">
						<div class="intro-image">
							<?php if($images->image_intro): ?>
								<img src="<?php echo $images->image_intro ;?>" alt="<?php echo $item->title; ?>"/>
							<?php endif ;?>
						</div>

						<div class="article-content">
							<div class="date-create">
								<?php echo JHtml::_('date', $item->created, 'M d, Y'); ?>
							</div>

							<h3 class="heading-link">
								<a href="<?php echo $item->link; ?>">
									<span>
										<?php echo $item->title; ?>
									</span>
								</a>
							</h3>

							<div class="articles-introtext">
								<?php //echo $item->introtext; ?>

								<?php 
				                    $item->introtext = substr(strip_tags($item->introtext), 0, 100);
				                    $item->introtext = substr($item->introtext, 0, strrpos($item->introtext, ' ')) . " ...";
				                    echo $item->introtext;
				                ?>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>

