<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_categories
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$input  = JFactory::getApplication()->input;
$option = $input->getCmd('option');
$view   = $input->getCmd('view');
$id     = $input->getInt('id');
$i = 0;

// Template helper
JLoader::register('JATemplateHelper', T3_TEMPLATE_PATH . '/helper.php');

foreach ($list as $item) : ?>
	<div class="col-sm-<?php echo ($i < 2) ? '6' : '4' ;?>">
		<?php 
			$bgCategory = '';
			if(json_decode($item->params)->image) {
				$bgCategory = json_decode($item->params)->image;
				$bgStyle = 'style="background-image: url('.$bgCategory.');"';
			}

			// add info for category
			$color = '';
			$icon = '';
			$customs 		= JATemplateHelper::getCustomFields($item->id, 'category');

				if(empty($customs['colors'])) :
					$color = "default";
				else: 
					$color = $customs['colors'];
				endif;
			// add info end
		?>
		<div class="category" <?php echo $bgStyle ;?>>
			<div class="category-detail">
				<h3>
					<a href="<?php echo JRoute::_(ContentHelperRoute::getCategoryRoute($item->id)); ?>">
					<?php echo $item->title; ?>
					</a>
				</h3>

				<?php if ($params->get('numitems')) : ?>
					<span class="num-items"><?php echo $item->numitems.' '.Jtext::_('TPL_LISTING'); ?></span>
				<?php endif; ?>

				<?php if ($params->get('show_description', 0)) : ?>
					<div class="category-description">
						<?php echo JHtml::_('content.prepare', $item->description, $item->getParams(), 'mod_articles_categories.content'); ?>
					</div>
				<?php endif; ?>
				<?php if(!empty($customs['icon'])) : ?>
					<div class="icon <?php echo $color ;?>">
						<span class="<?php echo $customs['icon'] ;?>"></span>
					</div>
				<?php endif; ?>
			
			</div>


			<?php if ($params->get('show_children', 0) && (($params->get('maxlevel', 0) == 0)
				|| ($params->get('maxlevel') >= ($item->level - $startLevel)))
				&& count($item->getChildren())) : ?>
				<?php echo Jtext::_('TPL_NOT_SUPPORT') ;?>
			<?php endif; ?>
		</div>
	</div>
<?php $i++; endforeach; ?>
