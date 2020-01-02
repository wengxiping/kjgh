<div class="mt-page-browse-by-results" id="mt-page-browse-by-<?php echo $this->page_id; ?>" class="mt-template-<?php echo $this->template; ?> cf-id-<?php echo $this->cf_id;?> cat-id-<?php echo $this->cat_id ;?> tlcat-id-<?php echo $this->tlcat_id ;?>">
	<h1 class="contentheading">
		<?php echo MText::sprintf('PAGE_HEADER_SEARCH_BY_RESULTS', $this->tlcat_id, $this->customfieldcaption, $this->searchword); ?>
	</h1>

	<?php include $this->loadTemplate( 'sub_listings.tpl.php' ) ?>
</div>