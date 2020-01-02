<div class="mt-page-index-style-<?php echo $this->config->getTemParam('indexStyle',4); ?>">
<?php
$numOfColumns = $this->config->getTemParam('numOfColumns',2);
$displayIndexListingCount = $this->config->getTemParam('displayIndexListingCount',1);
$displayIndexCatCount = $this->config->getTemParam('displayIndexCatCount',0);
$numOfSubcatsToDisplay = $this->config->getTemParam('numOfSubcatsToDisplay',3);
$itemsSeparatorString = ', ';

#
# Load category#-header-id# modules
#

$document	= JFactory::getDocument();
$renderer	= $document->loadRenderer('module');
$contents	= '';

$modules = JModuleHelper::getModules('category-header-id'.$this->cat_id);
if( !empty($modules) )
{
	$contents	.= '<div class="columns1-modules-inner">';
	foreach ($modules as $mod)  {
		$params = new JRegistry( $mod->params );
		$contents .= '<div class="module'.$params->get('moduleclass_sfx').'">';
		if ($mod->showtitle)
		{
			$contents .= '<h3>' . $mod->title . '</h3>';
			$contents .= '<div class="triangle"></div>';
		}
		$contents .= $renderer->render($mod);
		$contents .= '</div>';
	}
	$contents	.= '</div>';
}

$modules = JModuleHelper::getModules('category2-header-id'.$this->cat_id);
if( !empty($modules) )
{
	$contents	.= '<div class="columns2-modules-inner">';
	foreach ($modules as $mod)  {
		$params = new JRegistry( $mod->params );
		$contents .= '<div class="module'.$params->get('moduleclass_sfx').'">';
		if ($mod->showtitle)
		{
			$contents .= '<h3>' . $mod->title . '</h3>';
			$contents .= '<div class="triangle"></div>';
		}
		$contents .= $renderer->render($mod);
		$contents .= '</div>';
	}
	$contents	.= '</div>';
}

$modules = JModuleHelper::getModules('category3-header-id'.$this->cat_id);
if( !empty($modules) )
{
	$contents	.= '<div class="columns3-modules-inner">';
	foreach ($modules as $mod)  {
		$params = new JRegistry( $mod->params );
		$contents .= '<div class="module'.$params->get('moduleclass_sfx').'">';
		if ($mod->showtitle)
		{
			$contents .= '<h3>' . $mod->title . '</h3>';
			$contents .= '<div class="triangle"></div>';
		}
		$contents .= $renderer->render($mod);
		$contents .= '</div>';
	}
	$contents	.= '</div>';
}

if( !empty($contents) )
{
	echo '<div class="index-modules">' . $contents . '</div>';
}
?>
<?php
	include $this->loadTemplate('page_indexStyle'.$this->config->getTemParam('indexStyle',4).'.tpl.php');
?>
</div>
<?php

if( $this->display_listings_in_root && $this->cat_show_listings ) include $this->loadTemplate( 'sub_listings.tpl.php' ) ;

#
# Load category#-footer-id# modules
#

$document	= JFactory::getDocument();
$renderer	= $document->loadRenderer('module');

$contents	= '';

$modules = JModuleHelper::getModules('category-footer-id'.$this->cat_id);
if( !empty($modules) )
{
	$contents	.= '<div class="columns1-modules-inner row-fluid">';
	foreach ($modules as $mod)  {
		$params = new JRegistry( $mod->params );
		$contents .= '<div class="span12 module'.$params->get('moduleclass_sfx').'">';
		if ($mod->showtitle)
		{
			$contents .= '<h3>' . $mod->title . '</h3>';
			$contents .= '<div class="triangle"></div>';
		}
		$contents .= $renderer->render($mod);
		$contents .= '</div>';
	}
	$contents	.= '</div>';
}

$modules = JModuleHelper::getModules('category2-footer-id'.$this->cat_id);
if( !empty($modules) )
{
	$contents	.= '<div class="columns2-modules-inner row-fluid">';
	foreach ($modules as $mod)  {
		$params = new JRegistry( $mod->params );
		$contents .= '<div class="span6 module'.$params->get('moduleclass_sfx').'">';
		if ($mod->showtitle)
		{
			$contents .= '<h3>' . $mod->title . '</h3>';
			$contents .= '<div class="triangle"></div>';
		}
		$contents .= $renderer->render($mod);
		$contents .= '</div>';
	}
	$contents	.= '</div>';
}

$modules = JModuleHelper::getModules('category3-footer-id'.$this->cat_id);
if( !empty($modules) )
{
	$contents	.= '<div class="columns3-modules-inner row-fluid">';
	foreach ($modules as $mod)  {
		$params = new JRegistry( $mod->params );
		$contents .= '<div class="span4 module'.$params->get('moduleclass_sfx').'">';
		if ($mod->showtitle)
		{
			$contents .= '<h3>' . $mod->title . '</h3>';
			$contents .= '<div class="triangle"></div>';
		}
		$contents .= $renderer->render($mod);
		$contents .= '</div>';
	}
	$contents	.= '</div>';
}

if( !empty($contents) ) {
	echo '<div class="index-footer-modules">' . $contents . '</div>';
}
