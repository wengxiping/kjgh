<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
echo JHtml::stylesheet('mod_mt_filter/mod_mt_filter.css',array(),true, false);
$formId = 'modMtFilterForm' . $cat_id;
?>
<div class="search<?php echo $moduleclass_sfx; ?>">
	<form action="<?php echo JRoute::_("index.php") ?>" method="get" name="<?php echo $formId; ?>" id="<?php echo $formId; ?>">

		<div id="modMtFilter<?php echo $cat_id; ?>" class="modMtFilter"<?php echo (!$hasSearchParams)?' style="display:none"':''; ?>>

			<?php
			if( $show_keyword_search )
			{
				echo '<div id="modFilterField_0" class="control-group">';
				echo '<label class="control-label">' . JText::_( 'MOD_MT_FILTER_KEYWORD_SEARCH' ) . '</label>';
				echo '<div class="filterinput controls">';
				echo '<input type="text" name="keyword" value="' . htmlspecialchars($keyword_search) . '">';
				echo '</div>';
				echo '</div>';
			}

			// Availability Search
			if( $show_avl_search )
			{
				echo '<div id="modFilterField_avl" class="control-group">';
				echo '<label class="control-label">' . JText::_( 'MOD_MT_FILTER_AVAILABILITY_SEARCH' ) . '</label>';
				echo '<div class="filterinput controls">';
				echo '<input type="text" name="avl_date_from" id="avl_date_from" value="' . htmlspecialchars($avl_date_from) . '">';
				echo '<input type="text" name="avl_date_to" id="avl_date_to" value="' . htmlspecialchars($avl_date_to) . '">';
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

			$filter_fields->resetPointer();
			while( $filter_fields->hasNext() )
			{
				$filter_field = $filter_fields->getField();
				if($filter_field->hasFilterField())
				{
					echo '<div id="modFilterField_'.$filter_field->getId().'" class="control-group '.$filter_field->getFieldTypeClassName().'">';
					echo '<label class="control-label">' . $filter_field->caption . '</label>';
					echo '<div class="filterinput controls">';
					echo $filter_field->getFilterHTML();
					echo '</div>';
					echo '</div>';
				}
				$filter_fields->next();
			}

			if ( $filter_button ) { ?>
				<span class="button-send"><button type="submit" class="btn" onclick="javascript:var cookie = document.cookie.split(';');for(var i=0;i < cookie.length;i++) {var c = cookie[i];while (c.charAt(0)==' '){c = c.substring(1,c.length);}var name = c.split('=')[0];if( name.substr(0,35) == 'com_mtree_mfields_searchFieldValue_'){document.cookie = name + '=;';}}"><?php echo JText::_( 'MOD_MT_FILTER_FILTER' ) ?></button></span>
			<?php }

			if ( $reset_button ) { ?>
				<span class="button-reset"><button type="button" class="btn" onclick="javascript:var form=jQuery('form[name=<?php echo $formId; ?>] input,form[name=modMtFilterForm<?php echo $cat_id; ?>] select');form.each(function(index,el) {if(el.type=='checkbox'||el.type=='radio'){el.checked=false;} if(el.type=='text'){el.value='';}if(el.type=='select-one'||el.type=='select-multiple'){el.selectedIndex='';}if (el.type == 'hidden' && el.className.indexOf('slider-')>=0) {var s = jQuery('.' + el.className + '.ui-slider');s.slider('values', [s.slider('option', 'min'), s.slider('option', 'max')]);el.value = '';}});jQuery('form[name=<?php echo $formId; ?>]').trigger('submit');var cookie = document.cookie.split(';');for(var i=0;i < cookie.length;i++) {var c = cookie[i];while (c.charAt(0)==' '){c = c.substring(1,c.length);}var name = c.split('=')[0];if( name.substr(0,35) == 'com_mtree_mfields_searchFieldValue_'){document.cookie = name + '=;';}}"><?php echo JText::_( 'MOD_MT_FILTER_RESET' ) ?></button></span>
			<?php } ?>

		</div>

		<input type="hidden" name="option" value="com_mtree" />
		<input type="hidden" name="task" value="listall" />
		<input type="hidden" name="cat_id" value="<?php echo $cat_id ?>" />
		<input type="hidden" name="Itemid" value="<?php echo $intItemid ?>" />
		<?php if( $auto_search ) { ?>
			<script>
				jQuery(document).ready(function() {
					jQuery('#<?php echo $formId; ?> select, #<?php echo $formId; ?> input[type=checkbox], #<?php echo $formId; ?> input[type=radio]').change(function(){
						var cookie = document.cookie.split(';');for(var i=0;i < cookie.length;i++) {var c = cookie[i];while (c.charAt(0)==' '){c = c.substring(1,c.length);}var name = c.split('=')[0];if( name.substr(0,35) == 'com_mtree_mfields_searchFieldValue_'){document.cookie = name + '=;';}}
						jQuery('#<?php echo $formId; ?>').submit();
					});
					jQuery('#<?php echo $formId; ?> div[id*="slider-"]').on( "slidechange", function( event, ui ) {
						jQuery(event.target).closest('form').submit();
					});
				});
			</script>
		<?php } ?>
	</form>
</div>