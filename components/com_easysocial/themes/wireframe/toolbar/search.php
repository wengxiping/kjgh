<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-toolbar__item es-toolbar__item--search" data-toolbar-search>
	<div id="es-toolbar-search" class="es-toolbar__search">
		<form action="<?php echo JRoute::_('index.php');?>" method="post" class="es-toolbar__search-form">
			<?php if ($filters) { ?>
			<div class="es-toolbar__search-filter dropdown" data-filters>

				<a href="javascript:void(0);" class="btn dropdown-toggle_ es-toolbar__search-filter-toggle" data-bs-toggle="dropdown" data-filter-button>
					<i class="fa fa-filter es-toolbar__search-filter-icon"></i>
					<span class="es-toolbar__search-filter-txt">&nbsp;  <i class="fa fa-caret-down"></i></span>
				</a>

				<ul class="dropdown-menu es-toolbar__search-dropdown" data-filters-wrapper>
					<li>
						<div class="es-toolbar__search-filter-header">
							<div><?php echo JText::_('COM_EASYSOCIAL_SEARCH_FILTER_DESC');?></div>
						</div>

						<ol class="g-list-inline g-list-inline--delimited">
							<li>
								<a href="javascript:void(0);" data-filter="select"><?php echo JText::_('COM_EASYSOCIAL_SEARCH_FILTER_SELECT_ALL'); ?></a>
							</li>
							<li data-breadcrumb="|">
								<a href="javascript:void(0);" data-filter="deselect"><?php echo JText::_('COM_EASYSOCIAL_SEARCH_FILTER_DESELECT_ALL'); ?></a>
							</li>
						</ol>
					</li>

					<?php foreach ($filters as $filter) { ?>
					<li class="es-toolbar__search-filter-item">
						<div class="o-checkbox">
							<input id="search-type-<?php echo $filter->id;?>" type="checkbox" name="filtertypes[]" value="<?php echo $filter->alias; ?>" data-search-filtertypes />
							<label for="search-type-<?php echo $filter->id;?>">
								<?php echo $filter->displayTitle;?>
							</label>
						</div>
					</li>
					<?php } ?>
				</ul>
			</div>
			<?php } ?>

			<input type="text" name="q" class="es-toolbar__search-input" autocomplete="off" data-nav-search-input placeholder="<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_SEARCH', true);?>" />

			<div class="es-toolbar__search-submit-btn">
				<button class="btn btn-es-primary btn-toolbar-search" type="submit"><?php echo JText::_('COM_EASYSOCIAL_SEARCH');?></button>
			</div>
			<div class="es-toolbar__search-close-btn">
				<a href="javascript:void(0);" class="" data-es-toolbar-search-toggle><i class="fa fa-times"></i></a>
			</div>

			<?php echo $this->html('form.action', 'search', 'query'); ?>
			<?php echo $this->html('form.itemid', ESR::getItemId('search')); ?>
		</form>
	</div>
</div>
