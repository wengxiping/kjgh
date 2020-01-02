<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$doc    = JFactory::getDocument();
$doc->addStyleSheet("components/com_jblance/css/customer/event_item.css");
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
JHtml::addIncludePath(T3_PATH.'/html/com_content');
JHtml::addIncludePath(dirname(dirname(__FILE__)));
JHtml::_('behavior.caption');

$dispatcher = JFactory::getApplication();
//echo $this->category->id;
$this->category->text = $this->category->description;
$dispatcher->triggerEvent('onContentPrepare', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$this->category->description = $this->category->text;

$results = $dispatcher->triggerEvent('onContentAfterTitle', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$afterDisplayTitle = trim(implode("\n", $results));

$results = $dispatcher->triggerEvent('onContentBeforeDisplay', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$beforeDisplayContent = trim(implode("\n", $results));

$results = $dispatcher->triggerEvent('onContentAfterDisplay', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$afterDisplayContent = trim(implode("\n", $results));

?>
<style>
    #t3-mainbody{
        padding: 0 0 20px 0!important;margin:6px 0 0 0!important;
        background:#FFFFFF;
        width: 100% !important;
        height: 100% !important;
    }
    #t3-mainbody .row{
        margin:0!important;
        padding: 0!important;
        display: flex;justify-content: center;align-items: center;
    }
    #t3-mainbody .row .col-sm-2{
        padding: 0!important;
    }
    #t3-mainbody  #t3-content{
        padding: 0!important;
        width: 1200px!important;

    }
    #t3-mainbody  #t3-content .new-head{
           width: 100%;
           height: 56px;
           display: flex;
           justify-content: flex-start;
           align-items: center;
            opacity: 1;
            font-size: 18px;
            font-family: MicrosoftYaHei;
            color: rgba(255,96,16,1);
            line-height: 24px;
            letter-spacing: 0px;
    }
</style>
<div class="new-head">新闻资讯</div>
<div class="blog events-blog <?php echo $this->pageclass_sfx;?>" itemscope itemtype="https://schema.org/Blog">
	<?php if ($this->params->get('show_category_title', 1) or $this->params->get('page_subheading')) : ?>
  	<div class="page-subheader clearfix">
  		<h2 class="page-subtitle">
				<?php if ($this->params->get('show_category_title')) : ?>
					<small class="subheading-category"><?php echo $this->category->title;?></small><br/>
				<?php endif; ?>
  			<?php echo $this->escape($this->params->get('page_subheading')); ?>
  		</h2>
	</div>
	<?php endif; ?>
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="page-header clearfix">
		<h1 class="page-title title-lead"> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
	</div>
	<?php endif; ?>

	<?php echo $afterDisplayTitle; ?>

	<?php if ($this->params->get('show_cat_tags', 1) && !empty($this->category->tags->itemTags)) : ?>
		<?php echo JLayoutHelper::render('joomla.content.tags', $this->category->tags->itemTags); ?>
	<?php endif; ?>

	<?php if ($beforeDisplayContent || $afterDisplayContent || $this->params->get('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
	<div class="category-desc clearfix">
		<?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
			<img src="<?php echo $this->category->getParams()->get('image'); ?>" alt="<?php echo htmlspecialchars($this->category->getParams()->get('image_alt'), ENT_COMPAT, 'UTF-8'); ?>" />
		<?php endif; ?>
		<?php echo $beforeDisplayContent; ?>
		<?php if ($this->params->get('show_description') && $this->category->description) : ?>
			<?php echo JHtml::_('content.prepare', $this->category->description, '', 'com_content.category'); ?>
		<?php endif; ?>
		<?php echo $afterDisplayContent; ?>
	</div>
	<?php endif; ?>

	<?php if (empty($this->lead_items) && empty($this->link_items) && empty($this->intro_items)) : ?>
		<?php if ($this->params->get('show_no_articles', 1)) : ?>
			<p><?php echo JText::_('COM_CONTENT_NO_ARTICLES'); ?></p>
		<?php endif; ?>
	<?php endif; ?>

	<?php $leadingcount = 0; ?>
	<?php if (!empty($this->lead_items)) : ?>
	<div class="items-leading clearfix">
		<?php foreach ($this->lead_items as &$item) : ?>
		<div class="leading leading-<?php echo $leadingcount; ?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?>" itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting">
			<?php
				$this->item = &$item;
				echo $this->loadTemplate('item');
			?>
		</div>
		<?php $leadingcount++; ?>
		<?php endforeach; ?>
	</div><!-- end items-leading -->
	<?php endif; ?>

	<?php
		$introcount = (count($this->intro_items));
		$counter = 0;
	?>

	<?php if (!empty($this->intro_items)) : ?>
	<?php foreach ($this->intro_items as $key => &$item) : ?>
		<?php $rowcount = ((int) $key % (int) $this->columns) + 1; ?>
		<?php if ($rowcount === 1) : ?>
			<?php $row = $counter / $this->columns; ?>
		<?php endif; ?>
			<div class='row news-row'>
				<div class="col-sm-2">
                    <?php
                    $ret = JLayoutHelper::render('joomla.content.intro_image', $item);
                    if($ret){
                      echo $ret;
                    }else{
                    ?>
                    <div class="pull-left item-image"><img src="components/com_jblance/images/default_image.png"></div>
                        <?php
                    }
                    ?>
				</div>
				<div class="col-sm-10">
					<div class="item column-<?php echo $rowcount;?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?>" itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting">
						<?php
						$this->item = &$item;
						echo $this->loadTemplate('item');
					?>
					</div><!-- end item -->
					<?php $counter++; ?>
				</div><!-- end span -->
			</div>
			<?php if (($rowcount == $this->columns) or ($counter == $introcount)) : ?>
			<?php endif; ?>
	<?php endforeach; ?>
	<?php endif; ?>

	<?php if (!empty($this->link_items)) : ?>
	<div class="items-more">
	<?php echo $this->loadTemplate('links'); ?>
	</div>
	<?php endif; ?>

	<?php if ($this->maxLevel != 0 && !empty($this->children[$this->category->id])) : ?>
	<div class="cat-children">
		<?php if ($this->params->get('show_category_heading_title_text', 1) == 1) : ?>
		<h3> <?php echo JText::_('JGLOBAL_SUBCATEGORIES'); ?> </h3>
		<?php endif; ?>
		<?php echo $this->loadTemplate('children'); ?> </div>
	<?php endif; ?>

	<?php
  $pagesTotal = isset($this->pagination->pagesTotal) ? $this->pagination->pagesTotal : $this->pagination->get('pages.total');
  if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($pagesTotal > 1)) : ?>
<!--	<div class="pagination-wrap clearfix">-->
<!--		--><?php // if ($this->params->def('show_pagination_results', 1)) : ?>
<!--		<div class="counter"> --><?php //echo $this->pagination->getPagesCounter(); ?><!--</div>-->
<!--		--><?php //endif; ?>
<!--		--><?php //echo $this->pagination->getPagesLinks(); ?><!-- </div>-->
      <div class="clear"></div>
      <div class="xp-pagination" style="margin-top: 56px!important;">
          <div class="serviceListTotal"><?php if($this->params->def('show_pagination_results', 1)){echo $this->pagination->getPagesCounter();}?></div>
          <div class="pull-right">
              <?php echo $this->pagination->getPagesLinks(); ?>
          </div>
      </div>
	<?php  endif; ?>
</div>
