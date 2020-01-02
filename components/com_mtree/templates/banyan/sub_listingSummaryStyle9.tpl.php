<?php
// Notes:
// - There is no pulldown menu to edit or delete listing.
// - Only non-links and non-taggable field will appear correctly.
// - Due to the compactness of the style for each summary, the following are not available:
//  - Captions.
//  - Action links (Report, Recommend, Review, Visit etc.)
//  - Website
//  - Category


?><div class="listing-summary span12 row-fluid<?php

echo ($link->link_featured && $this->config->getTemParam('useFeaturedHighlight','1')) ? ' featured':'';
?>" data-link-id="<?php echo $link->link_id; ?>">
	<?php

	if( $link->link_approved )
	{
	?>
	<a class="mt-ls-mainlink" href="<?php echo JRoute::_('index.php?option=com_mtree&task=viewlink&link_id=' . $link->link_id . '&Itemid=' . $this->Itemid); ?>">
	<?php
	}

	$showImageInSummary = $this->config->getTemParam('showImageInSummary',1);
	if( $showImageInSummary )
	{
		echo '<div class="summary-view-image">';
		if ($link->link_image) {
			?>
			<img
					src="<?php echo $this->config->getjconf('live_site').$this->config->get('relative_path_to_listing_medium_image').$link->link_image;?>"
					class="image<?php echo (($this->config->getTemParam('imageDirectionListingSummary','left')=='right') ? '':'-left'); ?>"
			>
			<?php
		}
		else if ( $this->config->getTemParam('showFillerImage',1) )
		{
			?>
			<img src="<?php echo $this->config->getjconf('live_site') . $this->config->get('relative_path_to_images'); ?>noimage_thb.png"
			     class="image<?php echo(($this->config->getTemParam('imageDirectionListingSummary', 'left') == 'right') ? '' : '-left'); ?>"
			     alt=""/>
			<?php
		}
		echo '</div>';

	}
	?>
	<div class="mt-ls-header">
		<h3><?php
			$link_name = $link_fields->getFieldById(1);
			$this->plugin( 'ahreflisting', $link, $link_name->getOutput(2), '', array(
					'link'=>false,
					'delete'=>false,
					'edit'=>false,
			) );
			?></h3>
	</div>
	<?php

	// Address
	if( $this->config->getTemParam('displayAddressInOneRow','1') ) {
		$address_parts = array();
		foreach( array( 4,5,6,7,8 ) AS $address_field_id )
		{
			$field = $link_fields->getFieldById( $address_field_id );
			if ( isset($field) && ($output = $field->getOutput(1)) )
			{
				$address_parts[$field->ordering] = $output;
			}
		}

		ksort($address_parts,SORT_NUMERIC);
		if( !empty($address_parts) ) {
			echo '<div class="mt-ls-fields address">' . implode(', ',$address_parts) . '</div>';
		}
	}

	// Review
	if( $this->config->get('show_review') )
	{
		// Only show if listing has 1 or more reviews.
		if( $this->reviews_count[$link->link_id]->total > 0 ) {
			echo '<div class="mt-ls-field reviews">';
			$review_text = $this->reviews_count[$link->link_id]->total . ' ' . strtolower(JText::_( 'COM_MTREE_REVIEWS' ));
			echo $review_text;
			echo '</div>';
		}
	}

	// Other custom field
		$link_fields->resetPointer();
		echo '<span class="mt-ls-fields-misc">';
		$number_of_columns = $this->config->getTemParam('numOfColumnsInSummaryView','1');
		$field_count = 1;
		$need_div_closure = false;
		while( $link_fields->hasNext() ) {
			$field = $link_fields->getField();
			$value = $field->getOutput(2);
			$hasValue = $field->hasValue();
			if(
					(
							(
									!$field->hasInputField() && !$field->isCore() && empty($value))
							||
							(!empty($value) || $value == '0')
					)
					&&
					!in_array($field->getId(),$skipped_field_ids)
					&&
					(
							($this->config->getTemParam('displayAddressInOneRow','1') && !in_array($field->getId(),array(4,5,6,7,8))
									||
									$this->config->getTemParam('displayAddressInOneRow','1') == 0
							)
							&&
							$hasValue
							&&
							// User has the view levels to access the custom field
							in_array($field->viewAccessLevel,$this->myAuthorisedViewLevels)

					)
			) {
				// Start of a row
				if( $number_of_columns == 1 || $field_count % $number_of_columns == 1 ) {
					echo "\n\t\t<div class=\"mt-ls-fields\">";
					$need_div_closure = true;
				}
				echo "\n\t\t\t" . '<div id="field_'.$field->getId().'" class="mt-ls-field '.$field->getFieldTypeClassName().' ' . (($number_of_columns == 1 || $field_count % $number_of_columns == 0)?' lastFieldRow':'').'">';
				echo "\n\t\t\t\t".'<span class="output">' . $field->getOutput(2) . '</span>';
				echo "\n\t\t\t".'</div>';

				// End of a row
				if( $number_of_columns == 1 || $field_count % $number_of_columns == 0 ) {
					echo "\n\t\t</div>";
					$need_div_closure = false;
				}
				$field_count++;
			}
			$link_fields->next();
		}
		if( $need_div_closure ) {
			echo "\n\t\t</div>";
			$need_div_closure = false;
		}
		echo '</span>';


	// Listing Description
	if(!is_null($link_fields->getFieldById(2)) && !in_array(2,$config['summary_view']['hide_fields'])) {
		$link_desc = $link_fields->getFieldById(2);
		echo '<span class="mt-ls-field '.$link_desc->getFieldTypeClassName().'">';
		echo $link_desc->getOutput(2);
		echo '</span>';
	}
	?>

	<?php if( $link->link_approved ) { ?>
	</a>
	<?php } ?>
</div>