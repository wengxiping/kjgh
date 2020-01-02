<?php

/*------------------------------------------------------------------------
# com_affiliatetracker - Affiliate Tracker for Joomla
# ------------------------------------------------------------------------
# author        Germinal Camps
# copyright       Copyright (C) 2014 JoomlaThat.com. All Rights Reserved.
# @license        http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites:       http://www.JoomlaThat.com
# Technical Support:  Forum - http://www.JoomlaThat.com/support
-------------------------------------------------------------------------*/

defined('_JEXEC') or die('Restricted access'); 

$params = JComponentHelper::getParams( 'com_affiliatetracker' );
$multiLevel = $params->get('multi-level', '1');

$installedPlugins = AffiliateHelper::getInstalledPlugins();
$disabledInput = "";

if(!empty($installedPlugins)) { ?>
    <fieldset class="adminform">
        <legend><?php echo JText::_( 'COMMISSION_DETAILS' ); ?></legend>
        <?php if ($this->account->id == 0) {
            $disabledInput = "disabled"; ?>
            <p class="alert alert-info"><?php echo JText::_('LEAVE_BLANK_COMMISSIONS_INFO'); ?></p>
        <?php } ?>
        <?php for ($i = 0; $i < sizeof($installedPlugins); $i++) {
            $variable_commission = AffiliateHelper::getVariableCommissionByExtensionName(json_decode($this->account->variable_comissions), $installedPlugins[$i]);
            $displayCompName = ucfirst(substr_replace($installedPlugins[$i], '', 0, 4));
            ?>
            <div class="control-group">
                <label class="control-label" for="commission_<?php echo $installedPlugins[$i]; ?>"> <?php echo JText::sprintf( 'COMMISSION_FOR', $displayCompName ); ?> </label>
                <div class="controls">
                    <input <?php echo $disabledInput; ?> class="inputbox input-mini" type="text" name="commission_<?php echo $installedPlugins[$i]; ?>" id="commission_<?php echo $installedPlugins[$i]; ?>" size="8" maxlength="250" value="<?php if(!empty($variable_commission->commission)) echo $variable_commission->commission; ?>" />
                    <select <?php echo $disabledInput; ?> name="type_<?php echo $installedPlugins[$i]; ?>" id="type" class="input-medium">
                        <?php
                        $publish = "";
                        $unpublish = "";
                        if($variable_commission->type == "percent") $publish = "selected";
                        else $unpublish = "selected";

                        ?>
                        <option <?php echo $publish;?> value="percent"><?php echo JText::_('PERCENT');?></option>
                        <option <?php echo $unpublish;?> value="flat"><?php echo JText::_('FLAT');?></option>
                    </select>
                    <?php if ($multiLevel == 1) { ?><button <?php echo $disabledInput; ?> type="button" class="btn btn-inverse" onclick="newCommissionLevel('<?php echo $installedPlugins[$i]; ?>')"><?php echo JText::_('ADD_COMMISSION_LEVEL'); ?></button><?php } ?>
                </div>
                <!-- Multi level -->
                <?php if ($multiLevel == 1) { ?>
                    <div class="controls multilevel_box" id="multilevel_box_<?php echo $installedPlugins[$i]; ?>">
                        <?php if (!empty($variable_commission->levels)) {
                            $variable_commission_numLevels = count((array)$variable_commission->levels);
                            for ($j = 0; $j < $variable_commission_numLevels; $j++) {
                                $valueToShow = strval($j + 2); ?>
                                <div class="control-group multilevel_control_group_<?php echo $installedPlugins[$i]; ?>" id="multilevel_group_<?php echo $installedPlugins[$i]; ?>_level<?php echo $j+2; ?>">
                                    <label class="control-label" for="commission_<?php echo $installedPlugins[$i]; ?>_level<?php echo $j+2; ?>"><?php echo JText::sprintf( 'COMMISSION_LEVEL_LAVEL', $j+2 ); ?></label>
                                    <div class="controls">
                                        <input class="inputbox input-mini" type="text" name="commission_<?php echo $installedPlugins[$i]; ?>_level<?php echo $j+2; ?>" id="commission_<?php echo $installedPlugins[$i]; ?>_level<?php echo $j+2; ?>" size="8" maxlength="250" value="<?php echo $variable_commission->levels->$valueToShow; ?>" />
                                    </div>
                                </div>
                            <?php } ?>
                            <input type="hidden" id="numlevels_<?php echo $installedPlugins[$i]; ?>" name="numlevels_<?php echo $installedPlugins[$i]; ?>" value="<?php echo $variable_commission_numLevels + 2; ?>" />
                        <?php } ?>
                    </div>
                <?php } ?>
                <!-- /Multi level -->
            </div>
        <?php } ?>

    </fieldset>
<?php } ?>

<?php if ($multiLevel == 1) { ?>
    <script type="text/javascript">

        var levelsObject = {};

        function newCommissionLevel(plugin) {

            if (typeof levelsObject[plugin+'_nextLevel'] === 'undefined')
                levelsObject[plugin+'_nextLevel'] = jQuery('#numlevels_'+plugin).val();
            if (typeof levelsObject[plugin+'_nextLevel'] === 'undefined') //This if is necessary if there are no levels yet
                levelsObject[plugin + '_nextLevel'] = 2;

            var html = '<div class="control-group" id="multilevel_group_'+plugin+'_level'+levelsObject[plugin+'_nextLevel']+'" data-level="'+levelsObject[plugin+'_nextLevel']+'">';
            html +=      '<label class="control-label" for="commission_'+plugin+'_level'+levelsObject[plugin+'_nextLevel']+'"><?php echo JText::_('COMMISSION_NEXTLEVEL_LAVEL'); ?></label>';
            html +=      '<div class="controls">';
            html +=        '<input class="inputbox input-mini" type="text" name="commission_'+plugin+'_level'+levelsObject[plugin+'_nextLevel']+'" id="commission_'+plugin+'_level'+levelsObject[plugin+'_nextLevel']+'" size="8" maxlength="250" value="" />';
            html +=      '</div>';
            html +=    '</div>';

            jQuery('#multilevel_box_'+plugin).append(html);

            ++levelsObject[plugin+'_nextLevel'];

        }

    </script>
<?php } ?>
