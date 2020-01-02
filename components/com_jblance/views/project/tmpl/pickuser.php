<?php
/**
 * @company        :    BriTech Solutions
 * @created by    :    JoomBri Team
 * @contact        :    www.joombri.in, support@joombri.in
 * @created on    :    27 March 2012
 * @file name    :    views/project/tmpl/pickuser.php
 * @copyright   :    Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :    GNU General Public License version 2 or later
 * @author      :    Faisel
 * @description    :    Pick user from the bidders (jblance)
 */
defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');

$doc = JFactory::getDocument();
$doc->addScript("components/com_jblance/js/simplemodal.js");
$doc->addStyleSheet("components/com_jblance/css/simplemodal.css");
$doc->addStyleSheet("components/com_jblance/css/customer/pickuser.css");

$model = $this->getModel();
$user = JFactory::getUser();
$config = JblanceHelper::getConfig();

$currencycode = $config->currencyCode;
$dformat = $config->dateFormat;
$checkFund = $config->checkfundPickuser;
$showUsername = $config->showUsername;

$nameOrUsername = ($showUsername) ? 'username' : 'name';

$curr_balance = JblanceHelper::getTotalFund($user->id);

$link_deposit = JRoute::_('index.php?option=com_jblance&view=membership&layout=depositfund', false);

JText::script('COM_JBLANCE_CLOSE');
JText::script('COM_JBLANCE_YES');
?>
<script>
    <!--
    function checkBalance(id) {

        jQuery("#"+id).attr('checked',true);

        if (!jQuery("input[name='assigned_userid']:checked").length) {
            alert('<?php echo JText::_('COM_JBLANCE_PLEASE_PICK_AN_USER_FROM_THE_LIST', true); ?>');
            return false;
        }

        var checkFund = parseInt('<?php echo $checkFund; ?>');

        if (checkFund) {
            var balance = parseFloat('<?php echo $curr_balance; ?>');
            var assigned = jQuery("input[name='assigned_userid']:checked").val();
            var bidamt = jQuery("#bidamount_" + assigned).val();

            if (balance < bidamt) {
                modalConfirm('<?php echo JText::_('COM_JBLANCE_INSUFFICIENT_FUND'); ?>', '<?php echo JText::_('COM_JBLANCE_INSUFFICIENT_BALANCE_PICK_USER'); ?>', '<?php echo $link_deposit; ?>');
                return false;
            }
        }
        return true;
    }


    //-->
</script>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userForm">
    <div class='select-user'>
        <div class='select-user-p'>选择用户</div>
        <div class='select-user-momey'><?php echo JText::_('COM_JBLANCE_CURRENT_BALANCE'); ?>：
            <span style='color: #FF6010'><?php echo JblanceHelper::formatCurrency($curr_balance); ?></span>
        </div>
    </div>
    <div class='partition-line'></div>
    <!-- 与设计稿有差异，暂时隐藏，根据实际需求是否删除 -->
    <div class="jbl_h3title"
         style='display:none'><?php echo JText::_('COM_JBLANCE_PICK_USER') . ' : ' . $this->project->project_title; ?></div>

    <!-- <div class="well well-small pull-right span3 text-center font16">
		<b><?php // echo JText::_('COM_JBLANCE_CURRENT_BALANCE'); ?> : <?php // echo JblanceHelper::formatCurrency($curr_balance); ?></b>
	</div> -->
    <div class="clearfix"></div>

    <?php
    for ($ii = 0; $ii < count($this->rows); $ii++) {
        $row = $this->rows[$ii];
        ?>
        <div class="row-fluid">
            <?php if ($ii == 0): ?>
                <div class="head-title">
                    <div class='select-user-detail-top'>
                        <div style='font-size:12px;color:#999999'><?php echo JHtml::_('date', $row->bid_date, $dformat); ?></div>
                        <div>
                            <span style='font-size:12px;color:#999999'>&nbsp;发布&nbsp;</span>
                            <span style='color:#FF6010;font-size:12px'>出价：<?php echo JblanceHelper::formatCurrency($row->amount, true, false, 0); ?></span>
                        </div>
                    </div>
                    <div class='desc-title'>【<?php echo $this->project->project_title; ?>】</div>
                </div>
            <?php endif; ?>


            <div class="xp-content-group">
                <div class="span1 img-show-container">
                    <img src="<?php
                    $attrib = 'width=56 height=56 class="img-polaroid"';
                    $avatar = JblanceHelper::getLogo($row->user_id, $attrib);
                    $pattern = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
                    preg_match_all($pattern, $avatar, $match);
                    //echo !empty($avatar) ? LinkHelper::GetProfileLink($row->user_id, $avatar) : '&nbsp;';
                    echo isset($match[1]) ? $match[1][0] : '';
                    ?>" class="choose-img"/>
                </div>
                <div class='select-user-container'>
                    <!-- 设计稿没有，暂时隐藏 ，根据实际需求是否删除-->
                    <div class="span1" style="width:5px;display:none">
                        <?php if ($row->status == '') : ?>
                            <input type="radio" name="assigned_userid" id="assigned_userid_<?php echo $row->id; ?>"
                                   value="<?php echo $row->user_id; ?>"/>
                        <?php endif; ?>
                    </div>
                    <div class="span6">
                        <h5 class="media-heading">
                            <?php echo LinkHelper::GetProfileLink(intval($row->user_id), $this->escape($row->$nameOrUsername)); ?>
                        </h5>
                        <p>
                            <?php $rate = JblanceHelper::getAvarageRate($row->user_id, false);
                            for ($i = 1; $i <= 5; $i++) {
                                ?>
                                <img src="/components/com_jblance/images/need/<?php if ($i <= $rate) {
                                    echo 'hot';
                                } else {
                                    echo 'cold';
                                } ?>.png" alt="" style='width:11px;height:11px'>
                            <?php } ?>
                        </p>
                        <div>
					<span style='font-size: 18px;color:#FF6010;font-family: MicrosoftYaHei;'>
						<?php echo JblanceHelper::formatCurrency($row->amount, true, false, 0); ?>
					</span>/
                            <span style='font-size: 14px;color:#333333;font-family: MicrosoftYaHei;'>
						<?php if ($row->project_type == 'COM_JBLANCE_FIXED') : ?>
                            <?php echo $row->delivery; ?><?php echo JText::_('COM_JBLANCE_BID_DAYS'); ?>
                        <?php elseif ($row->project_type == 'COM_JBLANCE_HOURLY') :
                            $commitment = new JRegistry;
                            $commitment->loadString($row->commitment);
                            ?>
                            <?php echo $row->delivery; ?><?php echo JText::_('COM_JBLANCE_HOURS_PER') . ' ' . JText::_($commitment->get('interval')); ?>
                        <?php endif; ?>
					</span>
                            <span style='font-size: 14px;color:#999999;font-family: MicrosoftYaHei;'>(每天<?php echo JblanceHelper::formatCurrency($row->amount / $row->delivery, true, false, 0); ?>元)</span>
                        </div>
                        <!-- 设计稿上面是图片 暂时隐藏 ，根据实际需求是否删除-->
                        <p class="font14"
                           style='display:none'><?php echo ($row->details) ? $row->details : JText::_('COM_JBLANCE_DETAILS_NOT_PROVIDED'); ?></p>
                        <!-- 设计稿上面是图片 暂时隐藏 ，根据实际需求是否删除-->
                        <p style='display:none'>
                            <span title="<?php echo JText::_('COM_JBLANCE_BID_DATE'); ?>"><i
                                        class="jbf-icon-calendar"></i> <?php echo JHtml::_('date', $row->bid_date, $dformat); ?></span>
                            <!-- Show attachment if found -->
                            <?php
                            if (!empty($row->attachment)) : ?>
                                |
                                <span><?php echo LinkHelper::getDownloadLink('nda', $row->id, 'project.download'); ?></span>
                            <?php
                            endif;
                            ?>
                        </p>
                    </div>
                    <!-- 设计稿没有 暂时隐藏 ，根据实际需求是否删除-->
                    <div class="span2" style='display:none'>
                        <?php $rate = JblanceHelper::getAvarageRate($row->user_id, true); ?>
                    </div>
                    <!-- 设计稿没有 暂时隐藏 ，根据实际需求是否删除-->
                    <div class="span2" style='display:none'>
                        <div class="text-center">
					<span class="font20">
						<?php echo JblanceHelper::formatCurrency($row->amount, true, false, 0); ?>
						<input type="hidden" id="bidamount_<?php echo $row->user_id; ?>"
                               value="<?php echo $row->amount; ?>"/>
					</span><?php echo ($row->project_type == 'COM_JBLANCE_HOURLY') ? ' / ' . JText::_('COM_JBLANCE_HR') : ''; ?>
                            <br>
                            <span class="font12">
					<?php if ($row->project_type == 'COM_JBLANCE_FIXED') : ?>
                        <?php echo $row->delivery; ?><?php echo JText::_('COM_JBLANCE_BID_DAYS'); ?>
                    <?php elseif ($row->project_type == 'COM_JBLANCE_HOURLY') :
                        $commitment = new JRegistry;
                        $commitment->loadString($row->commitment);
                        ?>
                        <?php echo $row->delivery; ?><?php echo JText::_('COM_JBLANCE_HOURS_PER') . ' ' . JText::_($commitment->get('interval')); ?>
                    <?php endif; ?>
					</span>
                            <?php if ($row->status == 'COM_JBLANCE_ACCEPTED') : ?>
                                <span class="label label-success"><?php echo JText::_($row->status); ?></span>
                            <?php elseif ($row->status == 'COM_JBLANCE_DENIED') : ?>
                                <span class="label label-important"><?php echo JText::_($row->status); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class='need-accept-container'>
                        <div style='margin-left:80px'>
                            <img src="/components/com_jblance/images/need/accept.png" alt=""
                                 style='width:80px;height:80px'>
                        </div>
                        <input type="radio" name="assigned_userid" id="assigned_userid_<?php echo $row->id; ?>"
                               value="<?php echo $row->user_id; ?>" style="visibility: hidden"/>
                        <div class="submit-commit-btn">
                            <input type="submit" value="<?php echo JText::_('COM_JBLANCE_PICK_USER'); ?>" class="xp-btn"
                                   onclick="return checkBalance('assigned_userid_<?php echo $row->id; ?>');"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 设计稿没有 暂时隐藏 ，根据实际需求是否删除,就是一分割线-->
        <div class="lineseparator"></div>
        <?php
    }
    ?>
    <div class="clear"></div>
    <div class="xp-pagination" style="margin-top: 56px!important;">
        <div class="serviceListTotal">共<span> <?php echo $this->pageNav->total; ?> </span>条服务信息</div>
        <div class="pull-right">
            <?php echo $this->pageNav->getPagesLinks(); ?>
        </div>
    </div>
    <!-- 设计稿没有 暂时隐藏 ，根据实际需求是否删除-->
    <div class="form-actions" style='display:none'>
        <input type="submit" value="<?php echo JText::_('COM_JBLANCE_PICK_USER'); ?>" class="btn btn-primary"
               onclick="return checkBalance();"/>
    </div>
    <input type="hidden" name="option" value="com_jblance"/>
    <input type="hidden" name="task" value="project.savepickuser"/>
    <input type="hidden" name="id" value="<?php echo $row->project_id; ?>"/>
    <?php echo JHtml::_('form.token'); ?>
</form>
