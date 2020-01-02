<div class="mt-page-browse-by" class="mt-template-<?php echo $this->template; ?> cat-id-<?php echo $this->cat_id ;?> tlcat-id-<?php echo $this->tlcat_id ;?>">

	<h1 class="contentheading"><span class="customfieldcaption"><?php echo MText::_('PAGE_HEADER_SEARCH_BY', $this->tlcat_id); ?></span></h1>

	<p><?php echo MText::_('SEARCH_BY_DESC', $this->tlcat_id); ?></p>

	<?php
	if( empty($this->taggable_fields) ) {

		?>
		<p><?php echo JText::_( 'COM_MTREE_YOUR_SEARCH_DOES_NOT_RETURN_ANY_RESULT' ) ?></p>
		<?php

	} else {
		if($this->pageNav->total > 0) {
			?>
			<ul class="mt-browse-by-tag-fields">
			<?php
			$i = 0;
			foreach ($this->taggable_fields AS $taggable_field) {
				$i++;
				echo '<li class="mt-browse-by-tag-field" id="'.$taggable_field->elementId.'">';
				echo '<a href="'.$taggable_field->link.'">';
				echo $taggable_field->value;
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
			</div>
			<?php
			}
		}
	}
?></div>