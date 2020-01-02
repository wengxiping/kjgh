<?php
$hasImage = false;
if( isset($this->cat->cat_image) && $this->cat->cat_image <> '') {
	$hasImage = true;
}
?>
<div class="mt-page-category mt-template-<?php echo $this->template; ?> cat-id-<?php echo $this->cat->cat_id ;?> tlcat-id-<?php echo $this->tlcat_id ;?>">

	<?php if ($hasImage)
	{ ?>
		<div class="mt-category-header-card" style="background-image: url(<?php
		echo $this->config->getjconf('live_site') . $this->config->get('relative_path_to_cat_original_image') . rawurlencode($this->cat->cat_image) . ');" alt="' . htmlspecialchars($this->cat->cat_name);
		?>">
	<?php } else { ?>
			<div class="mt-category-header-no-image-card">
	<?php }?>
	</div>

	<div class="mt-category-header-title-desc">
		<h1><?php echo htmlspecialchars(MText::sprintf('PAGE_HEADER_LISTCATS', $this->tlcat_id, $this->cat->cat_name)) ?><?php echo ($this->mtconf['show_category_rss']) ? $this->plugin('showrssfeed', 'new') : ''; ?></h1>

		<?php
		if ((isset($this->cat->cat_image) && $this->cat->cat_image <> '') || (isset($this->cat->cat_desc) && $this->cat->cat_desc <> ''))
		{
			echo '<div class="mt-category-desc">';
			if (isset($this->cat->cat_desc) && $this->cat->cat_desc <> '')
			{
				echo $this->cat->cat_desc;
			}
			echo '</div>';
		}
		?>
	</div>

	<?php if ($this->config->get('display_categories') && isset($this->categories) && is_array($this->categories) && !empty($this->categories)) { ?>
		<div class="mt-category-subcats">
		<?php
		$numOfColumns = $this->config->getTemParam('numOfSubcatsColumns',3);
		$span = round(12 / $numOfColumns);
		$i = 0;

		###
		# Sub Categories
		###

		foreach ($this->categories as $cat) {
			if($this->task == 'listalpha' && $this->config->getTemParam('onlyShowRootLevelCatInListalpha',0) && $cat->cat_parent > 0) {
				continue;
			}

			if( $i % $numOfColumns == 0 ) {
				echo '<div class="row-fluid">';
			}

			echo '<div class="mt-category-subcats-item span'.$span.'">';
			if($cat->cat_featured) echo '<strong>';

			echo '<a href="' .  JRoute::_("index.php?option=$this->option&task=listcats&cat_id=$cat->cat_id&Itemid=$this->Itemid") . '">';
			echo htmlspecialchars($cat->cat_name);

			if( $this->config->getTemParam('displaySubcatsCatCount','0') ) {
				$count[] = $cat->cat_cats;
			}
			if( $this->config->getTemParam('displaySubcatsListingCount','1') ) {
				$count[] = $cat->cat_links;
			}
			if( !empty($count) ) {
				echo ' <small>('.implode('/',$count).')</small>';
				unset($count);
			}
			echo '</a>';

			if($cat->cat_featured) echo '</strong>';
			echo '</div>';

			if( $i % $numOfColumns == ($numOfColumns -1) || $i == (count($this->categories)-1)) {
				echo '</div>';
			}

			$i++;
		}
		?></div><?php
	}

	###
	# Related Categories
	###
	if ( isset($this->related_categories) && count($this->related_categories) > 0 ) {
		echo '<div class="mt-category-relcats">';
		?><div class="title"><?php echo JText::_( 'COM_MTREE_RELATED_CATEGORIES' ); ?></div><?php
		echo '<ul>';
		foreach( $this->related_categories AS $related_category ) {
			echo '<li>';
			$this->plugin('ahref', "index.php?option=com_mtree&task=listcats&cat_id=".$related_category."&Itemid=$this->Itemid", $this->pathway->printPathWayFromCat_withCurrentCat( $related_category ));
			echo '</li>';
		}
		echo '</ul>';
		echo '</div>';
	}

	if ( $this->show_add_listing_link ) {
		?>
		<p class="pull-right mt-addlisting">
			<a href="<?php echo JRoute::_( "index.php?option=com_mtree&task=addlisting&cat_id=$this->cat_id&Itemid=$this->Itemid" ); ?>" class="btn btn-small">
				<span class="icon-plus"></span>
				<?php echo MText::_( 'ADD_YOUR_LISTING_HERE', $this->tlcat_id ); ?>
			</a>
		</p>
		<?php
	}

	?>
</div>