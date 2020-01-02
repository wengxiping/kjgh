<?php defined('_JEXEC') or die('Restricted access');

require JModuleHelper::getLayoutPath('mod_mt_search', '_scripts');
?>
<form action="<?php echo JRoute::_('index.php');?>" method="post" class="form-inline search<?php echo $moduleclass_sfx; ?>" id="<?php echo $searchFormId; ?>">
	<div class="typeahead-container control-group">
		<span class="typeahead-query controls">
			<input type="search"
			       id="<?php echo $searchInputId; ?>"
			       name="searchword"
			       maxlength="<?php echo $mtconf->get('limit_max_chars'); ?>"
			       value="<?php echo htmlspecialchars($searchword); ?>"
			       placeholder="<?php echo $placeholder_text; ?>"
			       autocomplete="off"
				/>
            </span>
	</div>

	<?php if( $lists['categories'] ) { ?>
		<div class="control-group">
			<div class="controls">
				<?php echo $lists['categories']; ?>
			</div>
		</div>
	<?php
	} ?>

	<?php if ( $search_button ) { ?>
		<div class="control-group">
			<div class="controls">
				<button type="submit" class="btn"><?php echo JText::_( 'MOD_MT_SEARCH_SEARCH' ) ?></button>
			</div>
		</div>
	<?php } ?>

	<?php if ( $advsearch ) { ?>
		<div class="control-group">
			<div class="controls">
				<a href="<?php echo $advsearch_link; ?>"><?php echo JText::_( 'MOD_MT_SEARCH_ADVANCED_SEARCH' ) ?></a>
			</div>
		</div>
	<?php } ?>

	<input type="hidden" name="option" value="com_mtree" />
	<input type="hidden" name="Itemid" value="<?php echo str_replace('&Itemid=','',$itemid); ?>" />
	<input type="hidden" name="task" value="search" />
	<?php if ( $searchCategory == 1 ) { ?>
		<input type="hidden" name="search_cat" value="1" />
	<?php } ?>
	<?php
	if( $parent_cat_id > 0 && is_null($lists['categories']) )
	{
		?>
		<input type="hidden" name="cat_id" value="<?php echo $parent_cat_id; ?>" />
	<?php
	}
	?>
</form>