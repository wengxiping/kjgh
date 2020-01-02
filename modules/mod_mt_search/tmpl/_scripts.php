<?php defined('_JEXEC') or die('Restricted access');

if( $useSearchCompletion ) {
?>
<script type="text/javascript">
	jQuery(function() {

		jQuery('#<?php echo $searchInputId; ?>').typeahead({
			source: {
				<?php if ($searchCompletionSearchCategory) { ?>
				"<?php echo JText::_( "MOD_MT_SEARCH_SEARCH_CATEGORIES" ) ?>": {
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
				"<?php echo JText::_( "MOD_MT_SEARCH_SEARCH_LISTINGS" ) ?>": {
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