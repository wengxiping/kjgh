<?php
/**
 * @company        :    BriTech Solutions
 * @created by    :    JoomBri Team
 * @contact        :    www.joombri.in, support@joombri.in
 * @created on    :    02 April 2012
 * @file name    :    views/membership/tmpl/escrow.php
 * @copyright   :    Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :    GNU General Public License version 2 or later
 * @author      :    Faisel
 * @description    :    Escrow Payment Form (jblance)
 */
defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');
JHtml::_('behavior.formvalidator');

$doc = JFactory::getDocument();
$doc->addScript("components/com_jblance/js/utility.js");
$doc->addStyleSheet("components/com_jblance/css/customer/escrow.css");

$user = JFactory::getUser();
$config = JblanceHelper::getConfig();
$currencysym = $config->currencySymbol;

$totalFund = JblanceHelper::getTotalFund($user->id);

JblanceHelper::setJoomBriToken();
?>
<script type="text/javascript">
    <!--
    function validateForm(f) {
        var valid = document.formvalidator.isValid(f);

        if (valid == true) {

        } else {
            var msg = '<?php echo JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY', true); ?>';
            if ($("amount").hasClass("invalid")) {
                msg = msg + '\n\n* ' + '<?php echo JText::_('COM_JBLANCE_PLEASE_ENTER_AMOUNT_IN_NUMERIC_ONLY', true); ?>';
            }
            alert(msg);
            return false;
        }
        return true;
    }

    jQuery(document).ready(function ($) {
        jQuery("#pay_for").on("keyup", function (e) {
            var pay_for = jQuery("#pay_for").val();
            var amt = parseFloat(jQuery("#bid_amount").val() * jQuery("#pay_for").val());
            jQuery("#amount").val(amt);
        });

        jQuery("input[name='reason']").on("click", updateReason);
    });

    function updateReason() {
        if (jQuery("#full_payment_option:checked").length || jQuery("#partial_payment_option:checked").length) {
            jQuery("#projectBox").css("display", "block");
            jQuery("#project_id").addClass("required").prop("required", "required");
        } else if (jQuery("#other_reason_option:checked").length) {
            jQuery("#projectBox").css("display", "none");
            if (jQuery("#project_id"))
                jQuery("#project_id").removeClass("required").removeProp("required").val("");
            jQuery("#pay_for").removeClass("required").removeProp("required");
            jQuery("#div_pay_for").css("display", "none");
        }
    }

    function checkCause(object) {

        console.log(jQuery(object).find("input[type=checkbox]").is(":checked"));

        if (jQuery(object).find("input[type=checkbox]").is(":checked")) {
            jQuery(object).find("input[type=checkbox]").attr('checked', false);
            jQuery(object).find(".select-img").removeClass('select-img').addClass('default-img');
        } else {
            jQuery(object).find("input[type=checkbox]").attr('checked', true);
            jQuery(object).find(".default-img").removeClass('default-img').addClass('select-img');
        }
    }

    //-->
</script>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userFormProject" id="userFormProject"
      class="form-validate form-horizontal" onsubmit="return validateForm(this);">
    <div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_ESCROW_PAYMENT'); ?></div>
    <p class='select-reason-p'><?php echo JText::_('COM_JBLANCE_PLEASE_SELECT_ONE_OF_THE_FOLLOWING'); ?>:</p>
    <div class="row" style='margin-left: 150px;'>
        <div class="span6">
            <div class="xp-checkbox-label" onclick="checkCause(this)">
                <div class="select-img"></div>
                <input type="checkbox" name="reason" id="full_payment_option" value="full_payment" checked/>
                <div class="txt"><?php echo JText::_('COM_JBLANCE_FULL_FINAL_PAYMENT_FOR_COMPLETED_PROJECT'); ?></div>
            </div>
            <div class="xp-checkbox-label" onclick="checkCause(this)">
                <div class="default-img"></div>
                <input type="checkbox" name="reason" id="partial_payment_option" value="partial_payment"/>
                <div class="txt"> <?php echo JText::_('COM_JBLANCE_PARTIAL_PAYMENT_FOR_PROJECT'); ?></div>
            </div>
            <div class="xp-checkbox-label" onclick="checkCause(this)">
                <div class="default-img"></div>
                <input type="checkbox" name="reason" id="other_reason_option" value="other"/>
                <div class="txt"><?php echo JText::_('COM_JBLANCE_OTHER_REASON'); ?></div>
            </div>
        </div>
    </div>
    <!-- 设计稿 没有这跟线 暂时隐藏 -->
    <div class="lineseparator" style='display:none'></div>

    <div class="control-group" id="projectBox" style="margin-top: 20px;">
        <!--		<label class="control-label left-label-title" for="project_id">-->
        <?php //echo JText::_('COM_JBLANCE_PROJECT'); ?><!-- :</label>-->
        <div style="margin-top: -5px;" class="control-label left-label-title"
             for="project_id"><?php echo JText::_('COM_JBLANCE_PROJECT'); ?> :
        </div>
        <div class="controls">
            <?php echo $this->lists; ?>
            <input type="hidden" placeholder='请填写' name="proj_balance" id="proj_balance" value=""/>
            <strong><span id="proj_balance_div" class="help-inline"></span></strong>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label left-label-title" for="recipient"><?php echo JText::_('COM_JBLANCE_USERNAME'); ?>
            :</label>
        <div class="controls">
            <input type="text" name="recipient" placeholder='请填写' id="recipient" value=""
                   class="input-medium required user-name-input" onchange="checkUsername(this);"/>
            <span id="status_recipient" class="help-inline"></span>
        </div>
    </div>
    <div class="control-group" id="div_pay_for" style="display: none;">
        <label class="control-label left-label-title" for="pay_for"><?php echo JText::_('COM_JBLANCE_PAY_FOR'); ?>
            :</label>
        <div class="controls">
            <div class="input-append">
                <input type="text" placeholder='请填写' style='width:206px;height:36px' name="pay_for" id="pay_for"
                       class="input-small required validate-numeric"/>
                <span class="add-on"><?php echo JText::_('COM_JBLANCE_HOURS'); ?></span>
            </div>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="amount"><?php echo JText::_('COM_JBLANCE_AMOUNT'); ?> :</label>
        <div class="controls">
            <div class="input-prepend">
                <input type="text" name="amount" id="amount" class="input-small required validate-numeric" value=""
                       placeholder='请填写'/>
                <!--设计稿没有，暂时隐藏 -->
                <span class="add-on" style='display:none'><?php echo $currencysym; ?></span>
                <input type="hidden" name="bid_amount" id="bid_amount" value=""/>
            </div>
            <span class='money-unit'>/元</span>
        </div>
        <p class="account-balance">
            <span><?php echo JText::_('COM_JBLANCE_YOUR_BALANCE') . ' : ' . JblanceHelper::formatCurrency($totalFund); ?></span>
        </p>
    </div>
    <div class="control-group">
        <label class="control-label left-label-title" for="note"><?php echo JText::_('COM_JBLANCE_NOTES'); ?> :</label>
        <div class="controls">
            <textarea style='width:500px;height:72px' placeholder='请填写'></textarea>
        </div>
    </div>
    <div class="form-actions">
        <button class='cancel-btn'>取消</button>
        <input type="submit" value="<?php echo JText::_('COM_JBLANCE_TRANSFER') ?>" class="btn btn-primary"/>
    </div>

    <input type="hidden" name="option" value="com_jblance"/>
    <input type="hidden" name="task" value="membership.saveescrow"/>
    <?php echo JHtml::_('form.token'); ?>
</form>
