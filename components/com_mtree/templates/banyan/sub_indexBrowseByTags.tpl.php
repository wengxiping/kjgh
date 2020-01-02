<?php
$itemsSeparatorString = '';

if( !empty($this->mtconf['index_search_by']) && $this->config->getTemParam('displayBrowseBy','1') )
{

	$index_search_by = $this->mtconf['index_search_by'];

	if(!is_array($index_search_by)) {
		$index_search_by = explode('|',$this->mtconf['index_search_by']);
	}

	require_once(JPATH_COMPONENT . '/controllers/searchby.php');

	foreach($index_search_by AS $tag_cf_id)
	{
		$searchBy = new Mosets\searchBy();
		$searchBy->cf_id = $tag_cf_id;
		$tag_values = $searchBy->getTaggableFieldValues();
		$customField = $searchBy->getCustomField();

		?>
		<div class="title mt-index-browse-by-tags-title"><?php echo MText::sprintf('PAGE_HEADER_SEARCH_BY_TAGS', $this->tlcat_id, $customField->caption); ?></div>
		<div class="mt-index-tags">
		<?php
		if(empty($tag_values)) { ?>
			- <?php echo JText::_('COM_MTREE_NO_TAG'); ?> -
		<?php } else { ?>
			<ul class="mt-browse-by-tags">
				<?php
				$i = 0;
				$tag_values_count = count($tag_values);
				foreach ($tag_values AS $tag)
				{
					$i++;
					echo '<li class="mt-browse-by-tag" id="' . $tag->elementId . '">';
					echo '<a href="' . $tag->link . '">';
					echo $tag->value;
					echo ' (' . $tag->items . ')';
					echo '</a>';

					if( isset($itemsSeparatorString) && !empty($itemsSeparatorString) ) {
						if( $i < ($tag_values_count) ) {
							echo '<span class="mt-index-browse-by-tags-separator">' . $itemsSeparatorString . '</span>';
						}
					}

					echo '</li>';
				}
				?>
			</ul>
			<?php
		}
		?></div><?php
	}

}