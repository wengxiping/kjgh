<div class="mt-page-category mt-template-<?php echo $this->template; ?> cat-id-<?php echo $this->cat->cat_id ;?> tlcat-id-<?php echo $this->tlcat_id ;?>">
	<div class="mt-category-header">
		<h1><?php echo htmlspecialchars(MText::sprintf('PAGE_HEADER_LISTCATS', $this->tlcat_id, $this->cat->cat_name)) ?><?php echo ($this->mtconf['show_category_rss']) ? $this->plugin('showrssfeed','new') : ''; ?></h1>
	</div>
	<?php
	if ( (isset($this->cat->cat_image) && $this->cat->cat_image <> '') || (isset($this->cat->cat_desc) && $this->cat->cat_desc <> '') ) {
		echo '<div class="mt-category-desc">';
		if (isset($this->cat->cat_image) && $this->cat->cat_image <> '') {
			echo '<div class="mt-category-image">';
			echo '<img src="'.$this->config->getjconf('live_site').$this->config->get('relative_path_to_cat_small_image') . $this->cat->cat_image.'" alt="'.$this->cat->cat_name.'" />';
			echo '</div>';
		}
		if ( isset($this->cat->cat_desc) && $this->cat->cat_desc <> '') {	echo $this->cat->cat_desc; }
		echo '</div>';
	}

	include $this->loadTemplate( 'sub_subCats.tpl.php' );

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

?></div>