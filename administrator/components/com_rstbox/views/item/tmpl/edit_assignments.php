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

use NRFramework\Extension;

?>

<div class="assignments">
    <?php 
        echo $this->form->renderFieldSet('mirror');
    ?>

    <div data-showon='[{"field":"jform[mirror]","values":["0"],"sign":"=","op":""}]'>
        <?php echo $this->form->renderField('assignmentMatchingMethod') ?>
        
        <!-- Page -->
        <div class="assign-group">
            <label><?php echo JText::_('NR_ASSIGN_GROUP_PAGE_URL')?></label>
            <p><?php echo JText::_('NR_ASSIGN_GROUP_PAGE_URL_DESC')?></p>
            <div class="assign"><?php echo $this->form->renderFieldset('menu') ?></div>
            <div class="assign"><?php echo $this->form->renderFieldset('url') ?></div>
        </div>
        
        <!-- Date Time -->
        <div class="assign-group">
            <label><?php echo JText::_('NR_DATETIME') ?></label>
            <p><?php echo JText::_('NR_ASSIGN_GROUP_DATETIME_DESC') ?></p>
            <div class="assign"><?php echo $this->form->renderFieldset('datetime') ?></div>
            <div class="assign"><?php echo $this->form->renderFieldset('timerange') ?></div>
        </div>
        
        <!-- Joomla User -->
        <div class="assign-group">
            <label><?php echo JText::_('NR_ASSIGN_GROUP_USER_VISITOR') ?></label> 
            <p><?php echo JText::_('NR_ASSIGN_GROUP_USER_VISITOR_DESC') ?></p>
            <div class="assign"><?php echo $this->form->renderFieldset('usergroup') ?></div>
            <div class="assign"><?php echo $this->form->renderFieldset('userid') ?></div>
            <div class="assign"><?php echo $this->form->renderFieldset('pageviews') ?></div>
            <div class="assign"><?php echo $this->form->renderFieldset('onotherbox') ?></div>
            <div class="assign"><?php echo $this->form->renderFieldset('timeonsite') ?></div>
        </div>

        <!-- Visitor Platform -->
        <div class="assign-group">
            <label><?php echo JText::_('NR_ASSIGN_GROUP_PLATFORM') ?></label>
            <p><?php echo JText::_('NR_ASSIGN_GROUP_PLATFORM_DESC') ?></p>
            <div class="assign"><?php echo $this->form->renderFieldset('device') ?></div>
            <div class="assign"><?php echo $this->form->renderFieldset('browser') ?></div>
            <div class="assign"><?php echo $this->form->renderFieldset('os') ?></div>
        </div>

        <!-- Geolocating -->
        <?php if (Extension::pluginIsEnabled('tgeoip')) { ?>
        <div class="assign-group">
            <label><?php echo JText::_("NR_GEOLOCATING") ?></label>
            <p><?php echo JText::_('NR_ASSIGN_GROUP_GEO_DESC') ?></p>
            <div class="assign"><?php echo $this->form->renderFieldset('country') ?></div>
            <div class="assign"><?php echo $this->form->renderFieldset('continent') ?></div>
        </div>
        <?php } ?>

        <!-- Joomla! Articles -->
        <div class="assign-group">
            <label><?php echo JText::_('NR_ASSIGN_GROUP_JCONTENT') ?></label>
            <p><?php echo JText::_('NR_ASSIGN_GROUP_JCONTENT_DESC') ?></p>
            <div class="assign"><?php echo $this->form->renderFieldset('article') ?></div>
            <div class="assign"><?php echo $this->form->renderFieldset('articlecategories') ?></div>
        </div>

        <!-- K2 -->
        <?php if (Extension::isInstalled('k2')) { ?>
        <div class="assign-group">
            <label><?php echo JText::_('NR_ASSIGN_K2') ?></label>
            <p><?php echo JText::_('NR_ASSIGN_K2_DESC') ?></p>
            <div class="assign"><?php echo $this->form->renderFieldset('k2_items') ?></div>
            <div class="assign"><?php echo $this->form->renderFieldset('k2_cats') ?></div>
            <div class="assign"><?php echo $this->form->renderFieldset('k2_tags') ?></div>
            <div class="assign"><?php echo $this->form->renderFieldset('k2_pagetypes') ?></div>
        </div>
        <?php } ?>

        <!-- 3rd Party Components Integrations -->
        <div class="assign-group">
            <label><?php echo JText::_('NR_ASSIGN_GROUP_SYSTEM') ?></label>
            <p><?php echo JText::_('NR_ASSIGN_GROUP_SYSTEM_DESC') ?></p>
            <div class="assign"><?php echo $this->form->renderFieldset('components') ?></div>
            <div class="assign"><?php echo $this->form->renderFieldset('languages') ?></div>

            <?php if (Extension::isInstalled('akeebasubs')) { ?>
                <div class="assign"><?php echo $this->form->renderFieldset('akeebasubs') ?></div>
            <?php } ?>
            <?php if (Extension::isInstalled('convertforms') && Extension::pluginIsEnabled('convertforms')) { ?>
                <div class="assign"><?php echo $this->form->renderFieldset('convertforms') ?></div>
            <?php } ?>
            <?php if (Extension::isInstalled('acymailing')) { ?>
                <div class="assign"><?php echo $this->form->renderFieldset('acymailing') ?></div>
            <?php } ?>
        </div>

        <!-- Advanced Targeting -->
        <div class="assign-group">
            <label><?php echo JText::_('NR_ADVANCED') ?></label>
            <p><?php echo JText::_('NR_ASSIGN_GROUP_ADVANCED') ?></p>
            <div class="assign"><?php echo $this->form->renderFieldset('referrer') ?></div>
            <div class="assign"><?php echo $this->form->renderFieldset('ipaddress') ?></div>
            <div class="assign"><?php echo $this->form->renderFieldset('cookie') ?></div>
            <div class="assign"><?php echo $this->form->renderFieldset('php') ?></div>
        </div>  

    </div>
</div>