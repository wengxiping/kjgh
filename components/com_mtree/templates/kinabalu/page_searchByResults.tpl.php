<div id="browse-by-results" class="mt-template-<?php echo $this->template; ?> cf-id-<?php echo $this->cf_id;?> cat-id-<?php echo $this->cat_id ;?> tlcat-id-<?php echo $this->tlcat_id ;?>">
<h2 class="contentheading">
	<?php echo MText::sprintf('PAGE_HEADER_SEARCH_BY_RESULTS', $this->tlcat_id, $this->customfieldcaption, $this->searchword); ?>
</h2>

<?php include $this->loadTemplate( 'sub_listings.tpl.php' ) ?>
</div>