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

?>
<!-- Start K2 Category Layout -->
<div id="k2Container" class="itemDirectoryListView itemListView<?php if($this->params->get('pageclass_sfx')) echo ' '.$this->params->get('pageclass_sfx'); ?>">

  <div class="toolbar">
  	<?php if($this->params->get('show_page_title')): ?>
  	<!-- Page title -->
  	<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>">
  		<?php echo $this->escape($this->params->get('page_title')); ?>
  	</div>
  	<?php endif; ?>

  	<?php if($this->params->get('catFeedIcon')): ?>
  	<!-- RSS feed icon -->
  	<div class="k2FeedIcon">
  		<a href="<?php echo $this->feed; ?>" title="<?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?>">
  			<span><?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?></span>
  		</a>
  		<div class="clr"></div>
  	</div>
  	<?php endif; ?>

    <?php if(isset($this->addLink)): ?>
    <!-- Item add link -->
    <span class="catItemAddLink">
      <a class="modal" rel="{handler:'iframe',size:{x:990,y:650}}" href="<?php echo $this->addLink; ?>">
        <?php echo JText::_('K2_ADD_A_NEW_ITEM_IN_THIS_CATEGORY'); ?>
      </a>
    </span>
    <?php endif; ?>
  </div>

  <?php $locations = (object) array(
      'location' => new stdClass(),
      'latitude' =>  new stdClass(),
      'longitude' =>  new stdClass(),
      'info' =>  new stdClass()
    );
    $i = 0;
    $baseURL = JUri::base();
    $sections = array('leading', 'primary', 'secondary', 'links');
    foreach($sections as $section) {
      if(isset($this->{$section}) && count($this->{$section})) {
        foreach($this->{$section} as $key=>$item) {
          $latitude = $longitude = '';
          if(count($item->extra_fields)) {
            foreach ($item->extra_fields as $extraField) {
              if($extraField->alias == 'latitude') $latitude = $extraField->value;
              if($extraField->alias == 'longitude') $longitude = $extraField->value;
            }  
          }
          
          if($latitude && $longitude) {
            $catParams= new JRegistry($item->categoryparams);
            $icon = $catParams->get('category_marker', 'images/joomlart/directory-icons/default-marker.png');
            $icon = $baseURL.'/'.$icon;

            $locations->location->{$i} = '';
            $locations->latitude->{$i} = $latitude;
            $locations->longitude->{$i} = $longitude;
            $locations->info->{$i} = '<a class="marker-title" href="'.$item->link.'">'.$item->title.'</a>';
            foreach ($item->extra_fields as $key=>$extraField): 
            if($extraField->value != ''):
             if(($extraField->type != 'header') && ($extraField->alias != 'latitude') && ($extraField->alias != 'longitude')):
              $locations->info->{$i} .= '<div class=""><span class="">'.$extraField->name.':</span>';
              $locations->info->{$i} .= '<span class="">  '.$extraField->value.'</span></div>';
             endif;
            endif; 
            endforeach; 
            $locations->info->{$i} .= '<img class="marker-img" src="'.$item->imageSmall.'" alt="" />';
            @$locations->icon->{$i} = $icon;
            $i++;
          }
        }
      }
    }
    if($i) {
      $jamap = "{jamap locations='".str_replace('\'','\\\'',json_encode($locations))."' map_width='100%' map_height='455' zoom='4' disable_scrollwheelzoom='1' display_scale='1' display_overview='1'}{/jamap}";
      echo $jamap;
    }
  ?>

	<?php if((isset($this->leading) || isset($this->primary) || isset($this->secondary) || isset($this->links)) && (count($this->leading) || count($this->primary) || count($this->secondary) || count($this->links))): ?>
	<!-- Item list -->
	<div class="itemList">

		<?php if(isset($this->leading) && count($this->leading)): ?>
		<!-- Leading items -->
		<div id="itemListLeading" class="equal-height">
			<?php foreach($this->leading as $key=>$item): ?>

			<?php
			// Define a CSS class for the last container on each row
			if( (($key+1)%($this->params->get('num_leading_columns'))==0) || count($this->leading)<$this->params->get('num_leading_columns') )
				$lastContainer= ' itemContainerLast';
			else
				$lastContainer='';
			?>
			
			<div class="itemContainer<?php echo $lastContainer; ?> col"<?php echo (count($this->leading)==0) ? '' : ' style="width:'.number_format(100/$this->params->get('num_leading_columns'), 1).'%;"'; ?>>
				<?php
					// Load category_item.php by default
					$this->item=$item;
					echo $this->loadTemplate('item');
				?>
			</div>
			<?php if(($key+1)%($this->params->get('num_leading_columns'))==0): ?>
			<div class="clr"></div>
			<?php endif; ?>
			<?php endforeach; ?>
			<div class="clr"></div>
		</div>
		<?php endif; ?>

		<?php if(isset($this->primary) && count($this->primary)): ?>
		<!-- Primary items -->
		<div id="itemListPrimary" class="equal-height">
			<?php foreach($this->primary as $key=>$item): ?>
			
			<?php 
			// Define a CSS class for the last container on each row
			if( (($key+1)%($this->params->get('num_primary_columns'))==0) || count($this->primary)<$this->params->get('num_primary_columns') )
				$lastContainer= ' itemContainerLast';
			else
				$lastContainer='';
			?>
			
			<div class="itemContainer<?php echo $lastContainer; ?> col"<?php echo (count($this->primary)==0) ? '' : ' style="width:'.number_format(100/$this->params->get('num_primary_columns'), 1).'%;"'; ?>>
				<?php
					// Load category_item.php by default
					$this->item=$item;
					echo $this->loadTemplate('item');
				?>
			</div>
			<?php if(($key+1)%($this->params->get('num_primary_columns'))==0): ?>
			<div class="clr"></div>
			<?php endif; ?>
			<?php endforeach; ?>
			<div class="clr"></div>
		</div>
		<?php endif; ?>

		<?php if(isset($this->secondary) && count($this->secondary)): ?>
		<!-- Secondary items -->
		<div id="itemListSecondary" class="equal-height">
			<?php foreach($this->secondary as $key=>$item): ?>
			
			<?php
			// Define a CSS class for the last container on each row
			if( (($key+1)%($this->params->get('num_secondary_columns'))==0) || count($this->secondary)<$this->params->get('num_secondary_columns') )
				$lastContainer= ' itemContainerLast';
			else
				$lastContainer='';
			?>
			
			<div class="itemContainer<?php echo $lastContainer; ?> col"<?php echo (count($this->secondary)==0) ? '' : ' style="width:'.number_format(100/$this->params->get('num_secondary_columns'), 1).'%;"'; ?>>
				<?php
					// Load category_item.php by default
					$this->item=$item;
					echo $this->loadTemplate('item');
				?>
			</div>
			<?php if(($key+1)%($this->params->get('num_secondary_columns'))==0): ?>
			<div class="clr"></div>
			<?php endif; ?>
			<?php endforeach; ?>
			<div class="clr"></div>
		</div>
		<?php endif; ?>

		<?php if(isset($this->links) && count($this->links)): ?>
		<!-- Link items -->
		<div id="itemListLinks">
			<h4><?php echo JText::_('K2_MORE'); ?></h4>
			<?php foreach($this->links as $key=>$item): ?>

			<?php
			// Define a CSS class for the last container on each row
			if( (($key+1)%($this->params->get('num_links_columns'))==0) || count($this->links)<$this->params->get('num_links_columns') )
				$lastContainer= ' itemContainerLast';
			else
				$lastContainer='';
			?>

			<div class="itemContainer<?php echo $lastContainer; ?>"<?php echo (count($this->links)==1) ? '' : ' style="width:'.number_format(100/$this->params->get('num_links_columns'), 1).'%;"'; ?>>
				<?php
					// Load category_item_links.php by default
					$this->item=$item;
					echo $this->loadTemplate('item_links');
				?>
			</div>
			<?php if(($key+1)%($this->params->get('num_links_columns'))==0): ?>
			<div class="clr"></div>
			<?php endif; ?>
			<?php endforeach; ?>
			<div class="clr"></div>
		</div>
		<?php endif; ?>

	</div>

	<!-- Pagination -->
	<?php if($this->pagination->getPagesLinks()): ?>
	<div class="k2Pagination">
		<?php if($this->params->get('catPagination')) echo $this->pagination->getPagesLinks(); ?>
		<?php if($this->params->get('catPaginationResults')) echo '<p class="counter">'.$this->pagination->getPagesCounter().'</p>'; ?>
	</div>
	<?php endif; ?>

	<?php endif; ?>
</div>
<!-- End K2 Category Layout -->
