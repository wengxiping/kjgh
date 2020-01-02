<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-navbar__search <?php echo $this->isMobile() ? 't-hidden' : '';?>" data-toolbar-search>
    <form action="<?php echo JRoute::_('index.php');?>" method="post" class="es-navbar__search-form">
        
        <input type="text" name="q" class="es-navbar__search-input" autocomplete="off" data-nav-search-input placeholder="<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_SEARCH', true);?>" />

        <?php if ($filters) { ?>
        <div class="es-navbar__search-filter dropdown" data-filters>
            
            <a href="javascript:void(0);" class="dropdown-toggle_" data-bs-toggle="dropdown" data-filter-button>
                <i class="fa fa-cog es-navbar__search-filter-icon"></i>
            </a>

            <ul class="dropdown-menu dropdown-menu-right es-navbar__search-dropdown" data-filters-wrapper>
                <li>
                    <div class="es-navbar__search-filter-header">
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
                <li class="es-navbar__search-filter-item">
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

        <?php echo $this->html('form.itemid', ESR::getItemId('search')); ?>
        <input type="hidden" name="controller" value="search" />
        <input type="hidden" name="task" value="query" />
        <input type="hidden" name="option" value="com_easysocial" />
        <input type="hidden" name="<?php echo FD::token();?>" value="1" />
    </form>
</div>