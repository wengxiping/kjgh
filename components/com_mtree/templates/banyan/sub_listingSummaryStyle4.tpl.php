<div class="listing-summary span12 row-fluid<?php

echo ($link->link_featured && $this->config->getTemParam('useFeaturedHighlight','1')) ? ' featured':'';
?>" data-link-id="<?php echo $link->link_id; ?>">
	<?php
	$showImageInSummary = $this->config->getTemParam('showImageInSummary',1);
	if( $showImageInSummary )
	{
		echo '<div class="summary-view-image span3">';
		$this->plugin(
				'ahreflistingimage',
				$link,
				'class="image' . (($this->config->getTemParam('imageDirectionListingSummary','left')=='right') ? '':'-left') . '" alt="'.htmlspecialchars($link->link_name).'"',
				'medium',
				$link->images,
				$this->config->getTemParam('useImageSliderInSummaryView',1)
		);
		echo '</div>';
	}
	?>
	<div class="summary-view-details <?php echo ($showImageInSummary ? 'span6 mt-ls-has-image':'span9 mt-ls-no-image')?>">
		<?php
		if( isAuthorisedToEditListing($link) || isAuthorisedToDeleteListing($link) )
		{
			?>
			<div class="btn-group pull-right"> <a class="btn dropdown-toggle" data-toggle="dropdown" href="#" role="button"> <span class="icon-cog"></span> <span class="caret"></span> </a>
				<ul class="dropdown-menu">
					<?php if( isAuthorisedToEditListing($link) ) { ?>
						<li class="edit-icon">
							<a href="<?php echo JRoute::_('index.php?option=com_mtree&task=editlisting&link_id='.$link->link_id); ?>">
								<span class="icon-edit"></span>
								<?php echo JText::_( 'COM_MTREE_EDIT' ); ?>
							</a>
						</li>
						<?php
					}

					if( $link->link_published && $link->link_approved && isAuthorisedToDeleteListing($link) ) { ?>
						<li class="delete-icon">
							<a href="<?php echo JRoute::_('index.php?option=com_mtree&task=deletelisting&link_id='.$link->link_id); ?>">
								<span class="icon-remove"></span>
								<?php echo JText::_( 'COM_MTREE_DELETE' ); ?>
							</a>
						</li>
					<?php } ?>
				</ul>
			</div>
			<?php
		}
		?>
		<div class="mt-ls-header">
			<h3><?php
				$link_name = $link_fields->getFieldById(1);
				$this->plugin( 'ahreflisting', $link, $link_name->getOutput(2), '', array('delete'=>false, 'edit'=>false) );
				?></h3>
		</div>

		<div class="mt-ls-fields">
			<?php

		if( !empty($config['summary_view']['focus_field_2']) )
		{
			if( $focus2 = $link_fields->getFieldById( $config['summary_view']['focus_field_2'] ) )
			{

				?>
				<div class="mt-ls-field <?php echo $focus2->getFieldTypeClassName(); ?>">
					<?php echo $focus2->getDisplayPrefixText() . $focus2->getOutput() . $focus2->getDisplaySuffixText(); ?>
				</div>
				<?php

			}
		}

		// Review
		if( $this->config->get('show_review') )
		{
			echo '<div class="mt-ls-field reviews">';

			$review_text = $this->reviews_count[$link->link_id]->total . ' ' . strtolower(JText::_( 'COM_MTREE_REVIEWS' ));

			if( $this->reviews_count[$link->link_id]->total > 0 )
			{
				echo '<a href="' . JRoute::_( 'index.php?option=com_mtree&task=viewlink&link_id=' . $link->link_id . '&Itemid='  . $this->Itemid . '#reviews') . '">';
				echo $review_text;
				echo '</a>';
			}
			else
			{
				echo $review_text;
			}

			echo '</div>';
		}
		?>
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

		// Website
		$website = $link_fields->getFieldById(12);
		if(!is_null($website) && $website->hasValue() && $show_website) {
			?>
			<div class="mt-ls-fields">
				<div class="mt-ls-field <?php echo $website->getFieldTypeClassName(); ?>">
					<?php echo $website->getOutput(2); ?>
				</div>
			</div>
			<?php
		}

		// Listing Description
		if(!is_null($link_fields->getFieldById(2)) && !in_array(2,$config['summary_view']['hide_fields'])) {
			$link_desc = $link_fields->getFieldById(2);
			echo '<p class="mt-ls-field '.$link_desc->getFieldTypeClassName().'">';
			echo $link_desc->getOutput(2);
			echo '</p>';
		}

		// Listing's category
		if(!in_array($this->task,array('listcats','home',''))) {
			?>
			<div class="mt-ls-fields">
				<div class="mt-ls-field category">
					<span class="caption"><?php echo JText::_( 'COM_MTREE_CATEGORY' ); ?></span>
					<span class="output"><?php $this->plugin( 'mtpath', $link->cat_id, '' ); ?></span>
				</div>
			</div>
			<?php
		}

		if($this->config->getTemParam('showActionLinksInSummary','0')) {
			echo '<div class="actions">';
			$this->plugin( 'ahrefreview', $link, array("rel"=>"nofollow", "class"=>"btn btn-small") );
			$this->plugin( 'ahrefrecommend', $link, array("rel"=>"nofollow", "class"=>"btn btn-small") );
			$this->plugin( 'ahrefprint', $link, array("rel"=>"nofollow", "class"=>"btn btn-small") );
			$this->plugin( 'ahrefcontact', $link, array("rel"=>"nofollow", "class"=>"btn btn-small") );
			$this->plugin( 'ahrefvisit', $link, JText::_( 'COM_MTREE_VISIT' ), 1, array("rel"=>"nofollow", "class"=>"btn btn-small") );
			$this->plugin( 'ahrefreport', $link, array("rel"=>"nofollow", "class"=>"btn btn-small") );
			$this->plugin( 'ahrefclaim', $link, array("rel"=>"nofollow", "class"=>"btn btn-small") );
			$this->plugin( 'ahrefownerlisting', $link, array("class"=>"btn btn-small") );
			$this->plugin( 'ahrefmap', $link, array("rel"=>"nofollow", "class"=>"btn btn-small") );
			echo '</div>';
		}
		?></div>

	<div class="summary-view-fields span3">
		<?php
		// Focus Field
		$focus1 = $link_fields->getFieldById( $config['summary_view']['focus_field_1'] );

		if(
			!is_null($focus1)
			&&
			$focus1->hasValue()
		)
		{
			?>
			<div class="mt-ls-fields mt-ls-field-focus">
				<div class="mt-ls-field <?php echo $focus1->getFieldTypeClassName(); ?>">
					<?php echo $focus1->getDisplayPrefixText() . $focus1->getOutput() . $focus1->getDisplaySuffixText(); ?>
				</div>
			</div>
			<?php
		}

		// Other custom field
		$link_fields->resetPointer();
		echo '<div class="mt-ls-fields-misc">';
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
					echo "\n\t\t<div class=\"mt-ls-fields row-fluid\">";
					$need_div_closure = true;
				}
				echo "\n\t\t\t" . '<div id="field_'.$field->getId().'" class="mt-ls-field '.$field->getFieldTypeClassName().' span'.round(12/$number_of_columns).(($number_of_columns == 1 || $field_count % $number_of_columns == 0)?' lastFieldRow':'').'">';

				if($field->hasCaption()) {
					echo "\n\t\t\t\t".'<span class="caption">' . $field->getCaption() . '</span>';
					echo '<span class="output">';
					// Special case to always output Price field's 'display prefix' and 'display suffix' text
					if( $field->getId() == 13 && $field->getValue() > 0 )
					{
						echo $field->getDisplayPrefixText();
						echo $field->getOutput(2);
						echo $field->getDisplaySuffixText();
					}
					else
					{
						echo $field->getOutput(2);
					}
					echo '</span>';
				} else {
					echo "\n\t\t\t\t".'<span class="output">' . $field->getOutput(2) . '</span>';
				}
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
		echo '</div>';
		?>
	</div>
</div>