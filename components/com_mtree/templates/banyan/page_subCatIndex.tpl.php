<div class="mt-page-category-style-<?php echo $this->config->getTemParam('categoryStyle',2); ?>">

	<?php
	#
	# Load category#-header-id# modules
	#

	$document	= JFactory::getDocument();
	$renderer	= $document->loadRenderer('module');

	$contents	= '';

	$modules = JModuleHelper::getModules('category-header-id'.$this->cat_id);
	if( !empty($modules) )
	{
		$contents	.= '<div class="category-header-inner row-fluid">';
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

	$modules = JModuleHelper::getModules('category2-header-id'.$this->cat_id);
	if( !empty($modules) )
	{
		$contents	.= '<div class="category2-header-inner row-fluid">';
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

	$modules = JModuleHelper::getModules('category3-header-id'.$this->cat_id);
	if( !empty($modules) )
	{
		$contents	.= '<div class="category3-header-inner row-fluid">';
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

	if( !empty($contents) )
	{
		echo '<div class="category-header-modules">' . $contents . '</div>';
	}

	include $this->loadTemplate('page_subCatIndexStyle'.$this->config->getTemParam('categoryStyle',2).'.tpl.php');

	if( $this->cat_show_listings ) include $this->loadTemplate( 'sub_listings.tpl.php' );

	#
	# Load category#-footer-id# modules
	#

	$document	= JFactory::getDocument();
	$renderer	= $document->loadRenderer('module');

	$contents	= '';

	$modules = JModuleHelper::getModules('category-footer-id'.$this->cat_id);
	if( !empty($modules) )
	{
		$contents	.= '<div class="category-footer-inner row-fluid">';
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
		$contents	.= '<div class="category2-footer-inner row-fluid">';
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
		$contents	.= '<div class="category3-footer-inner row-fluid">';
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

	if( !empty($contents) )
	{
		echo '<div class="category-header-modules">' . $contents . '</div>';
	}
	?>
</div>