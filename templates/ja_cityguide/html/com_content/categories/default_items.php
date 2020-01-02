<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
$lang	= JFactory::getLanguage();
$i = 0;

if (count($this->items[$this->parent->id]) > 0 && $this->maxLevelcat != 0) :
?>
	<?php foreach($this->items[$this->parent->id] as $id => $item) : ?>
		<?php
		// add info for category
			$icon = '';
			$customs 		= JATemplateHelper::getCustomFields($item->id, 'category');

				if(empty($customs['colors'])) :
					$color = "default";
				else: 
					$color = $customs['colors'];
				endif;
		// add info end

		$bgCategory = '';
		if($item->getParams()->get('image')) {
			$bgCategory = $item->getParams()->get('image');
			$bgStyle = 'style="background-image: url('.$bgCategory.');"';
		}	

		if ($this->params->get('show_empty_categories_cat') || $item->numitems || count($item->getChildren())) :
		?>
		<div class="category-item col-sm-<?php echo ($i < 2) ? '6' : '4' ;?>">
			<div class="category" <?php echo $bgStyle ;?>>
				<div class="category-detail">
					<h3 class="item-title">
						<a href="<?php echo JRoute::_(ContentHelperRoute::getCategoryRoute($item->id));?>">
						<?php echo $this->escape($item->title); ?></a>
						<?php if (count($item->getChildren()) > 0) : ?>
							<a href="#category-<?php echo $item->id;?>" data-toggle="collapse" data-toggle="button" class="btn btn-mini pull-right">
								<span class="fa fa-plus"></span>
							</a>
						<?php endif;?>
					</h3>

					<?php if ($this->params->get('show_cat_num_articles_cat') == 1) :?>
						<span class="num-items hasTooltip" title="<?php echo T3J::tooltipText('COM_CONTENT_NUM_ITEMS'); ?>">
							<?php echo $item->numitems.' '.Jtext::_('TPL_LISTING'); ?>
						</span>
					<?php endif; ?>
				
					<?php if ($this->params->get('show_subcat_desc_cat') == 1) :?>
						<?php if ($item->description) : ?>
							<div class="category-desc">
								<?php echo JHtml::_('content.prepare', $item->description, '', 'com_content.categories'); ?>
							</div>
						<?php endif; ?>
					<?php endif; ?>

					<?php if(!empty($customs['icon'])) : ?>
						<div class="icon <?php echo $color ;?>">
							<span class="<?php echo $customs['icon'] ;?>"></span>
						</div>
					<?php endif; ?>

					<?php if (count($item->getChildren()) > 0) :?>
						<div class="collapse fade" id="category-<?php echo $item->id;?>">
						<?php
						$this->items[$item->id] = $item->getChildren();
						$this->parent = $item;
						$this->maxLevelcat--;
						echo $this->loadTemplate('items');
						$this->parent = $item->getParent();
						$this->maxLevelcat++;
						?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>
	<?php $i++;endforeach; ?>
<?php endif; ?>
