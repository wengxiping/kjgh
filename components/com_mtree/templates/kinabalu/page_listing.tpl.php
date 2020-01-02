<div class="page-listing" itemscope itemtype="http://schema.org/<?php echo $this->config->get('schema_type'); ?>">
<?php

if( $this->config->getTemParam('listingDetailsStyle',1) == 1 )
{
	include $this->loadTemplate( 'sub_listingDetails.tpl.php' );
}
else
{
	include $this->loadTemplate( 'sub_listingDetailsStyle'.$this->config->getTemParam('listingDetailsStyle',1).'.tpl.php' );
}

if ($this->config->get('use_map')) include $this->loadTemplate( 'sub_map.tpl.php' );

if ($this->config->get('show_review')) include $this->loadTemplate( 'sub_reviews.tpl.php' );

if ($this->mtconf['show_previous_next_listing_in_listing_details'])
{
	?>
	<div class="navigate-adjacent-listing">
		<a href="<?php echo JRoute::_('index.php?option=com_mtree&task=adjacentListing&direction=-1&link_id=' . $this->link->link_id . '&Itemid=' . $this->Itemid); ?>">
			<?php echo MText::sprintf('PREVIOUS_LISTING_IN_CATEGORY', $this->tlcat_id, $this->link->cat_name); ?>
		</a>
		|
		<a href="<?php echo JRoute::_('index.php?option=com_mtree&task=adjacentListing&direction=1&link_id=' . $this->link->link_id . '&Itemid=' . $this->Itemid); ?>">
			<?php echo MText::sprintf('NEXT_LISTING_IN_CATEGORY', $this->tlcat_id, $this->link->cat_name); ?>
		</a>
	</div>
	<?php
}

if (isset($this->links)) include $this->loadTemplate( 'sub_listings.tpl.php' );

#
# Load listing#-footer-modules Modules
#

$document	= JFactory::getDocument();
$renderer	= $document->loadRenderer('module');

$contents	= '';

$modules = array_merge(
	JModuleHelper::getModules('listing-footer'),
	JModuleHelper::getModules('listing-footer-id'.$this->link->link_id)
	);

if( !empty($modules) )
{
	$contents	.= '<div class="columns1-modules-inner">';
	foreach ($modules as $mod)  {
		$params = new JRegistry( $mod->params );
		$contents .= '<div class="module'.$params->get('moduleclass_sfx').'">';
		$contents .= '<h3>' . $mod->title . '</h3>';
		$contents .= '<div class="triangle"></div>';
		$contents .= $renderer->render($mod);
		$contents .= '</div>';
	}
	$contents	.= '</div>';
}

$modules = array_merge(
		JModuleHelper::getModules('listing2-footer'),
		JModuleHelper::getModules('listing2-footer-id'.$this->link->link_id)
);

if( !empty($modules) )
{
	$contents	.= '<div class="columns2-modules-inner">';
	foreach ($modules as $mod)  {
		$params = new JRegistry( $mod->params );
		$contents .= '<div class="module'.$params->get('moduleclass_sfx').'">';
		$contents .= '<h3>' . $mod->title . '</h3>';
		$contents .= '<div class="triangle"></div>';
		$contents .= $renderer->render($mod);
		$contents .= '</div>';
	}
	$contents	.= '</div>';
}

$modules = array_merge(
		JModuleHelper::getModules('listing3-footer'),
		JModuleHelper::getModules('listing3-footer-id'.$this->link->link_id)
);

if( !empty($modules) )
{
	$contents	.= '<div class="columns3-modules-inner">';
	foreach ($modules as $mod)  {
		$params = new JRegistry( $mod->params );
		$contents .= '<div class="module'.$params->get('moduleclass_sfx').'">';
		$contents .= '<h3>' . $mod->title . '</h3>';
		$contents .= '<div class="triangle"></div>';
		$contents .= $renderer->render($mod);
		$contents .= '</div>';
	}
	$contents	.= '</div>';
}

if( !empty($contents) )
{
	echo '<div class="listing-footer-modules">' . $contents . '</div>';
}

?>
</div>