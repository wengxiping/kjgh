<?php
require 'setup.php';

$skipped_field_ids = array(1,2,12);

$skipped_field_ids[] = $config['summary_view']['focus_field_1'];
$skipped_field_ids[] = $config['summary_view']['focus_field_2'];
$skipped_field_ids = array_merge(
	$skipped_field_ids,
	$config['summary_view']['main_attr_fields']
);

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
<?php } ?>
<h1 class="contentheading"><?php
	if( $this->my->id == $this->owner->id ) {
		echo JText::_( 'COM_MTREE_MY_PAGE' ) ?> (<?php echo $this->owner->name ?>)<?php
	} else {
		echo $this->owner->name;
	}
	?></h1>
<?php include $this->loadTemplate('sub_ownerProfile.tpl.php'); ?>

<ul class="nav nav-tabs" style="clear:left">
	<li class="active">
		<a href="<?php echo JRoute::_("index.php?option=com_mtree&task=viewuserslisting&user_id=".$this->owner->id."&Itemid=$this->Itemid") ?>"><?php echo JText::_( 'COM_MTREE_LISTINGS' ) ?> (<?php echo $this->pageNav->total ?>)</a>
	</li>
	<?php if($this->mtconf['show_review']) {
		?><li class="">
		<a href="<?php echo JRoute::_("index.php?option=com_mtree&task=viewusersreview&user_id=".$this->owner->id."&Itemid=$this->Itemid") ?>"><?php echo JText::_( 'COM_MTREE_REVIEWS' ) ?> (<?php echo $this->total_reviews ?>)</a>
		</li><?php } ?>
	<?php if($this->mtconf['show_favourite']) {
		?><li class="">
		<a href="<?php echo JRoute::_("index.php?option=com_mtree&task=viewusersfav&user_id=".$this->owner->id."&Itemid=$this->Itemid") ?>"><?php echo JText::_( 'COM_MTREE_FAVOURITES' ) ?> (<?php echo $this->total_favourites ?>)</a>
		</li><?php } ?>
</ul>

<div id="listings"><?php
	if (is_array($this->links) && !empty($this->links)) {

		$i = 0;
		foreach ($this->links AS $link) {
			$i++;
			$link_fields = $this->links_fields[$link->link_id];
			include $this->loadTemplate('sub_listingSummary.tpl.php');
		}

		if( $this->pageNav->total > $this->pageNav->limit ) {
			?>
			<div class="pagination">
				<p class="counter pull-right">
					<?php echo $this->pageNav->getPagesCounter(); ?>
				</p>
				<?php echo $this->pageNav->getPagesLinks(); ?>
			</div>
			<?php
		}

	} else {

		?><div class="text-center"><?php

		if( $this->my->id == $this->owner->id ) {
			echo JText::_( 'COM_MTREE_YOU_DO_NOT_HAVE_ANY_LISTINGS' );
		} else {
			echo JText::_( 'COM_MTREE_THIS_USER_DO_NOT_HAVE_ANY_LISTINGS' );
		}

		?></div><?php

	} ?></div>