<?php
/**
 * @company        :    BriTech Solutions
 * @created by    :    JoomBri Team
 * @contact        :    www.joombri.in, support@joombri.in
 * @created on    :    16 March 2012
 * @file name    :    views/membership/tmpl/planadd.php
 * @copyright   :    Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :    GNU General Public License version 2 or later
 * @author      :    Faisel
 * @description    :    Shows list of available Plans (jblance)
 */
defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');
JHtml::_('bootstrap.popover');

$doc = JFactory::getDocument();
$doc->addScript("components/com_jblance/js/simplemodal.js");
$doc->addStyleSheet("components/com_jblance/css/simplemodal.css");
$doc->addStyleSheet("components/com_jblance/css/pricing.css");
$doc->addStyleSheet("components/com_jblance/css/xiping_pricing.css");

$app = JFactory::getApplication();
$model = $this->getModel();
$user = JFactory::getUser();

$config = JblanceHelper::getConfig();
$currencysym = $config->currencySymbol;
$taxname = $config->taxName;
$taxpercent = $config->taxPercent;

$hasJBProfile = JblanceHelper::hasJBProfile($user->id);

JText::script('COM_JBLANCE_CLOSE');

$link_usergroup = JRoute::_('index.php?option=com_jblance&view=guest&layout=showfront', false);
$link_subscr_history = JRoute::_('index.php?option=com_jblance&view=membership&layout=planhistory');


$step = $app->input->get('step', 0, 'int');
$planInRow = 3;    // number of plans in a row. Default is 3. Use values between 1 to 4 and do not go beyond
$span = round(12 / ($planInRow + 1));
$span = 'span' . $span;
?>
<script type="text/javascript">
    <!--
    function gotoRegistration() {
        var form = document.userFormJob;
        form.task.value = 'guest.grabplaninfo';

        if (validateForm()) {
            form.submit();
        }
    }

    function addSubscr() {
        var form = document.userFormJob;
        form.task.value = 'membership.upgradesubscription';
        if (validateForm()) {
            form.submit();
        }
    }

    function validateForm() {
        if (!jQuery("input[name='plan_id']:checked").length) {
            alert('<?php echo JText::_('COM_JBLANCE_PLEASE_CHOOSE_YOUR_PLAN', true); ?>');
            return false;
        } else {
            if (!jQuery("input[name='gateway']:checked").length) {
                alert('<?php echo JText::_('COM_JBLANCE_PLEASE_SELECT_PAYMENT_GATEWAY', true); ?>');
                return false;
            }
            return true;
        }
    }

    function checkZeroPlan(planAmt, planId) {

        if(planAmt == 0){
            jQuery("#paymethod-list").slideUp();
        }else{
            jQuery("#paymethod-list").slideDown();
        }
        jQuery(".xp-content-label").addClass("box-shadow").find(".img").removeClass('img').addClass('img-default');
        jQuery("#content-box" + planId).removeClass("box-shadow").find(".img-default").removeClass('img-default').addClass('img');

    }

    //-->
</script>
<?php
if ($step)
//	echo JblanceHelper::getProgressBar($step);
?>
<div class="xp-register-container">
    <form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userFormJob" enctype="multipart/form-data">
        <div class="jbl_h3title" style="display: none"><?php echo JText::_('COM_JBLANCE_BUY_SUBSCR'); ?></div>

        <div class="row-fluid" style="background: #FFFFFF;">
            <div class="span12 pricing comparsion">

                <div class="head">
                    <div class="register">
                        <?php
                        echo JText::_('COM_JBLANCE_REGISTER')."-";
                        if (!$hasJBProfile) {
                            $session = JFactory::getSession();
                            $ugid = $session->get('ugid', 0, 'register');
                            $jbuser = JblanceHelper::get('helper.user');
                            $groupName = $jbuser->getUserGroupInfo(null, $ugid)->ug_name;
                    echo $groupName;
//			echo JText::sprintf('COM_JBLANCE_USERGROUP_CHOSEN_CLICK_TO_CHANGE', $groupName, $link_usergroup);
                        }; ?>
                    </div>
                    <div class="register-text"><?php echo JText::_('COM_JBLANCE_WELCOME') ?></div>
                    <div class="register-step-1">
                        <div class="img"></div>
                    </div>
                    <div class="register-step-txt">
                        <div class="txt"><?php echo JText::_('COM_JBLANCE_SUBSCRIBETO') ?></div>
                        <div class="txt"><?php echo JText::_('COM_JBLANCE_ACCOUNT_REGISTER') ?></div>
                        <div class="txt"><?php echo JText::_('COM_JBLANCE_COMPLATE_MESSAGE') ?></div>
                    </div>
                </div>
                <?php
                if ($hasJBProfile) { ?>
                    <p>
                        <a href="<?php echo $link_subscr_history; ?>" class="btn btn-primary"><i
                                    class="jbf-icon-clock"></i> <?php echo JText::_('COM_JBLANCE_SUBSCR_HISTORY'); ?>
                        </a>
                    </p>
                    <?php
                }
                ?>
                <p style="display: none;"><?php
                    if ($hasJBProfile)
                        echo JText::_('COM_JBLANCE_CHOOSE_SUBSCR_PAYMENT');
                    else
                        echo JText::_('COM_JBLANCE_SUBSCR_WELCOME'); ?>
                </p>


                <?php
                if (empty($this->rows)){
                    echo '<p class="alert alert-error">' . JText::_('COM_JBLANCE_NO_PLAN_ASSIGNED_FOR_USERGROUP') . '</p>';
                }
                else {
                ?>
                <div class="xp-service-content">
                    <div class="service-item"><?php echo JText::_('COM_JBLANCE_SWITCH_SERVICE_TYPE') ?></div>
                    <div class="service-content">
                        <?php
                        $infos = $model->buildPlanInfo($this->rows[0]->id);
                        //get the array of plan ids, the user has subscribed to.
                        $planArray = array();
                        foreach ($this->plans as $plan) {
                            $planArray[] = $plan->planid;
                        }
                        $totPlans = count($this->rows);
                        for ($i = 0; $i < $totPlans; $i++) {
                            $row = $this->rows[$i];
                            $nprice = '';
                            if (($row->discount > 0) && in_array($row->id, $planArray) && ($row->price > 0)) {
                                $nprice = $row->price - (($row->price / 100) * $row->discount);
                                $npriceNoformat = $nprice;
                                $nprice = JblanceHelper::formatCurrency($nprice, true, false, 0);
                            }
                            $infos = $model->buildPlanInfo($row->id);

                            //get the option params
                            $options = new JRegistry;
                            $options->loadString($row->option_params);

                            $theme = $options->get('theme', 'blue');

                            if ($i % $planInRow == 0) {

                            }
                            ?>
                            <label for="plan_id<?php echo $row->id; ?>" id="lbl_plan_id<?php echo $row->id; ?>"
                                   class="<?php if ($i !== 0) {
                                       echo 'content-margin';
                                   } ?>">
                                <?php if ($user->id > 0 && $row->time_limit > 0 && in_array($row->id, $planArray) && $this->plans[$row->id]->plan_count >= $row->time_limit) : ?>
                                    <button type="button" class="btn disabled"
                                            onclick="javascript:modalAlert('<?php echo JText::_('COM_JBLANCE_LIMIT_EXCEEDED', true); ?>', '<?php echo JText::sprintf('COM_JBLANCE_PLAN_PURCHASE_LIMIT_MESSAGE', $row->time_limit, array('jsSafe' => true)); ?>');"><?php echo JText::_('COM_JBLANCE_SELECT'); ?></button>
                                <?php else: ?>
                                    <input type="radio" name="plan_id" id="plan_id<?php echo $row->id; ?>"
                                           value="<?php echo $row->id; ?>" class="jb-hidefield"
                                           onclick="javascript:checkZeroPlan('<?php echo $nprice ? $npriceNoformat : $row->price; ?>', '<?php echo $row->id; ?>');"/>
                                <?php endif; ?>
                                <div class="xp-content-label content-item box-shadow "
                                     id="content-box<?php echo $row->id; ?>"
                                     onclick="javascript:checkZeroPlan('<?php echo $nprice ? $npriceNoformat : $row->price; ?>', '<?php echo $row->id; ?>');">

                                    <div class="item-head">
                                        <div class="head-1">
                                            <?php echo $row->name; ?>
                                            <?php if (!empty($row->description)) { ?>
                                                <span class="hasPopover font14" style="display: inline-flex;"
                                                      data-placement="bottom" title="<?php echo $row->name; ?>"
                                                      data-content="<?php echo $row->description; ?>"><i
                                                            class="jbf-icon-info"></i></span>
                                            <?php } ?>
                                        </div>

                                        <div class="head-2">
                                            <div class="item">
                                             <span>￥</span>
                                            <?php echo $nprice ? '<span style="float:left; color:red; text-decoration:line-through">' . ' ' . JblanceHelper::formatCurrency($row->price, true, false, 0) . '</span><span>' . $nprice . '</span>' : '<span>'.JblanceHelper::formatCurrency($row->price, false, false, 0).'</span>'; ?>
                                            <?php
                                            if ($row->days > 100 && $row->days_type == 'years')
                                                echo JText::_('COM_JBLANCE_LIFETIME');
                                            else { ?>
                                                <span>/<?php echo JblanceHelper::getDaysType($row->days, $row->days_type); ?></span>
                                                <?php
                                            } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="item-content">
                                        <div class="item-group">
                                            <div class="item"><?php echo JText::_('COM_JBLANCE_BONUS_FUND'); ?></div>
                                            <div class="item">
                                                <?php echo JblanceHelper::formatCurrency($row->bonusFund, true, false, 0); ?>
                                            </div>
                                        </div>
                                        <?php foreach ($infos as $val) { ?>
                                            <div class="item-group">
                                                <div class="item"><?php echo $val->key ?></div>
                                                <div class="item">
                                                    <?php echo $val->value; ?>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="img-default"></div>

                                </div>
                                <input type="hidden" name="planname<?php echo $row->id; ?>"
                                       id="planname<?php echo $row->id; ?>" value="<?php echo $row->name; ?>"/>
                                <input type="hidden" name="planperiod<?php echo $row->id; ?>"
                                       id="planperiod<?php echo $row->id; ?>"
                                       value="<?php echo JblanceHelper::getDaysType($row->days, $row->days_type); ?>"/>
                                <input type="hidden" name="plancredit<?php echo $row->id; ?>"
                                       id="plancredit<?php echo $row->id; ?>" value="<?php echo $row->bonusFund; ?>"/>
                                <input type="hidden" name="price<?php echo $row->id; ?>"
                                       id="price<?php echo $row->id; ?>"
                                       value="<?php echo $nprice ? $nprice : $row->price; ?>"/>
                            </label>
                            <?php
                            if ($i % $planInRow == ($planInRow - 1) || $i == ($totPlans - 1)) { ?>

                                <?php
                            }
                        }
                        ?>
                    </div>
                    <div class="note-text"><?php echo JText::_('COM_JBLANCE_PRICE_DESCRIBE')?>></div>
                </div>
            </div>

            <?php
            if ($taxpercent > 0) { ?>
                <p class="alert alert-info" style="display: none;">
                    <?php echo JText::sprintf('COM_JBLANCE_TAX_APPLIES', $taxname, $taxpercent); ?>
                </p>
                <?php
            } ?>
        </div>
            <div class="xp-service-content margin-method-top" id="paymethod-list">
                <div class="service-item"><?php echo JText::_('COM_JBLANCE_SWITCH_PAYMETHOD') ?></div>
                <div class="paymethod-service-content">
                    <div class="item-group">
                        <?php
                        $list_paymode = $model->getNewPaymode('gateway', '', '', 'subscription');
                        foreach ($list_paymode as $key => $val) {
                            ?>
                            <div class="RadioStyle">
                                <input type="radio" name="gateway"
                                       id="<?php echo $val->value; ?>" <?php if ($key === 0) echo "checked"; ?>
                                       value="<?php echo $val->value; ?>"/>
                                <label for="<?php echo $val->value; ?>"><img
                                            src="<?php echo "components/com_jblance/gateways/xp_register/$val->value.png" ?>"></label>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="xp-service-content">
                <div class="paymethod-service-content">
                    <?php if ($hasJBProfile) : ?>
                        <div class="next-button"
                             onclick="addSubscr();"><?php echo JText::_('COM_JBLANCE_CONTINUE') ?></div>
                    <?php else : ?>
                        <div class="next-button"
                             onclick="gotoRegistration();"><?php echo JText::_('COM_JBLANCE_CONTINUE'); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <!--		<div class="form-actions">-->
            <!--		--><?php //if($hasJBProfile) : ?>
            <!--			<input type="button" class="btn btn-primary" value="-->
            <?php //echo JText::_('COM_JBLANCE_CONTINUE') ?><!--" onclick="addSubscr();"/>-->
            <!--		--><?php //else : ?>
            <!--			<input type="button" class="btn btn-primary" value="-->
            <?php //echo JText::_('COM_JBLANCE_CONTINUE'); ?><!--" onclick="gotoRegistration();" />-->
            <!--		--><?php //endif; ?>
            <!--		</div>-->

            <?php
            }
            ?>
        </div>
        <input type="hidden" name="option" value="com_jblance">
        <input type="hidden" name="task" value="">
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
