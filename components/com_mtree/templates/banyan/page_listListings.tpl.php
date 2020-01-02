<?php
require "setup.php";

if( $this->config->getTemParam('useImageSliderInSummaryView',1) ) {
?>
<script type="text/javascript" src="media/com_mtree/js/flexslider/jquery.flexslider-min.js"></script>
<script>
	jQuery(window).load(function() {
		jQuery('.flexslider').flexslider({
			animation: "fade",
			prevText: "",
			nextText: "",
			animationSpeed: "200",
			slideshow: false

		});
	});
</script>
<?php }

$listingSummaryImageHeight = $this->config->getTemParam('listingSummaryImageHeight','');

if( $listingSummaryImageHeight > 0 )
{
	?>
	<style scoped>
		.listing-summary img.image-left {
			height: <?php echo $listingSummaryImageHeight; ?>px;
		}
	</style>
	<?php
}
?>
<div class="mt-listings mt-ls-style-<?php echo $this->config->getTemParam('summaryStyle',1); ?>">
<div id="top-listings" class="mt-template-<?php echo $this->template; ?> cat-id-<?php echo $this->cat->cat_id ;?> tlcat-id-<?php echo $this->tlcat_id ;?>">

	<h1 class="contentheading"><?php echo $this->header ?>&nbsp;<?php echo $this->plugin('showrssfeed',$this->task); ?></h1>
	<?php if( $this->task == 'listall'): ?>
	<form action="<?php echo JRoute::_("index.php") ?>" method="get" name="mtFormAllListings" id="mtFormAllListings" class="form-horizontal">
<span class="mt-sort-by">
	<label for="sort"><?php echo JText::_( 'COM_MTREE_SORT_BY' );?></label>
	<?php echo $this->lists['sort']; ?>
</span>
		<?php endif; ?>

		<div class="mt-listings">

			<div class="mt-listings-pages">
				<span class="mt-x-listings"><?php echo $this->pageNav->getResultsCounter(); ?></span>
				<?php // echo $this->pageNav->getPagesLinks(); ?>
				<?php if( in_array($this->task,array('listcats','listall')) && $this->mtconf['display_all_listings_link']): ?>
					<span class="category-scope">
		<?php
		if( $this->task == 'listcats' ) {
			echo '<strong>'.JTEXT::_( 'COM_MTREE_THIS_CATEGORY' ).'</strong>';
		} else {
			echo '<a href="';
			echo JRoute::_('index.php?option=com_mtree&task=listcats&cat_id='.$this->cat_id);
			echo '">';
			echo JTEXT::_( 'COM_MTREE_THIS_CATEGORY' );
			echo '</a>';
		}
		echo ' · ';
		if( $this->task == 'listall' ) {
			echo '<strong>'.MTEXT::_( 'ALL_LISTINGS', $this->tlcat_id ).'</strong>';
		} else {
			echo '<a href="';
			echo JRoute::_('index.php?option=com_mtree&task=listall&cat_id='.$this->cat_id);
			echo '">';
			echo MText::_( 'ALL_LISTINGS', $this->tlcat_id );
			echo '</a>';
		}
		?>
	</span>
				<?php endif; ?>
			</div>

			<?php if(
				$this->mtconf['display_filters']
				&&
				($this->hasSearchParams || $this->task == 'listall')
			): ?>
				<div class="mt-filter">
					<a href="#" onclick="javascript:jQuery('#comMtFilter<?php echo $this->cat_id; ?>').slideToggle('300'); return false;"><?php echo MText::_( 'FILTER_LISTINGS', $this->tlcat_id ); ?></a>
					<div id="comMtFilter<?php echo $this->cat_id; ?>" class="mt-filter-component"<?php echo (!$this->hasSearchParams)?' style="display:none"':''; ?>>
						<?php
						if( $this->show_keyword_search )
						{
							echo '<div id="modFilterField_0" class="control-group">';
							echo '<label class="control-label">' . JText::_( 'COM_MTREE_KEYWORD_SEARCH' ) . '</label>';
							echo '<div class="mt-filter-input controls">';
							echo '<input type="text" name="keyword" value="' . htmlspecialchars($this->keyword_search) . '">';
							echo '</div>';
							echo '</div>';
						}

						// Availability Search
						if( $this->show_avl_search )
						{
							echo '<div id="modFilterField_avl" class="control-group">';
							echo '<label class="control-label">' . JText::_( 'COM_MTREE_AVAILABILITY_SEARCH' ) . '</label>';
							echo '<div class="mt-filter-input controls">';
							echo '<input type="text" name="avl_date_from" id="avl_date_from" value="' . $this->avl_date_from . '">';
							echo '<input type="text" name="avl_date_to" id="avl_date_to" value="' . $this->avl_date_to . '">';
							echo '</div>';
							echo '</div>';
							?>
							<script type="text/javascript">
								jQuery(document).ready(function() {
									jQuery('#avl_date_from,#avl_date_to').datepick({onSelect: customRange, dateFormat: jQuery.datepick.ISO_8601});

									function customRange(dates) {
										if (this.id == 'avl_date_from') {
											jQuery('#avl_date_to').datepick('option', 'minDate', dates[0] || null);
										}
										else {
											jQuery('#avl_date_from').datepick('option', 'maxDate', dates[0] || null);
										}
									}
								});
							</script>
							<?php
						}

						$this->filter_fields->resetPointer();
						while( $this->filter_fields->hasNext() )
						{
							$filter_field = $this->filter_fields->getField();
							if($filter_field->hasFilterField())
							{
								echo '<div id="comFilterField_'.$filter_field->getId().'" class="control-group '.$filter_field->getFieldTypeClassName().'">';
								echo '<label class="control-label">' . $filter_field->caption . ':' . '</label>';
								echo '<div class="mt-filter-input controls">';
								echo $filter_field->getFilterHTML();
								echo '</div>';
								echo '</div>';
							}
							$this->filter_fields->next();
						}
						?>
						<span class="button-send"><button type="submit" class="btn" onclick="javascript:var cookie = document.cookie.split(';');for(var i=0;i < cookie.length;i++) {var c = cookie[i];while (c.charAt(0)==' '){c = c.substring(1,c.length);}var name = c.split('=')[0];if( name.substr(0,35) == 'com_mtree_mfields_searchFieldValue_'){document.cookie = name + '=;';}}"><?php echo JText::_( 'COM_MTREE_SEARCH' ) ?></button></span>
<span class="button-reset"><button class="btn" onclick="javascript:var form=jQuery('form[name=mtFormAllListings] input,form[name=mtFormAllListings] select');form.each(function(index,el){if(el.type=='checkbox'||el.type=='radio'){el.checked=false;}if(el.type=='text'){el.value='';}if(el.type=='select-one'||el.type=='select-multiple'){el.selectedIndex='';}if (el.type == 'hidden' && el.className.indexOf('slider-')>=0) {var s = jQuery('.'+el.className+'.ui-slider');s.slider('values',[s.slider('option','min'),s.slider('option','max')]);el.value = '';}
});var cookie = document.cookie.split(';');for(var i=0;i < cookie.length;i++) {var c = cookie[i];while (c.charAt(0)==' '){c = c.substring(1,c.length);}var name = c.split('=')[0];if( name.substr(0,35) == 'com_mtree_mfields_searchFieldValue_'){document.cookie = name + '=;';}}jQuery('form[name=mtFormAllListings]').submit();"><?php echo JText::_( 'COM_MTREE_RESET' ) ?></button></span>
					</div>
				</div>
			<?php else:

				$this->filter_fields->resetPointer();
				while( $this->filter_fields->hasNext() )
				{
					$filter_field = $this->filter_fields->getField();
					if($filter_field->hasFilterField() && $filter_field->hasSearchValue())
					{
						echo $filter_field->getHiddenHTML();
					}
					$this->filter_fields->next();
				}

			endif;

			if( $this->show_map ) {
				include $this->loadTemplate('sub_clusterMap.tpl.php');
			}

			?>

			<div class="mt-listings-spacing-top"></div>

			<div class="mt-listings-list">
			<?php
			if( !empty($this->links) )
			{
				$i = 0;
				foreach ($this->links AS $link) {
					$i++;
					$link_fields = $this->links_fields[$link->link_id];
					include $this->loadTemplate('sub_listingSummary.tpl.php');
				}
			}
			?>
			</div>
			<?php
			if( $this->pageNav->total > 0 ) { ?>
				<div class="pagination mt-pagination">
					<p class="counter pull-right">
						<?php echo $this->pageNav->getPagesCounter(); ?>
					</p>
					<?php echo $this->pageNav->getPagesLinks(); ?>
				</div>
			<?php }
			?></div>

		<?php if( $this->task == 'listall'): ?>
		<input type="hidden" name="option" value="<?php echo $this->option ?>" />
		<input type="hidden" name="task" value="listall" />
		<input type="hidden" name="cat_id" value="<?php echo $this->cat_id ?>" />
		<input type="hidden" name="Itemid" value="<?php echo $this->Itemid ?>" />
	</form>
<?php endif; ?>
</div>
</div>