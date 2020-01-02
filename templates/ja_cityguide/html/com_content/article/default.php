<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

// T3 ovrride
JHtml::addIncludePath(T3_PATH . '/html/com_content');
JHtml::addIncludePath(dirname(dirname(__FILE__)));

// Create shortcuts to some parameters.
$params  = $this->item->params;
$images  = json_decode($this->item->images);
$urls    = json_decode($this->item->urls);
$canEdit = $params->get('access-edit');
$user    = JFactory::getUser();
$info    = $params->get('info_block_position', 0);

// T3 ovrride.
$aInfo1 = ($params->get('show_publish_date') || $params->get('show_parent_category') || $params->get('show_author'));
$aInfo2 = ($params->get('show_create_date') || $params->get('show_modify_date') || $params->get('show_hits'));
$topInfo = ($aInfo1 && $info != 1) || ($aInfo2 && $info == 0);
$botInfo = ($aInfo1 && $info == 1) || ($aInfo2 && $info != 0);
$icons = !empty($this->print) || $canEdit || $params->get('show_print_icon') || $params->get('show_email_icon');


// Check if associations are implemented. If they are, define the parameter.
$assocParam = (JLanguageAssociations::isEnabled() && $params->get('show_associations'));
JHtml::_('behavior.caption');

// Template helper
JLoader::register('JATemplateHelper', T3_TEMPLATE_PATH . '/helper.php');

// add color for category
$color = '';
$customs 		= JATemplateHelper::getCustomFields($this->item->catid, 'category');

	if(empty($customs)) :
		$color = "default";
	else: 
		$color = $customs['colors'];
	endif;
// add color end

// Get Custom Field
$extrafields = new JRegistry($this->item->attribs);

$location = $extrafields->get('location');
$phone = $extrafields->get('phone');
$website = $extrafields->get('website');
$mapLat = $extrafields->get('map-lat','');
$mapLon = $extrafields->get('map-lon','');
$mapInfo = $extrafields->get('map-info','');
$mapOption = $extrafields->get('map-option');
$starHotel = $extrafields->get('star-hotel');

// Add Info to masthead
$doc = JFactory::getDocument();
$masthead = JLayoutHelper::render('joomla.content.masthead', array('item' => $this->item, 'params' => $params, 'topInfo'=>$topInfo, 'botInfo'=>$botInfo, 'icons' => $icons, 'view' => $this));
 $doc->setBuffer($masthead, array('type' => 'modules', 'name' => 'masthead', 'title' => ''));

// Rating
if (isset($this->item->rating_sum) && $this->item->rating_count > 0) {
  $this->item->rating = round($this->item->rating_sum / $this->item->rating_count, 1);
  $this->item->rating_percentage = $this->item->rating_sum / $this->item->rating_count * 20;
} else {
  if (!isset($this->item->rating)) $this->item->rating = 0;
  if (!isset($this->item->rating_count)) $this->item->rating_count = 0;
  $this->item->rating_percentage = $this->item->rating * 20;
}
$uri = JUri::getInstance();

?>

<!-- Page header -->
<?php if ($this->params->get('show_page_heading')) : ?>
<div class="page-header">
	<h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
</div>
<?php endif; ?>
<!-- // Page header -->
<div class="row">
	<!-- Location -->
	<?php if($location) :?>
	<div class="col-sm-4">
		<div class="review-wrap">
			<?php if($mapOption) :?>
			<div class="map">
				{jamap locations='{"location":{"0":"<?php echo $location ;?>"},"latitude":{"0":"<?php echo $mapLat ;?>"},"longitude":{"0":"<?php echo $mapLon ;?>"},"info":{"0":"<?php echo $mapInfo ;?>"}}' }{/jamap}
			</div>
			<?php endif; ?>

			<div class="review-detail">
				<?php if ($starHotel): ?>
				<div class="review-item hotel-star">
					<div class="label-hotel">
						<?php echo Jtext::_('TPL_HOTEL_STAR') ;?>
					</div>

					<div class="rating-info pd-rating-info">
            <form class="rating-form no-action">
              <ul class="rating-list" >
                  <li class="rating-current" style="width:<?php echo ($starHotel / 5) * 100; ?>%;"></li>
                  <li><a href="javascript:void(0)" title="<?php echo JText::_('JA_1_STAR_OUT_OF_5'); ?>" class="one-star">1</a></li>
                  <li><a href="javascript:void(0)" title="<?php echo JText::_('JA_2_STARS_OUT_OF_5'); ?>" class="two-stars">2</a></li>
                  <li><a href="javascript:void(0)" title="<?php echo JText::_('JA_3_STARS_OUT_OF_5'); ?>" class="three-stars">3</a></li>
                  <li><a href="javascript:void(0)" title="<?php echo JText::_('JA_4_STARS_OUT_OF_5'); ?>" class="four-stars">4</a></li>
                  <li><a href="javascript:void(0)" title="<?php echo JText::_('JA_5_STARS_OUT_OF_5'); ?>" class="five-stars">5</a></li>
              </ul>
          	</form>
          </div>
				</div>
				<?php endif;?>

				<?php if($location) :?>
				<div class="review-item location">
					<span class="fa fa-map-marker" aria-hidden="true"></span><?php echo $location ;?>
				</div>
				<?php endif; ?>

				<?php if($phone) :?>
				<div class="review-item phone">
					<span class="fa fa-phone" aria-hidden="true"></span><?php echo $phone ;?>
				</div>
				<?php endif; ?>

				<?php if($website) :?>
				<div class="review-item website">
					<span class="fa fa-link" aria-hidden="true"></span><a href="<?php echo $website ;?>" title="Website"><?php echo $website ;?></a>
				</div>
				<?php endif; ?>

				<!-- Show voting form -->
			  <?php	if ($params->get('show_vote')): ?>
			  	<div class="review-item rating">
				    <div class="rating-info pd-rating-info">
				      <span><?php echo JText::_('TPL_VOTE_FOR_US'); ?>: </span>
				      <form class="rating-form action-vote" method="POST" action="<?php echo htmlspecialchars($uri->toString()) ?>">
				          <ul class="rating-list">
				              <!-- <li class="rating-current" style="width:<?php echo $this->item->rating_percentage; ?>%;"></li> -->
				              <li><a href="javascript:void(0)" title="<?php echo JText::_('JA_1_STAR_OUT_OF_5'); ?>" class="one-star">1</a></li>
				              <li><a href="javascript:void(0)" title="<?php echo JText::_('JA_2_STARS_OUT_OF_5'); ?>" class="two-stars">2</a></li>
				              <li><a href="javascript:void(0)" title="<?php echo JText::_('JA_3_STARS_OUT_OF_5'); ?>" class="three-stars">3</a></li>
				              <li><a href="javascript:void(0)" title="<?php echo JText::_('JA_4_STARS_OUT_OF_5'); ?>" class="four-stars">4</a></li>
				              <li><a href="javascript:void(0)" title="<?php echo JText::_('JA_5_STARS_OUT_OF_5'); ?>" class="five-stars">5</a></li>
				          </ul>
				          <input type="hidden" name="task" value="article.vote" />
				          <input type="hidden" name="hitcount" value="0" />
				          <input type="hidden" name="user_rating" value="5" />
				          <input type="hidden" name="url" value="<?php echo htmlspecialchars($uri->toString()) ?>" />
				          <?php echo JHtml::_('form.token') ?>
				      </form>
				    </div>
				    <!-- //Rating -->

				    <script type="text/javascript">
				        !function($){
				            $('.rating-form').each(function(){
				                var form = this;
				                $(this).find('.rating-list li a').click(function(event){
				                    event.preventDefault();
				                    if (form.user_rating) {
				                    	form.user_rating.value = this.innerHTML;
				                    	form.submit();
				                    }
				                });
				            });
				        }(window.jQuery);
				    </script>
				  </div>
			  <?php endif;?>
			  <!-- End showing -->
			</div>
		</div>
	</div>
	<?php endif; ?>
	<!-- // Location -->

	<div class="<?php echo ($location) ? 'col-sm-8' : 'col-sm-12' ;?>">
		<div class="item-page<?php echo $this->pageclass_sfx; ?>" itemscope itemtype="https://schema.org/Article">
			<?php if (!empty($this->item->pagination) && $this->item->pagination && !$this->item->paginationposition && $this->item->paginationrelative) {
				echo $this->item->pagination;
			} ?>

			<!-- Article -->
			<article itemscope itemtype="http://schema.org/Article">
			  <meta itemscope itemprop="mainEntityOfPage"  itemType="https://schema.org/WebPage" itemid="https://google.com/article"/>
				<meta itemprop="inLanguage" content="<?php echo ($this->item->language === '*') ? JFactory::getConfig()->get('language') : $this->item->language; ?>" />

				<?php if(!empty($this->print)): 
				if ($icons): 
		      echo JLayoutHelper::render('joomla.content.icons', array('item' => $this->item, 'params' => $params, 'print' => isset($this->print) ? $this->print : null));
	     	endif; 	endif; ?>

				
				 <?php if ($params->get('show_category')) : ?>
          <?php echo JLayoutHelper::render('joomla.content.info_block.category', array('item' => $this->item, 'params' => $params)); ?>
        <?php endif; ?>

				<?php if ($params->get('show_title')) : ?>
					<?php echo JLayoutHelper::render('joomla.content.item_title', array('item' => $this->item, 'params' => $params, 'title-tag'=>'h1')); ?>
				<?php endif; ?>

				<!-- Show voting form -->
			  <?php	if (!$location && $params->get('show_vote')): ?>
			    <div class="rating-info pd-rating-info">
			      <span><?php echo JText::_('TPL_VOTES'); ?>: </span>
			      <form class="rating-form action-vote" method="POST" action="<?php echo htmlspecialchars($uri->toString()) ?>">
			          <ul class="rating-list">
			              <!-- <li class="rating-current" style="width:<?php echo $this->item->rating_percentage; ?>%;"></li> -->
			              <li><a href="javascript:void(0)" title="<?php echo JText::_('JA_1_STAR_OUT_OF_5'); ?>" class="one-star">1</a></li>
			              <li><a href="javascript:void(0)" title="<?php echo JText::_('JA_2_STARS_OUT_OF_5'); ?>" class="two-stars">2</a></li>
			              <li><a href="javascript:void(0)" title="<?php echo JText::_('JA_3_STARS_OUT_OF_5'); ?>" class="three-stars">3</a></li>
			              <li><a href="javascript:void(0)" title="<?php echo JText::_('JA_4_STARS_OUT_OF_5'); ?>" class="four-stars">4</a></li>
			              <li><a href="javascript:void(0)" title="<?php echo JText::_('JA_5_STARS_OUT_OF_5'); ?>" class="five-stars">5</a></li>
			          </ul>
			          <input type="hidden" name="task" value="article.vote" />
			          <input type="hidden" name="hitcount" value="0" />
			          <input type="hidden" name="user_rating" value="5" />
			          <input type="hidden" name="url" value="<?php echo htmlspecialchars($uri->toString()) ?>" />
			          <?php echo JHtml::_('form.token') ?>
			      </form>
			    </div>
			    <!-- //Rating -->

			    <script type="text/javascript">
			        !function($){
			            $('.rating-form').each(function(){
			                var form = this;
			                $(this).find('.rating-list li a').click(function(event){
			                    event.preventDefault();
			                    if (form.user_rating) {
			                    	form.user_rating.value = this.innerHTML;
			                    	form.submit();
			                    }
			                });
			            });
			        }(window.jQuery);
			    </script>
			  <?php endif;?>
			  <!-- End showing -->
				
				<?php // Content is generated by content plugin event "onContentAfterTitle" ?>
				<?php echo $this->item->event->afterDisplayTitle; ?>

				<?php $useDefList = ($params->get('show_modify_date') || $params->get('show_publish_date') || $params->get('show_create_date')
		  	|| $params->get('show_hits') || $params->get('show_category') || $params->get('show_parent_category') || $params->get('show_author') || $assocParam || $icons); ?>

				<?php if (isset ($this->item->toc)) :
					echo $this->item->toc;
				endif; ?>

				<?php // Content is generated by content plugin event "onContentBeforeDisplay" ?>
				<?php echo $this->item->event->beforeDisplayContent; ?>

				<?php if (isset($urls) && ((!empty($urls->urls_position) && ($urls->urls_position == '0')) || ($params->get('urls_position') == '0' && empty($urls->urls_position)))
					|| (empty($urls->urls_position) && (!$params->get('urls_position')))) : ?>
					<?php echo $this->loadTemplate('links'); ?>
				<?php endif; ?>

				<?php if ($params->get('access-view')) : ?>
					<?php if (!empty($this->item->pagination) && $this->item->pagination && !$this->item->paginationposition && !$this->item->paginationrelative) :
						echo $this->item->pagination;
					endif; ?>

					<section class="article-content clearfix" itemprop="articleBody">
						<?php echo $this->item->text; ?>

						<!-- Item tags -->
						<?php if ($info == 0 && $params->get('show_tags', 1) && !empty($this->item->tags)) : ?>
							<?php echo JLayoutHelper::render('joomla.content.tags', $this->item->tags->itemTags); ?>
						<?php endif; ?>
						<!-- // Item tags -->
					</section>


					<?php if (!empty($this->item->pagination) && $this->item->pagination && $this->item->paginationposition && !$this->item->paginationrelative) :
						echo $this->item->pagination; ?>
					<?php endif; ?>

					<?php if (isset($urls) && ((!empty($urls->urls_position) && ($urls->urls_position == '1')) || ($params->get('urls_position') == '1'))) : ?>
						<?php echo $this->loadTemplate('links'); ?>
					<?php endif; ?>

					<?php // Optional teaser intro text for guests ?>
					<?php elseif ($params->get('show_noauth') == true && $user->get('guest')) : ?>
						<?php echo JLayoutHelper::render('joomla.content.intro_image', $this->item); ?>
						<?php echo JHtml::_('content.prepare', $this->item->introtext); ?>
						
						<?php // Optional link to let them register to see the whole article. ?>
						<?php if ($params->get('show_readmore') && $this->item->fulltext != null) : ?>
							<?php $menu = JFactory::getApplication()->getMenu(); ?>
							<?php $active = $menu->getActive(); ?>
							<?php $itemId = $active->id; ?>
							<?php $link = new JUri(JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false)); ?>
							<?php $link->setVar('return', base64_encode(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language))); ?>

							<section class="readmore">
								<a href="<?php echo $link; ?>" class="register"><span>
								<?php $attribs = json_decode($this->item->attribs); ?>
								<?php
								if ($attribs->alternative_readmore == null) :
									echo JText::_('COM_CONTENT_REGISTER_TO_READ_MORE');
								elseif ($readmore = $attribs->alternative_readmore) :
									echo $readmore;
									if ($params->get('show_readmore_title', 0) != 0) :
										echo JHtml::_('string.truncate', $this->item->title, $params->get('readmore_limit'));
									endif;
								elseif ($params->get('show_readmore_title', 0) == 0) :
									echo JText::sprintf('COM_CONTENT_READ_MORE_TITLE');
								else :
									echo JText::_('COM_CONTENT_READ_MORE');
									echo JHtml::_('string.truncate', $this->item->title, $params->get('readmore_limit'));
								endif; ?>
								</span></a>
							</section>
						<?php endif; ?>
					<?php endif; ?>

			</article>
			<!-- //Article -->

			<?php if (!empty($this->item->pagination) && $this->item->pagination && $this->item->paginationposition && $this->item->paginationrelative) :
				echo $this->item->pagination; ?>
			<?php endif; ?>

			<?php // Content is generated by content plugin event "onContentAfterDisplay" ?>
			<?php echo $this->item->event->afterDisplayContent; ?>
		</div>
	</div>
</div>