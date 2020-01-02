<?php
// Listing title
include $this->loadTemplate( 'sub_listingDetailsTitle.tpl.php' );

// Address
$address = '';
if( $this->config->getTemParam('displayAddressInOneRow','1') ) {
	$address_parts = array();
	$address_displayed = false;
	foreach( array( 4,5,6,7,8 ) AS $address_field_id )
	{
		$field = $this->fields->getFieldById($address_field_id);
		if( isset($field) && $output = $field->getOutput(1) )
		{
			$address_parts[$field->ordering] = $output;

		}
	}

	ksort($address_parts,SORT_NUMERIC);
	if( !empty($address_parts) ) { $address = implode(', ',$address_parts); }
}
?>
<div id="field_4" class="mfieldtype_coreaddress">
	<?php echo $address; ?>
</div>
<?php

if ( !empty($this->mambotBeforeDisplayContent) && $this->mambotBeforeDisplayContent[0] <> '' ) { 
	echo trim( implode( "\n", $this->mambotBeforeDisplayContent ) ); 
}
echo '<div class="row-fluid"><div class="span12">';

if (!empty($this->images)) {
	include $this->loadTemplate( 'sub_images.tpl.php' );
}

?>
<div class="mt-ld-container mt-ld-main_stats">
	<?php
	foreach($config['details_view']['main_attr_fields'] AS $cf_id) {
		if($field = $this->fields->getFieldById( $cf_id ))
		{
			?>
			<div class="mt-ld-field <?php echo $field->getFieldtypeClassName(); ?>">
				<span class="output"><?php echo $field->getDisplayPrefixText() . $field->getOutput() . $field->getDisplaySuffixText(); ?></span>
				<span class="caption"><?php echo $field->getCaption(); ?></span>
			</div>
			<?php
		}
	}
	?>
</div>

<div class="mt-ld-container mt-ld-property_details">
	<?php
	// Other custom fields
	include $this->loadTemplate( 'sub_listingDetailsFields.tpl.php' );

	?>
</div>

<?php

if(!is_null($this->fields->getFieldById(2))) {
	$link_desc = $this->fields->getFieldById(2);
	?>
	<div class="mt-ld-property_desc">
	<h3><?php echo $link_desc->getCaption(); ?></h3>
	<?php
	echo '<span itemprop="description">';
	if( $link_desc->hasValue() )
	{
		echo $link_desc->getDisplayPrefixText();
		echo $link_desc->getOutput(1);
		echo $link_desc->getDisplaySuffixText();
	}
	echo '</span>';
	echo '</div>';
}

if ( !empty($this->mambotAfterDisplayContent) ) { echo trim( implode( "\n", $this->mambotAfterDisplayContent ) ); }

// Rating & Favourite Box
include $this->loadTemplate( 'sub_listingDetailsRatingFavourite.tpl.php' );

echo '</div></div>';


// Action buttons
include $this->loadTemplate( 'sub_listingDetailsActions.tpl.php' );

include $this->loadTemplate( 'sub_listingShare.tpl.php' );

// Load User Profile
if( $this->config->get('show_user_profile_in_listing_details') )
{
	include $this->loadTemplate( 'sub_userProfile.tpl.php' );
}

// Load Contact Owner Form
if( $this->config->get('contact_form_location') == 2 )
{
	include $this->loadTemplate( 'sub_contactOwnerForm.tpl.php' );
}

?>