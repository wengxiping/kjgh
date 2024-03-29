<!-- Listing Details Style 4 -->
<div id="listing" class="row-fluid link-id-<?php echo $this->link_id; ?> cat-id-<?php echo $this->link->cat_id; ?> tlcat-id-<?php echo $this->link->tlcat_id; ?>">
<?php
// Listing title
include $this->loadTemplate( 'sub_listingDetailsTitle.tpl.php' );

if ( !empty($this->mambotBeforeDisplayContent) && $this->mambotBeforeDisplayContent[0] <> '' ) {
	echo trim( implode( "\n", $this->mambotBeforeDisplayContent ) ); 
}

echo '<div class="row-fluid">';

if (
	($this->config->getTemParam('skipFirstImage','0') == 1 && count($this->images) >= 2)
	||
	($this->config->getTemParam('skipFirstImage','0') == 0 && !empty($this->images))
) {
	echo '<div class="span7">';
	include $this->loadTemplate( 'sub_images.tpl.php' );
	echo '</div>';
}

echo '<div class="span'.(!empty($this->images)? '5':' 12').'">';

// Other custom fields
include $this->loadTemplate( 'sub_listingDetailsFields.tpl.php' );

echo '</div>';

echo '<div class="listing-desc bottom">';
if(!is_null($this->fields->getFieldById(2))) {
	$link_desc = $this->fields->getFieldById(2);
	echo '<span itemprop="description">';
	if( $link_desc->hasValue() )
	{
		echo $link_desc->getDisplayPrefixText();
		echo $link_desc->getOutput(1);
		echo $link_desc->getDisplaySuffixText();
	}
	echo '</span>';
}
echo '</div>';
if ( !empty($this->mambotAfterDisplayContent) ) { echo trim( implode( "\n", $this->mambotAfterDisplayContent ) ); }

// Rating & Favourite Box
include $this->loadTemplate( 'sub_listingDetailsRatingFavourite.tpl.php' );

// Action buttons
include $this->loadTemplate( 'sub_listingDetailsActions.tpl.php' );

echo '</div>'; // end of .row-fluid

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
</div>