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
JHtml::_('behavior.formvalidation');

if (!defined('nrJ4'))
{
    JHtml::_('formbehavior.chosen', 'select');
} else {
    JHtml::_('formbehavior.chosen', '.hasChosen');
}

$smartTagsModal =  array(
    'url'        => JURI::base() . 'index.php?option=com_rstbox&view=item&layout=smarttags&tmpl=component',
    'title'      => JText::_('NR_SMARTTAGS'),
    'width'      => '800px',
    'height'     => '300px',
    'modalWidth' => '80',
    'bodyHeight' => '60',
    'footer'     => '<a type="button" class="btn btn-secondary" data-dismiss="modal" aria-hidden="true">'. JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>'
);

echo JHtml::_('bootstrap.renderModal','smarttags', $smartTagsModal);

?>

<!-- Test Mode Notice -->
<?php if ($this->item->testmode) { ?>
	<div class="alert alert-warning text-center">
		<?php echo JText::_('COM_RSTBOX_ITEM_TESTMODE_NOTICE'); ?>
	</div>
<?php } ?>

<div class="rstbox rstbox-item form-horizontal">
    <form action="<?php echo JRoute::_('index.php?option=com_rstbox&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
        <div class="rowTop">
            <?php echo $this->form->renderFieldset("top") ?>
        </div>
        <div class="<?php echo defined('nrJ4') ? 'row' : 'row-fluid' ?>">
            <div class="span9 col-md-9">
                <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

                <!-- Content Tab -->
                <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_RSTBOX_CONTENT')); ?>
                <div class="boxtype">
                    <?php echo $this->form->renderFieldset($this->form->getData()->get('boxtype')); ?>
                </div>
                <?php echo JHtml::_('bootstrap.endTab'); ?>
                
                <!-- Trigger Tab -->
                <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'trigger', JText::_('COM_RSTBOX_TRIGGER')); ?>
                <div class="<?php echo defined('nrJ4') ? 'row' : 'row-fluid' ?>">
                    <div class="span6 col-md-6"><?php echo $this->form->renderFieldset("item1") ?></div>
                    <div class="span6 col-md-6"><?php echo $this->form->renderFieldset("item2") ?></div>
                </div>
                <?php echo JHtml::_('bootstrap.endTab'); ?>

                <!-- Appearance Tab -->
                <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'appearance', JText::_('COM_RSTBOX_APPEARANCE')); ?>
                <div class="<?php echo defined('nrJ4') ? 'row' : 'row-fluid' ?>">
                    <div class="span6 col-md-6"><?php echo $this->form->renderFieldset("appearance1") ?></div>
                    <div class="span6 col-md-6"><?php echo $this->form->renderFieldset("appearance2") ?></div>
                </div>
                <?php echo JHtml::_('bootstrap.endTab'); ?>

                <!-- Publishing Assignments Tab -->
                <?php 
                    echo JHtml::_('bootstrap.addTab', 'myTab', 'publishingAssignments', JText::_('NR_PUBLISHING_ASSIGNMENTS')); 
                    echo $this->loadTemplate('assignments');
                    echo JHtml::_('bootstrap.endTab');
                ?>

                <!-- Advanced Tab -->
                <?php
                    echo JHtml::_('bootstrap.addTab', 'myTab', 'advanced', JText::_('NR_ADVANCED')); 
                    echo $this->form->renderFieldset("advanced");
                    echo JHtml::_('bootstrap.endTab');
                ?>

                <input type="hidden" name="task" value="item.edit" />
                <?php echo JHtml::_('form.token'); ?>
                <?php echo JHtml::_('bootstrap.endTabSet'); ?>
            </div>

            <div class="span3 col-md-3 card form-vertical form-no-margin paddingLeft">
                <div class="card-body">
                    <h4>Details</h4>
                    <hr>
                    <?php echo $this->form->renderFieldset("general") ?>
                </div>
            </div>
        </div>
    </form>
</div>