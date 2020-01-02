<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_category
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$modShow = $params->get('show-link');
$modCat = $params->get('title-category');
$modMenu = $params->get('link-category');

// Template helper
JLoader::register('JATemplateHelper', T3_TEMPLATE_PATH . '/helper.php');

?>
<div class="category-grid-view<?php echo $moduleclass_sfx; ?>">
	<?php if ($grouped) : ?>
		<?php foreach ($list as $group_name => $group) : ?>
		<li>
			<div class="mod-articles-category-group"><?php echo JText::_($group_name); ?></div>
			<ul>
				<?php foreach ($group as $item) : ?>
					<li>
						<?php if ($params->get('link_titles') == 1) : ?>
							<a class="mod-articles-category-title <?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
								<?php echo $item->title; ?>
							</a>
						<?php else : ?>
							<?php echo $item->title; ?>
						<?php endif; ?>

						<?php if ($item->displayHits) : ?>
							<span class="mod-articles-category-hits">
								(<?php echo $item->displayHits; ?>)
							</span>
						<?php endif; ?>

						<?php if ($params->get('show_author')) : ?>
							<span class="mod-articles-category-writtenby">
								<?php echo $item->displayAuthorName; ?>
							</span>
						<?php endif; ?>

						<?php if ($item->displayCategoryTitle) : ?>
							<span class="mod-articles-category-category">
								(<?php echo $item->displayCategoryTitle; ?>)
							</span>
						<?php endif; ?>

						<?php if ($item->displayDate) : ?>
							<span class="mod-articles-category-date"><?php echo $item->displayDate; ?></span>
						<?php endif; ?>

						<?php if ($params->get('show_introtext')) : ?>
							<p class="mod-articles-category-introtext">
								<?php echo $item->displayIntrotext; ?>
							</p>
						<?php endif; ?>

						<?php if ($params->get('show_tags', 0) && $item->tags->itemTags) : ?>
							<div class="mod-articles-category-tags">
								<?php echo JLayoutHelper::render('joomla.content.tags', $item->tags->itemTags); ?>
							</div>
						<?php endif; ?>

						<?php if ($params->get('show_readmore')) : ?>
							<p class="mod-articles-category-readmore">
								<a class="mod-articles-category-title <?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
									<?php if ($item->params->get('access-view') == false) : ?>
										<?php echo JText::_('MOD_ARTICLES_CATEGORY_REGISTER_TO_READ_MORE'); ?>
									<?php elseif ($readmore = $item->alternative_readmore) : ?>
										<?php echo $readmore; ?>
										<?php echo JHtml::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
											<?php if ($params->get('show_readmore_title', 0) != 0) : ?>
												<?php echo JHtml::_('string.truncate', $this->item->title, $params->get('readmore_limit')); ?>
											<?php endif; ?>
									<?php elseif ($params->get('show_readmore_title', 0) == 0) : ?>
										<?php echo JText::sprintf('MOD_ARTICLES_CATEGORY_READ_MORE_TITLE'); ?>
									<?php else : ?>
										<?php echo JText::_('MOD_ARTICLES_CATEGORY_READ_MORE'); ?>
										<?php echo JHtml::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
									<?php endif; ?>
								</a>
							</p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</li>
		<?php endforeach; ?>
	<?php else : ?>
		<div id="owl-view-<?php echo $module->id; ?>" >
			<div class="owl-carousel owl-theme">
				<?php foreach ($list as $item) : ?>
					<div class="item-inner">
						<?php
							// Custom field
							$extrafields = new JRegistry($item->attribs);
							$starHotel = $extrafields->get('star-hotel');
							$location = $extrafields->get('location');

							// Intro Image
							$introImage = json_decode($item->images)->image_intro;

							$color = '';
							$customs 		= JATemplateHelper::getCustomFields($item->catid, 'category');

								if(empty($customs)) :
									$color = "default";
								else: 
									$color = $customs['colors'];
								endif;
							// add color end

							// Voting
							if (isset($item->rating_sum) && $item->rating_count > 0) {
					        $item->rating = round($item->rating_sum / $item->rating_count, 1);
					        $item->rating_percentage = $item->rating_sum / $item->rating_count * 20;
					    } else {
					        if (!isset($item->rating)) $item->rating = 0;
					        if (!isset($item->rating_count)) $item->rating_count = 0;
					        $item->rating_percentage = $item->rating * 20;
					    }
					    $uri = JUri::getInstance();
						?>
						<!-- Intro Image -->
						<div class="intro-image">
							<?php if($introImage) : ?>
								<img src="<?php echo $introImage ;?>" alt="Intro Image" />
							<?php else : ?>
								<img src="images/joomlart/default.jpg" alt="No Image" />
							<?php endif ;?>

							<?php if ($item->displayCategoryTitle) : ?>
								<span class="category <?php echo $color ;?>">
									<?php echo $item->displayCategoryTitle; ?>
								</span>
							<?php endif; ?>
						</div>

						<div class="article-info show-vote">
							<!-- Title -->
							<div class="title">
								<?php if ($params->get('link_titles') == 1) : ?>
								<a class="mod-articles-category-title <?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
									<?php echo $item->title; ?>
								</a>
								<?php else : ?>
									<?php echo $item->title; ?>
								<?php endif; ?>
								<?php if($starHotel):?>
									<!-- Show voting form -->
							    <div class="rating-info pd-rating-info">
							      <div class="rating-form no-action">
						          <ul class="rating-list">
					              <li class="rating-current" style="width:<?php echo ($starHotel / 5) * 100; ?>%;"></li>
					              <li><span title="<?php echo JText::_('JA_1_STAR_OUT_OF_5'); ?>" class="one-star"></span></li>
					              <li><span title="<?php echo JText::_('JA_2_STARS_OUT_OF_5'); ?>" class="two-stars"></span></li>
					              <li><span title="<?php echo JText::_('JA_3_STARS_OUT_OF_5'); ?>" class="three-stars"></span></li>
					              <li><span title="<?php echo JText::_('JA_4_STARS_OUT_OF_5'); ?>" class="four-stars"></span></li>
					              <li><span title="<?php echo JText::_('JA_5_STARS_OUT_OF_5'); ?>" class="five-stars"></span></li>
						          </ul>
							      </div>
							    </div>
							  	<!-- End showing -->
						  	<?php endif ;?>
							</div>	

							<div class="article-meta">
							  <?php if($location):?>
							  	<div class="info-item articles-location">
										<span class="fa fa-map-marker" aria-hidden="true"></span> <?php echo $location; ?>
									</div>
							  <?php endif ;?>

							  <?php if ($params->get('show_author')) : ?>
									<div class="info-item articles-writtenby">
										<span class="fa fa-user" aria-hidden="true"></span> <?php echo $item->displayAuthorName; ?>
									</div>
								<?php endif; ?>

								<?php if ($item->displayDate) : ?>
									<div class="info-item articles-date">
										<span class="fa fa-calendar" aria-hidden="true"></span> <?php echo $item->displayDate; ?>
									</div>
								<?php endif; ?>

								<?php if ($item->displayHits) : ?>
									<div class="info-item articles-hits">
										<span class="fa fa-eye" aria-hidden="true"></span> <?php echo $item->displayHits; ?>
									</div>
								<?php endif; ?>
							</div>

							<?php if ($params->get('show_introtext')) : ?>
								<div class="articles-introtext">
									<?php echo $item->displayIntrotext; ?>
								</div>
							<?php endif; ?>

							<?php if ($params->get('show_tags', 0) && $item->tags->itemTags) : ?>
								<div class="mod-articles-category-tags">
									<?php echo JLayoutHelper::render('joomla.content.tags', $item->tags->itemTags); ?>
								</div>
							<?php endif; ?>

							<?php if ($params->get('show_readmore')) : ?>
								<p class="articles-readmore">
									<a class="<?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
										<?php if ($item->params->get('access-view') == false) : ?>
											<?php echo JText::_('MOD_ARTICLES_CATEGORY_REGISTER_TO_READ_MORE'); ?>
										<?php elseif ($readmore = $item->alternative_readmore) : ?>
											<?php echo $readmore; ?>
											<?php echo JHtml::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
										<?php elseif ($params->get('show_readmore_title', 0) == 0) : ?>
											<?php echo JText::sprintf('MOD_ARTICLES_CATEGORY_READ_MORE_TITLE'); ?>
										<?php else : ?>
											<?php echo JText::_('MOD_ARTICLES_CATEGORY_READ_MORE'); ?>
											<?php echo JHtml::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
										<?php endif; ?>
									</a>
								</p>
							<?php endif; ?>

						  <div class="rating-detail">
						  	<?php
						  		$scoreRate = Jtext::_('TPL_NO_REVIEW');
						  		if($item->rating == 1) {
						  			$scoreRate = Jtext::_('TPL_RATE_1');
						  		} elseif ($item->rating == 2) {
						  			$scoreRate = Jtext::_('TPL_RATE_2');
						  		} elseif ($item->rating == 3) {
						  			$scoreRate = Jtext::_('TPL_RATE_3');
						  		} elseif ($item->rating == 4) {
						  			$scoreRate = Jtext::_('TPL_RATE_4');
						  		} elseif ($item->rating == 5) {
						  			$scoreRate = Jtext::_('TPL_RATE_5');
						  		}
						  	?>
						  	<span class="score"><?php echo $item->rating * 2; ?></span>
						  	<div class="list">
						  		<h5><?php echo $scoreRate; ?></h5>
						  		<?php echo $item->rating_count.' '.Jtext::_('TPL_REVIEWS'); ?>	
						  	</div>
						  </div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if($modShow) :?>
		<div class="category-action text-center">
			<a class="btn btn-primary btn-lg" href="<?php  echo JRoute::_("index.php?Itemid={$modMenu}"); ?>" title="View More">
					<?php echo $modCat ;?>
			</a>
		</div>
	<?php endif ;?>
</div>

<script>
(function($){
  jQuery(document).ready(function($) {
    $("#owl-view-<?php echo $module->id; ?> .owl-carousel").owlCarousel({
      addClassActive: true,
      items: 5,
      loop: true,
      margin: 24,
      nav : true,
      navText : ["<span class='fa fa-angle-left'></span>", "<span class='fa fa-angle-right'></span>"],
      dots: true,
      autoPlay: false,
      responsive : {
		    // breakpoint from 0 up
		    0 : {
		      items: 1
		    },

		    // breakpoint from 480 up
		    480 : {
		      items: 2
		    },

		    // breakpoint from 768 up
		    768 : {
		      items: 3
		    },

		    // breakpoint from 992 up
		    992 : {
		      items: 4
		    },

		    // breakpoint from 1440 up
		    1440 : {
		      items: 4
		    }
			}
    });
  });
})(jQuery);
</script>
