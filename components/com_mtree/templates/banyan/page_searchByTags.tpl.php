<div class="mt-page-browse-by-tags" id="mt-page-browse-by-<?php echo $this->page_id; ?>" class="mt-template-<?php echo $this->template; ?> cf-id-<?php echo $this->cf_id;?> cat-id-<?php echo $this->cat_id ;?> tlcat-id-<?php echo $this->tlcat_id ;?>">

	<h1 class="contentheading"><span class="customfieldcaption"><?php echo MText::sprintf('PAGE_HEADER_SEARCH_BY_TAGS', $this->tlcat_id, $this->customfieldcaption); ?></span></h1>

	<?php

	if( empty($this->tags) ) {

		?>
		<p><?php echo JText::_( 'COM_MTREE_YOUR_SEARCH_DOES_NOT_RETURN_ANY_RESULT' ) ?></p>
		<?php

	} else {
		if($this->pageNav->total > 0) {
			?>
			<ul class="mt-browse-by-tags">
			<?php
			$i = 0;
			foreach ($this->tags AS $tag) {
				$i++;
				echo '<li class="mt-browse-by-tag" id="'.$tag->elementId.'">';
				echo '<a href="'.$tag->link.'">';
				echo $tag->value;
				echo ' ('.$tag->items.')';
				echo '</a>';
				echo '</li>';
			}
			?>
			</ul>
			<?php
			if( $this->pageNav->total > $this->pageNav->limit ) { ?>
			<div class="pagination">
				<p class="counter pull-right">
					<?php echo $this->pageNav->getPagesCounter(); ?>
				</p>
				<?php echo $this->pageNav->getPagesLinks(); ?>
			</div>		<?php
			}
		}
	}
?></div>