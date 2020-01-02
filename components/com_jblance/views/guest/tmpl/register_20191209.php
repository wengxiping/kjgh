<?php
/**
 * @company        :    BriTech Solutions
 * @created by    :    JoomBri Team
 * @contact        :    www.joombri.in, support@joombri.in
 * @created on    :    16 March 2012
 * @file name    :    views/guest/tmpl/register.php
 * @copyright   :    Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :    GNU General Public License version 2 or later
 * @author      :    Faisel
 * @description    :    User Groups (jblance)
 */
defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');
JHtml::_('behavior.formvalidator');
JHtml::_('bootstrap.tooltip');

$doc = JFactory::getDocument();
$doc->addScript("components/com_jblance/js/utility.js");
$doc->addScript("components/com_jblance/js/simplemodal.js");
$doc->addStyleSheet("components/com_jblance/css/simplemodal.css");
$doc->addStyleSheet("components/com_jblance/css/xiping_pricing.css");

$app = JFactory::getApplication();
$user = JFactory::getUser();
$model = $this->getModel();
$config = JblanceHelper::getConfig();
$taxpercent = $config->taxPercent;
$taxname = $config->taxName;

$session = JFactory::getSession();
$ugid = $session->get('ugid', 0, 'register');
$planChosen = $session->get('planChosen', 0, 'register');
$planId = $session->get('planid', 0, 'register');
$skipPlan = $session->get('skipPlan', 0, 'register');

$jbuser = JblanceHelper::get('helper.user');        // create an instance of the class UserHelper

if (empty($planId)) {    //this is to check if the user has selected plan and entered this page
    $link = JRoute::_('index.php?option=com_jblance&view=guest&layout=showfront', false);
    $app->redirect($link);
}

$step = $app->input->get('step', 0, 'int');
JText::script('COM_JBLANCE_AVAILABLE');

$termid = $config->termArticleId;
//$link = JRoute::_("index.php?option=com_content&view=article&id=".$termid.'&tmpl=component');
$link = JUri::root() . "index.php?option=com_content&view=article&id=" . $termid . "&tmpl=component";

JblanceHelper::setJoomBriToken();
?>
<script type="text/javascript">
    <!--
    function validateForm(f) {
        if (!$("#xp-checkbox").is(":checked")) {
            alert('请勾选协议！！！');
            return false;
        }
        var valid = document.formvalidator.isValid(f);

        //check password equals password2
        if (jQuery("#password").val() != jQuery("#password2").val()) {
            alert('<?php echo JText::_('COM_JBLANCE_VERIFY_PASSWORD_INVALID', true); ?>');
            return false;
        }

        if (valid == true) {

        } else {
            alert('<?php echo JText::_('COM_JBLANCE_FIEDS_HIGHLIGHTED_RED_COMPULSORY', true); ?>');
            return false;
        }
        return true;
    }

    jQuery(function ($) {
        $("a.jb-modal").click(function (e) {
            e.preventDefault();
            JoomBriSM.popupURL('<?php echo $link; ?>');
        });
        $(".xieyi-label").click(function () {
            if ($("#xp-checkbox").is(":checked")) {
                $(".checkbox-img-default").removeClass('checkbox-img-default').addClass('checkbox-img-selected');
            } else {
                $(".checkbox-img-selected").removeClass('checkbox-img-selected').addClass('checkbox-img-default');
            }
        });
    });
    //-->
</script>
<div class="row-fluid">
    <div class="span12 pricing comparsion">
        <div class="head">
            <div class="register">注册</div>
            <div class="register-text">欢迎注册“话事”跨境电商外包服务平台！</div>
            <div class="register-step-2">
                <div class="img"></div>
            </div>
            <div class="register-step-txt">
                <div class="txt">订阅套餐</div>
                <div class="txt">账户注册</div>
                <div class="txt">完善信息</div>
            </div>
        </div>


        <form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="regNewUser"
              class="form-horizontal form-validate" onsubmit="return validateForm(this);" enctype="multipart/form-data">
            <!--<div class="jbl_h3title">--><?php //echo JText::_('COM_JBLANCE_ACCOUNT_INFO'); ?><!--</div>-->
            <?php //echo JText::_('COM_JBLANCE_FIELDS_COMPULSORY'); ?>

            <?php if (!$skipPlan) { ?>
                <fieldset class="fieldset">
                    <div class="xp-profile">已选择的“话事”服务会员身份</div>
                    <?php $sub_id = $planChosen['plan_id'];
                    ?>
                    <table class="table table-bordered" style="border-bottom:0px;border-collapse:collapse;">
                        <tr class="xp-tr-height36">
                            <th class="xp-tr-height-th">服务类型</th>
                            <th class="xp-tr-height-th">持续时间</th>
                            <th class="xp-tr-height-th">奖金基金</th>
                            <th class="xp-tr-height-th">总金额</th>
                        </tr>
                        <tr class="xp-tr-height52">
                            <td class="xp-tr-td"><?php echo $planChosen['planname' . $sub_id]; ?></td>
                            <td class="xp-tr-td"><?php echo $planChosen['planperiod' . $sub_id]; ?></td>
                            <td class="xp-tr-td"><?php echo JblanceHelper::formatCurrency($planChosen['plancredit' . $sub_id]); ?></td>
                            <td class="xp-tr-td">
                                <?php
                                $totalamt = $planChosen['price' . $sub_id];
                                if ($taxpercent > 0) {
                                    $taxamt = $totalamt * ($taxpercent / 100);
                                    $totalamt = $taxamt + $totalamt;
                                }
                                echo JblanceHelper::formatCurrency($totalamt);
                                if ($taxpercent > 0 && $totalamt > 0) {
                                    echo ' (' . JblanceHelper::formatCurrency($planChosen['price' . $sub_id]) . ' + ' . JblanceHelper::formatCurrency($taxamt) . ')';
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                </fieldset>
            <?php } ?>

            <fieldset class="fieldset">
                <div class="xp-profile">填写账户信息</div>
                <div class="xp-profile-item">
                    <div class="profile-left"><span class="redfont">*</span><span>用户昵称 : </span></div>
                    <div class="profile-right"><input class="required" type="text" name="name" id="name"
                                                      placeholder="请填写"/></div>
                </div>
                <div class="xp-profile-item">
                    <div class="profile-left"><span class="redfont">*</span><span>用户名:</span></div>
                    <div class="profile-right"><input class="required" type="text" name="username" id="username"
                                                      placeholder="请填写"/></div>
                </div>

                <div class="xp-profile-item">
                    <div class="profile-left"><span class="redfont">*</span><span>邮箱:</span></div>
                    <div class="profile-right"><input class="required" type="text" name="email" id="email"
                                                      placeholder="请填写"/></div>
                </div>
                <div class="xp-profile-item">
                    <div class="profile-left"><span class="redfont">*</span><span>密码:</span></div>
                    <div class="profile-right"><input class="required" type="text" name="password" id="password"
                                                      placeholder="请填写"/></div>
                </div>
                <div class="xp-profile-item">
                    <div class="profile-left"><span class="redfont">*</span><span>确认密码:</span></div>
                    <div class="profile-right"><input class="required" type="text" name="password2" id="password2"
                                                      placeholder="请填写"/></div>
                </div>
                <div class="xp-profile-item">
                    <div class="xieyi-left"></div>
                    <div class="xieyi-right">
                        <label class="xieyi-label" for="xp-checkbox">
                            <div class="checkbox-img-default"></div>
                            <input class="required" type="checkbox" name="checkbox" id="xp-checkbox">
                            <p><span>阅读并接受</span><span>《话事》</span><span>服务平台条款与协议。</span></p>
                        </label>
                    </div>
                </div>
                <div class="btn-step-action">
                    <div class="left-back">
                        <input type="submit" value="上一步"
                               class=""/>
                    </div>
                    <div class="left-create">
                        <input type="submit" value="创建账户"
                               class=""/>
                    </div>
                </div>
            </fieldset>
            <input type="hidden" name="option" value="com_jblance"/>
            <input type="hidden" name="task" value="guest.grabuseraccountinfo"/>
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>
