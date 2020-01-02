<div class="mt-page-search mt-template-<?php
	echo $this->template; ?> cat-id-<?php
	echo (isset($this->cat->cat_id)?$this->cat->cat_id:0) ;?> tlcat-id-<?php
	echo (isset($this->tlcat_id)?$this->tlcat_id:0) ;?> row-fluid">

	<?php if(!empty($this->searchword)): ?>
	<h1 class="contentheading"><?php echo JText::sprintf( 'COM_MTREE_SEARCH_RESULTS_FOR_KEYWORD', $this->searchword ) ?></h1>
	<?php endif; ?>

	<?php include $this->loadTemplate( 'sub_search.tpl.php' ) ?>

	<?php include $this->loadTemplate( 'sub_subCats.tpl.php' ) ?>

	<?php include $this->loadTemplate( 'sub_listings.tpl.php' ) ?>

</div>
