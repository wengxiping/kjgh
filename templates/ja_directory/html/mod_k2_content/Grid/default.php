<?php
/**
 * @version		2.6.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;


function getCategoriesTreeGrid($arrid = null)
{
    $mainframe = JFactory::getApplication();
    $clientID = $mainframe->getClientId();
    $db = JFactory::getDBO();
    $user = JFactory::getUser();
    $aid = (int)$user->get('aid');

    $extraSql = '';
    if ($arrid) {
        $extraSql = ' AND id IN (' . implode(',', $arrid) . ')';
    }

    $query = "SELECT *  FROM #__k2_categories";
    if ($mainframe->isSite()) {
        $query .= " WHERE published=1  AND trash=0";
        if (K2_JVERSION != '15') {
            $query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).")";
            if ($mainframe->getLanguageFilter()) {
                $query .= " AND language IN(".$db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*').")";
            }
        } else {
            $query .= " AND access<={$aid}";
        }
    }

    $query .= " $extraSql ORDER BY parent ";
    $db->setQuery($query);
    $categories = $db->loadObjectList();
    if ($arrid) {
        return $categories;
    }

    $tree = array();
    return buildTreeGrid($categories);
}

function buildTreeGrid(array &$categories, $parent = 0)
{
    $branch = array();
    foreach ($categories as &$category) {
        if ($category->parent == $parent) {
            $children = buildTreeGrid($categories, $category->id);
            if ($children) {
                $category->children = $children;
            }
            $branch[$category->id] = $category;
        }
    }
    return $branch;
}

if ($params->get('catfilter')) {
    $categoriesTree = getCategoriesTreeGrid($params->get('category_id'));
} else {
    $categoriesTree = getCategoriesTreeGrid();
    foreach ($categoriesTree as $tree) {
        $categoriesTree = $tree->children;
        break;
    }
}

$itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
$catFilter = array();
foreach ($categoriesTree as $category) {
    $paramsCate = json_decode($category->params, true);
    $categoryIcon = $paramsCate['category_icon'] ? $paramsCate['category_icon'] : 'images/joomlart/directory-icons/default-marker.png';
    $catFilter[] = array(
        'id' => $category->id,
        'name' => $category->name,
        'icon' => $categoryIcon,
        'children' => implode(',', $itemListModel->getCategoryTree($category->id)),
    );
}
?>

<div id="k2ModuleBox<?php echo $module->id; ?>" class="k2ItemsBlockGrid k2ItemsBlock<?php if($params->get('moduleclass_sfx')) echo ' '.$params->get('moduleclass_sfx'); ?>">
  <div class="container">
  <?php if($catFilter): ?>
    <div class="container grid-filter-category" data-numshow="<?php echo $params->get('number_item', 9) ?>">
        <ul class="nav nav-pills">
            <?php foreach ($catFilter as $filter) { ?>
                <li class="">
                    <a href="#" data-id="<?php echo $filter['children'] ?>" title="<?php echo $filter['name'] ?>">
                        <img src="<?php echo $filter['icon'] ?>" alt="" /> 
                        <span><?php echo $filter['name'] ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
  <?php endif; ?>
	<?php if($params->get('itemPreText')): ?>
	<p class="modulePretext"><?php echo $params->get('itemPreText'); ?></p>
	<?php endif; ?>

	<?php if(count($items)): ?>
  <div class="row">
    <?php foreach ($items as $key=>$item): ?>

    <?php $colClass = "col-md-3"; ?>

    <div class="col col-xs-12 col-sm-4 grid-filter-item <?php echo $colClass; ?> <?php if ($item->featured) { echo 'directoryItemIsFeatured'; } ?> <?php if(count($items)==$key+1) echo ' lastItem'; ?>" data-cate="<?php echo $item->categoryid; ?>">

      <!-- Plugins: BeforeDisplay -->
      <?php echo $item->event->BeforeDisplay; ?>

      <!-- K2 Plugins: K2BeforeDisplay -->
      <?php echo $item->event->K2BeforeDisplay; ?>

      <?php if($params->get('itemAuthorAvatar')): ?>
      <a class="k2Avatar moduleItemAuthorAvatar" rel="author" href="<?php echo $item->authorLink; ?>">
				<img src="<?php echo $item->authorAvatar; ?>" alt="<?php echo K2HelperUtilities::cleanHtml($item->author); ?>" style="width:<?php echo $avatarWidth; ?>px;height:auto;" />
			</a>
      <?php endif; ?>

      <?php if($params->get('itemTitle')): ?>
      <a class="moduleItemTitle" href="<?php echo $item->link; ?>"><?php echo $item->title; ?></a>
      <?php endif; ?>

      <?php if($params->get('itemAuthor')): ?>
      <div class="moduleItemAuthor">
	      <?php echo K2HelperUtilities::writtenBy($item->authorGender); ?>
	
				<?php if(isset($item->authorLink)): ?>
				<a rel="author" title="<?php echo K2HelperUtilities::cleanHtml($item->author); ?>" href="<?php echo $item->authorLink; ?>"><?php echo $item->author; ?></a>
				<?php else: ?>
				<?php echo $item->author; ?>
				<?php endif; ?>
				
				<?php if($params->get('userDescription')): ?>
				<?php echo $item->authorDescription; ?>
				<?php endif; ?>
				
			</div>
			<?php endif; ?>

      <!-- Plugins: AfterDisplayTitle -->
      <?php echo $item->event->AfterDisplayTitle; ?>

      <!-- K2 Plugins: K2AfterDisplayTitle -->
      <?php echo $item->event->K2AfterDisplayTitle; ?>

      <!-- Plugins: BeforeDisplayContent -->
      <?php echo $item->event->BeforeDisplayContent; ?>

      <!-- K2 Plugins: K2BeforeDisplayContent -->
      <?php echo $item->event->K2BeforeDisplayContent; ?>

      <?php if($params->get('itemImage') || $params->get('itemIntroText')): ?>
      <div class="moduleItemIntrotext">
	      <?php if($params->get('itemImage') && isset($item->image)): ?>
	      <a class="moduleItemImage" href="<?php echo $item->link; ?>" title="<?php echo JText::_('K2_CONTINUE_READING'); ?> &quot;<?php echo K2HelperUtilities::cleanHtml($item->title); ?>&quot;">
	      	<img src="<?php echo $item->image; ?>" alt="<?php echo K2HelperUtilities::cleanHtml($item->title); ?>"/>
	      </a>
	      <?php endif; ?>

      	<?php if($params->get('itemIntroText')): ?>
      	<?php echo $item->introtext; ?>
      	<?php endif; ?>
      </div>
      <?php endif; ?>

      <?php if($params->get('itemExtraFields') && count($item->extra_fields)): ?>
      <div class="moduleItemExtraFields">
	      <ul>
	        <?php foreach ($item->extra_fields as $extraField): ?>
					<?php if($extraField->name == 'Location'): ?>
					<li class="type<?php echo ucfirst($extraField->type); ?> group<?php echo $extraField->group; ?>">
						<span class="moduleItemExtraFieldsValue"><?php echo $extraField->value; ?></span>
					</li>
					<?php endif; ?>
	        <?php endforeach; ?>
	      </ul>
      </div>
      <?php endif; ?>

      <div class="clr"></div>

      <?php if($params->get('itemVideo')): ?>
      <div class="moduleItemVideo">
      	<?php echo $item->video ; ?>
      	<span class="moduleItemVideoCaption"><?php echo $item->video_caption ; ?></span>
      	<span class="moduleItemVideoCredits"><?php echo $item->video_credits ; ?></span>
      </div>
      <?php endif; ?>

      <div class="clr"></div>

      <!-- Plugins: AfterDisplayContent -->
      <?php echo $item->event->AfterDisplayContent; ?>

      <!-- K2 Plugins: K2AfterDisplayContent -->
      <?php echo $item->event->K2AfterDisplayContent; ?>

      <?php if($params->get('itemDateCreated')): ?>
      <span class="moduleItemDateCreated"><?php echo JText::_('K2_WRITTEN_ON') ; ?> <?php echo JHTML::_('date', $item->created, JText::_('K2_DATE_FORMAT_LC2')); ?></span>
      <?php endif; ?>

      <?php if($params->get('itemCategory')): ?>
      <?php echo JText::_('K2_IN') ; ?> <a class="moduleItemCategory" href="<?php echo $item->categoryLink; ?>"><?php echo $item->categoryname; ?></a>
      <?php endif; ?>

      <?php if($params->get('itemTags') && count($item->tags)>0): ?>
      <div class="moduleItemTags">
      	<b><?php echo JText::_('K2_TAGS'); ?>:</b>
        <?php foreach ($item->tags as $tag): ?>
        <a href="<?php echo $tag->link; ?>"><?php echo $tag->name; ?></a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if($params->get('itemAttachments') && count($item->attachments)): ?>
			<div class="moduleAttachments">
				<?php foreach ($item->attachments as $attachment): ?>
				<a title="<?php echo K2HelperUtilities::cleanHtml($attachment->titleAttribute); ?>" href="<?php echo $attachment->link; ?>"><?php echo $attachment->title; ?></a>
				<?php endforeach; ?>
			</div>
      <?php endif; ?>

			<?php if($params->get('itemCommentsCounter') && $componentParams->get('comments')): ?>		
				<?php if(!empty($item->event->K2CommentsCounter)): ?>
					<!-- K2 Plugins: K2CommentsCounter -->
					<?php echo $item->event->K2CommentsCounter; ?>
				<?php else: ?>
					<?php if($item->numOfComments>0): ?>
					<a class="moduleItemComments" href="<?php echo $item->link.'#itemCommentsAnchor'; ?>">
						<?php echo $item->numOfComments; ?> <?php if($item->numOfComments>1) echo JText::_('K2_COMMENTS'); else echo JText::_('K2_COMMENT'); ?>
					</a>
					<?php else: ?>
					<a class="moduleItemComments" href="<?php echo $item->link.'#itemCommentsAnchor'; ?>">
						<?php echo JText::_('K2_BE_THE_FIRST_TO_COMMENT'); ?>
					</a>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>

			<?php if($params->get('itemHits')): ?>
			<span class="moduleItemHits">
				<?php echo JText::_('K2_READ'); ?> <?php echo $item->hits; ?> <?php echo JText::_('K2_TIMES'); ?>
			</span>
			<?php endif; ?>

			<?php if($params->get('itemReadMore') && $item->fulltext): ?>
			<a class="moduleItemReadMore" href="<?php echo $item->link; ?>">
				<?php echo JText::_('K2_READ_MORE'); ?>
			</a>
			<?php endif; ?>

      <!-- Plugins: AfterDisplay -->
      <?php echo $item->event->AfterDisplay; ?>

      <!-- K2 Plugins: K2AfterDisplay -->
      <?php echo $item->event->K2AfterDisplay; ?>

      <div class="clr"></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

	<?php if($params->get('itemCustomLink')): ?>
	<a class="moduleCustomLink" href="<?php echo $params->get('itemCustomLinkURL'); ?>" title="<?php echo K2HelperUtilities::cleanHtml($itemCustomLinkTitle); ?>"><?php echo $itemCustomLinkTitle; ?></a>
	<?php endif; ?>

	<?php if($params->get('feed')): ?>
	<div class="k2FeedIcon">
		<a href="<?php echo JRoute::_('index.php?option=com_k2&view=itemlist&format=feed&moduleID='.$module->id); ?>" title="<?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?>">
			<span><?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?></span>
		</a>
		<div class="clr"></div>
	</div>
	<?php endif; ?>
  </div>
</div>

<script type="text/javascript">
   (function($) {
       $(document).ready(function() {
           $('.k2ItemsBlockGrid').each(function(i) {
               var panel = $(this);
               var filterList = $('.grid-filter-category', panel.parent());

               // filter category
               $('a', filterList).click(function(e) {
                   var filter = $(this);
                   if (filter.parent().hasClass('active')) {
                       return false;
                   }
                   $('li', filterList).removeClass('active');
                   filter.parent().addClass('active');

                   var categories = filter.attr('data-id').split(',');
                   var showNumItem = filterList.attr('data-numshow');
                   var count = 0;
                   $('.grid-filter-item', panel).each(function(i) {
                       if (count < showNumItem && categories && $.inArray($(this).attr('data-cate'), categories) != -1) {
                           $(this).fadeIn();
                           count++;
                       } else {
                           $(this).hide();
                       }
                   });
                   setEqualHeight(panel);
                   e.preventDefault();
               });
               $('a:first', filterList).click();

               // equal height items
               function setEqualHeight(panel) {
                   var maxHeight = 0;
                   var cols = $('.grid-filter-item', panel);
                   cols.css('min-height', 0);
                   cols.each(function(i) {
                       if ($(this).css('display') != 'none') {
                           console.log($(this).innerHeight())
                           maxHeight = Math.max(maxHeight, $(this).innerHeight());
                       }
                   });
                   cols.each(function(i) {
                       if ($(this).hasClass('2col')) {
                           $(this).css('min-height', maxHeight * 2);
                       } else {
                           $(this).css('min-height', maxHeight);
                       }
                   });
               }
               setEqualHeight(panel);

               $(window).resize(function() {
                   setEqualHeight(panel);
               });
           });
       });
   })(jQuery);
</script>