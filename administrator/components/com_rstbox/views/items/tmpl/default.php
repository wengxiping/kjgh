<?php

/**
 * @package         EngageBox
 * @version         3.5.2 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2019 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$showcolors = $this->config->get("colorgroup", true);
$user       = JFactory::getUser();

?>

<div class="j-main-container">
    <form action="<?php echo JRoute::_('index.php?option=com_rstbox&view=items'); ?>" method="post" name="adminForm" id="adminForm">
        <?php
            echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
        ?>

        <table class="adminlist table table-striped">
            <thead>
                <tr>
                    <th class="center" width="2%">
                        <?php echo JHtml::_('grid.checkall'); ?>
                    </th>
                    <th width="1%" class="nowrap hidden-phone" align="center">
                        <?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
                    </th>
                    <?php if ($showcolors) { ?>
                        <th width="1%"></th>
                    <?php } ?>
                    <th>
                        <?php echo JHtml::_('searchtools.sort', 'NR_NAME', 'a.name', $listDirn, $listOrder); ?>
                    </th>
                    <th width="15%">
                        <?php echo JText::_('COM_RSTBOX_BOX_MODE') ?>
                    </th>
                    <th width="15%">
                         <?php echo JHtml::_('searchtools.sort', 'COM_RSTBOX_ITEM_FIELD_TYPE', 'a.boxtype', $listDirn, $listOrder); ?>
                    </th>
                    <th width="15%">
                        <?php echo JHtml::_('searchtools.sort', 'COM_RSTBOX_ITEM_TRIGGER', 'a.triggermethod', $listDirn, $listOrder); ?>
                    </th>
                    <th width="15%">
                        <?php echo JHtml::_('searchtools.sort', 'COM_RSTBOX_ASSIGN_IMPRESSIONS', 'impressions', $listDirn, $listOrder); ?>
                    </th>
                    <th width="5%">
                        <?php echo JHtml::_('searchtools.sort', 'COM_RSTBOX_ID', 'a.id', $listDirn, $listOrder); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    // The first check prevents the PHP Warning: count(): Parameter must be an array or an object caused in PHP 7.2
                    if (is_array($this->items) && count($this->items)) { ?>
                    <?php foreach($this->items as $i => $item): ?>
                        <?php 
                            $canChange  = $user->authorise('core.edit.state', 'com_rstbox.item.' . $item->id);
                        ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td class="center"><?php echo JHtml::_('grid.id', $i, $item->id); ?></td>
                            <td class="center">
                                <div class="btn-group">
                                    <?php echo JHtml::_('jgrid.published', $item->published, $i, 'items.', $canChange); ?>

                                    <?php
                                    if ($canChange && !defined('nrJ4'))
                                    {
                                        JHtml::_('actionsdropdown.' . ((int) $item->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'items');
                                        JHtml::_('actionsdropdown.' . 'duplicate', 'cb' . $i, 'items');
                                               
                                        echo JHtml::_('actionsdropdown.render', $this->escape($item->name));
                                    }
                                    ?>
                                </div>
                            </td>
                            <?php if ($showcolors) : ?>
                                <td class="center inlist">
                                    <?php $color = isset($item->params->colorgroup) ? $item->params->colorgroup : ""; ?>
                                    <span class="boxColor">
                                        <span style="background-color: <?php echo $color ?>;"></span>
                                    </span>
                                </td>
                            <?php endif; ?>
                            <td>
                                <a href="<?php echo JRoute::_('index.php?option=com_rstbox&task=item.edit&id='.$item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>"><?php echo ucfirst($this->escape($item->name)); ?>
                                </a>
                                <?php if (EBHelper::boxHasCookie($item->id)) { ?>
                                    <span class="label label-important hasTooltip" title="<?php echo JText::_("COM_RSTBOX_HIDDEN_BY_COOKIE_DESC") ?>">
                                        <?php echo JText::_("COM_RSTBOX_HIDDEN_BY_COOKIE") ?>
                                    </span>     
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-default dropdown-toggle btn-mini" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="javascript://" onclick="listItemTask('cb<?php echo $i; ?>', 'items.removeCookie')">
                                                    <span class="icon-trash"></span> <?php echo JText::_("COM_RSTBOX_REMOVE_COOKIE") ?>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                <?php } ?>
                                <?php if ($item->testmode) { ?>
                                    <span class="label hasTooltip" title="<?php echo JTEXT::_("COM_RSTBOX_ITEM_TESTMODE_DESC") ?>">
                                        <?php echo JText::_("COM_RSTBOX_ITEM_TESTMODE") ?>
                                    </span>
                                <?php } ?>

                                <?php if ($mirror = (isset($item->params->mirror) && $item->params->mirror && $item->params->mirror_box) ? $item->params->mirror_box : false) { ?>
                                    <span class="label label-warning">
                                        <?php echo JText::sprintf('COM_RSTBOX_MIRRORING_BOX', $mirror) ?>
                                    </span>
                                <?php } ?>

                                <div class="small"><?php echo (isset($item->params->note)) ? $item->params->note : "" ?></div>
                            </td>
                            <td>
                                <?php 
                                    $mode = isset($item->params->mode) ? $item->params->mode : 'popup';
                                    echo JText::_('COM_RSTBOX_' . $mode);
                                ?>
                            </td>
                            <td><?php echo ucfirst($item->boxtype) ?></td>
                            <td>
                                <?php echo ucfirst($item->triggermethod) ?> /
                                <?php echo ucfirst($item->position) ?>
                            </td>
                            <td>
                                <?php if ($item->impressions > 0 && !defined('nrJ4')) { ?>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-default dropdown-toggle btn-mini" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="javascript://" onclick="listItemTask('cb<?php echo $i; ?>', 'items.reset')">
                                                <span class="icon-refresh"></span>
                                                <?php echo JText::_("COM_RSTBOX_RESET_STATISTICS") ?>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <?php } ?>

                                <span class="badge <?php echo $item->impressions > 0 ? 'badge-info' : 'badge-secondary' ?> hasTooltip" title="<?php echo JText::sprintf("COM_RSTBOX_TOTAL_IMPRESSIONS", $item->impressions); ?>">
                                    <?php echo $item->impressions; ?>
                                </span>
                            </td>
                            <td><?php echo $item->id ?></td>
                        </tr>
                    <?php endforeach; ?>  
                <?php } else { ?>
                    <tr>
                        <td align="center" colspan="8">
                            <div align="center"><?php echo JText::_('COM_RSTBOX_ERROR_NO_BOXES') ?></div>
                        </td>
                    </tr>
                <?php } ?>        
            </tbody>
            <tfoot>
    			<tr><td colspan="8"><?php echo $this->pagination->getListFooter(); ?></td></tr>
            </tfoot>
        </table>
        <div>
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="boxchecked" value="0" />
            <?php echo JHtml::_('form.token'); ?>
        </div>
    </form>
    <?php include_once(JPATH_COMPONENT_ADMINISTRATOR . '/layouts/footer.php'); ?>
</div>