<?php
$searchFormId = 'mt-component-search-form';
$searchInputId = 'mt-component-search-form-input';

$searchCompletionSearchCategory = true;

$sourceUrlListing = JURI::root() . '?option=com_mtree&task=search.completion&format=json&cat_id=' . $this->cat_id . '&Itemid=' . $this->Itemid . '&type=listing';
$sourceUrlCategory = JURI::root() . '?option=com_mtree&task=search.completion&format=json&cat_id=' . $this->cat_id . '&Itemid=' . $this->Itemid . '&type=category';

$searchCompletionShowImage = true;

$maxListings = 8;

$placeholder_text = MText::_( 'SEARCH_PLACEHOLDER', $this->tlcat_id );

$search_button = true;

$advsearch = false;

$searchCategory = false;

$parent_cat_id = $this->cat_id;

$searchword = '';

if( isset($this->searchword) && !empty($this->searchword) ) {
	$searchword = $this->searchword;
}

if( true ) {
?>
<script type="text/javascript">
	jQuery(function() {

		jQuery('#<?php echo $searchInputId; ?>').typeahead({
			source: {
				<?php if ($searchCompletionSearchCategory) { ?>
				"<?php echo JText::_( "COM_MTREE_SEARCH_CATEGORIES" ) ?>": {
					url: [
						{
							type: "POST",
							url: "<?php echo $sourceUrlCategory; ?>",
							data: {searchword: "{{query}}" }
						}],
					template: '<span class="row">' +
					'<span class="catname">{{cat_name}}</span>' +
					"</span>",
					display: "cat_name"
				},
				<?php } ?>
				"<?php echo JText::_( "COM_MTREE_SEARCH_LISTINGS" ) ?>": {
					url: [
						{
							type: "POST",
							url: "<?php echo $sourceUrlListing; ?>",
							data: {searchword: "{{query}}" }
						}]
				}
			},
			template: '<span class="row">' +
			<?php if ($searchCompletionShowImage) { ?>
			'<span class="typeahead-result-thumbnail">' +
			'<img src="{{image_url}}">' +
			"</span>" +
			<?php } ?>
			'<span class="name">{{link_name}}</span>' +
			"</span>",
			callback: {
				onClickAfter: function (node, a, item, event) {
					window.location.href = item.href;
				}
			},
			display: ["link_name"],
			dynamic: true,
			maxItem: <?php echo $maxListings; ?>,
			maxItemPerGroup: <?php echo $maxListings; ?>,
			minLength: 1,
			group: true
		});
	});
</script>
<?php }
?>
<div class="mt-component-search">
<form action="<?php echo JRoute::_('index.php');?>" method="post" class="mt-search-form form-inline typeahead-container search" id="<?php echo $searchFormId; ?>">
	<span class="typeahead-query">
		<input type="search"
		       id="<?php echo $searchInputId; ?>"
		       name="searchword"
		       maxlength="<?php echo $this->mtconf['limit_max_chars']; ?>"
		       value="<?php echo htmlspecialchars($searchword); ?>"
		       placeholder="<?php echo $placeholder_text; ?>"
		       autocomplete="off"
		/>
	</span>

	<?php if ( $search_button ) { ?>
		<button type="submit" class="mt-search-button"></button>
	<?php } ?>

	<?php if ( $advsearch ) { ?>
		<a href="<?php echo $advsearch_link; ?>"><?php echo JText::_( 'COM_MTREE_ADVANCED_SEARCH' ) ?></a>
	<?php } ?>

	<input type="hidden" name="option" value="com_mtree" />
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="task" value="search" />
	<?php if ( $searchCategory == 1 ) { ?>
		<input type="hidden" name="search_cat" value="1" />
	<?php } ?>
	<?php
	if( $parent_cat_id > 0 )
	{
		?>
		<input type="hidden" name="cat_id" value="<?php echo $parent_cat_id; ?>" />
		<?php
	}
	?>
</form>
</div>