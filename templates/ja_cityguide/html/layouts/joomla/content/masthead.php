<?php
/*
 * ------------------------------------------------------------------------
 * JA City Guide Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
*/

defined('_JEXEC') or die;
$params = $displayData['params'];
$item = $displayData['item'];
$topInfo = $displayData['topInfo'];
$botInfo = $displayData['botInfo'];
$icons = $displayData['icons'];
$fullImage = json_decode($item->images)->image_fulltext;

// Get Custom Field
$extrafields = new JRegistry($item->attribs);
$location = $extrafields->get('location');

?>
<div class="ja-masthead-article" style="background-image:url(<?php echo $fullImage ?>);">
    <div class="container">
        <div class="jamasthead-detail">
            <!-- footer -->
            <?php if ($botInfo) : ?>
                <footer class="article-footer clearfix">
                    <?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $item, 'params' => $params, 'position' => 'below')); ?>
                </footer>
            <?php endif; ?>
            <!-- //footer -->

            <?php if ($params->get('show_title')) : ?>
                <?php echo JLayoutHelper::render('joomla.content.item_title', array('item' => $item, 'params' => $params, 'title-tag'=>'h1')); ?>
            <?php endif; ?>

            <!-- Aside -->
            <?php if ($topInfo || $icons) : ?>
                <aside class="article-aside clearfix">
                    <?php if ($topInfo): ?>
                        <?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $item, 'params' => $params, 'position' => 'above')); ?>
                    <?php endif; ?>

                    <?php if ($icons): ?>
                        <?php echo JLayoutHelper::render('joomla.content.icons', array('item' => $item, 'params' => $params, 'print' => isset($view->print) ? $view->print : null)); ?>
                    <?php endif; ?>
                </aside>
            <?php endif; ?>

            <?php if($location) :?>
            <div class="location">
                <span class="fa fa-map-marker" aria-hidden="true"></span><?php echo $location ;?>
            </div>
            <?php endif; ?>

            <?php if ($params->get('show_vote')): ?>
              <!-- Rating -->
              <?php
              if (isset($item->rating_sum) && $item->rating_count > 0) {
                  $item->rating = round($item->rating_sum / $item->rating_count, 1);
                  $item->rating_percentage = $item->rating_sum / $item->rating_count * 20;
              } else {
                  if (!isset($item->rating)) $item->rating = 0;
                  if (!isset($item->rating_count)) $item->rating_count = 0;
                  $item->rating_percentage = $item->rating * 20;
              }
              ?>
              <div class="rating-info pd-rating-info">
                  <form class="rating-form view-masthead">
                    <ul class="rating-list" >
                        <li class="rating-current" style="width:<?php echo $item->rating_percentage; ?>%;"></li>
                        <li><a href="javascript:void(0)" title="<?php echo JText::_('JA_1_STAR_OUT_OF_5'); ?>" class="one-star">1</a></li>
                        <li><a href="javascript:void(0)" title="<?php echo JText::_('JA_2_STARS_OUT_OF_5'); ?>" class="two-stars">2</a></li>
                        <li><a href="javascript:void(0)" title="<?php echo JText::_('JA_3_STARS_OUT_OF_5'); ?>" class="three-stars">3</a></li>
                        <li><a href="javascript:void(0)" title="<?php echo JText::_('JA_4_STARS_OUT_OF_5'); ?>" class="four-stars">4</a></li>
                        <li><a href="javascript:void(0)" title="<?php echo JText::_('JA_5_STARS_OUT_OF_5'); ?>" class="five-stars">5</a></li>
                    </ul>
                    <div class="rating-log"><span><?php echo $item->rating_count.' '.Jtext::_('TPL_VOTES'); ?></span></div>
                </form>
              </div>
               <!-- //Rating -->
            <?php endif;?>
        </div>
    </div>
</div>