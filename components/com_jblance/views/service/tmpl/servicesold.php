<?php
/**
 * @company        :    BriTech Solutions
 * @created by    :    JoomBri Team
 * @contact        :    www.joombri.in, support@joombri.in
 * @created on    :    13 November 2014
 * @file name    :    views/service/tmpl/servicesold.php
 * @copyright   :    Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :    GNU General Public License version 2 or later
 * @author      :    Faisel
 * @description    :    List of services buyer has bought (jblance)
 */
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.modal', 'a.jb-modal');
$doc = JFactory::getDocument();
$doc->addScript("components/com_jblance/js/layer/layer.js");
$doc->addStyleSheet("components/com_jblance/css/customer/servicesold.css");
$model = $this->getModel();
$user = JFactory::getUser();
$financeHelp = JblanceHelper::get('helper.finance');        // create an instance of the class FinanceHelper
?>
<script type="text/javascript">

</script>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userForm">
    <div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_SERVICES_SOLD'); ?></div>
    <?php if (empty($this->rows)) : ?>
        <div class="alert alert-info">
            <?php echo JText::_('COM_JBLANCE_NO_SERVICES_SOLD_YET'); ?>
        </div>
    <?php else : ?>
        <?php
        for ($i = 0; $i < count($this->rows); $i++) {
            $row = $this->rows[$i];
            $attachments = JBMediaHelper::processAttachment($row->attachment, 'service');
            $link_progress = JRoute::_('index.php?option=com_jblance&view=service&layout=serviceprogress&id=' . $row->id);    // id is the service order id and NOT service id
            $link_invoice = JRoute::_('index.php?option=com_jblance&view=membership&layout=invoice&id=' . $row->id . '&tmpl=component&print=1&type=service&usertype=freelancer');

            $hasPaid = $financeHelp->hasPaid($row->id, 'COM_JBLANCE_SERVICE');
            ?>
            <div class="row-fluid">
                <div class="xp-content-group">
                    <div class="span1 img-show-container">
                        <img class="choose-img" src="<?php echo $attachments[0]['thumb']; ?>"/>
                    </div>
                    <div class='select-user-container'>
                        <div class="span5">
                            <div>
                                <?php echo $row->service_title; ?>
                            </div>
                            <div class="small">
                                <span class="label label-success"><?php echo (!empty($row->p_status)) ? JText::_($row->p_status) : JText::_('COM_JBLANCE_NOT_YET_STARTED'); ?></span>
                            </div>
                            <div style="margin-top: 16px;">
                                <span class="font20"><?php echo JblanceHelper::formatCurrency($row->totalprice, true, false, 0); ?></span>
                               / <span
                                        class="font16"><?php echo JText::plural('COM_JBLANCE_N_DAYS', $row->totalduration); ?></span>
                            </div>
                        </div>
                        <div class="span7 text-center">
                            <div class='need-accept-container'>
                            <?php if (!empty($row->p_status)) { ?>
                                <div class="progress progress-success progress-striped"
                                     title="<?php echo JText::_('COM_JBLANCE_PROGRESS') . ' : ' . $row->p_percent . '%'; ?>"
                                     style="margin: 0px auto; width:50%; float: none;">
                                    <div class="bar" style="width: <?php echo $row->p_percent; ?>%"></div>
                                    <div class="progress-title" style="width: 100%;height: 14px;text-align: center;font-size: 10px;"><?php echo $row->p_percent.'%'; ?></div>
                                </div>
                            <?php } ?>

                                <div class="submit-commit-btn">
                                    <div class="xp-btn">
                                    <a href="<?php echo $link_progress; ?>"
                                       class="btn btn-primary btn-small"><?php echo JText::_('COM_JBLANCE_UPDATE_PROGRESS'); ?></a>
                                    </div>
                                        <?php
                                    $hasRated = $model->hasRated($row->id, $user->id);
                                    if ($row->p_status == 'COM_JBLANCE_COMPLETED' && !$hasRated) {
                                        $link_rate = JRoute::_('index.php?option=com_jblance&view=service&layout=rateservice&id=' . $row->id); ?>
                                    <div class="xp-btn">
                                        <a href="<?php echo $link_rate; ?>"
                                           class="btn btn-primary btn-small"><?php echo JText::_('COM_JBLANCE_RATE_BUYER'); ?></a>
                                    </div>
                                    <?php } ?>
                                    <?php
                                    if ($row->p_status == 'COM_JBLANCE_COMPLETED' && $hasPaid) { ?>
                                        <div class="xp-btn">
                                        <a rel="{handler: 'iframe', size: {x: 650, y: 500}}"
                                           href="<?php echo $link_invoice; ?>"
                                           class="jb-modal btn btn-success btn-small"><?php echo JText::_('COM_JBLANCE_PRINT_INVOICE'); ?></a>
                                        </div>
                                        <?php
                                    } ?>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="lineseparator"></div>
            <?php
        }
        ?>
    <?php endif; ?>

    <!--	<div class="pagination pagination-centered clearfix">-->
    <!--		<div class="display-limit pull-right">-->
    <!--			--><?php //echo JText::_('JGLOBAL_DISPLAY_NUM'); ?><!--&#160;-->
    <!--			--><?php //echo $this->pageNav->getLimitBox(); ?>
    <!--		</div>-->
    <!--		--><?php //echo $this->pageNav->getPagesLinks(); ?>
    <!--	</div>-->

    <div class="clear"></div>
    <div class="xp-pagination" style="margin-top: 56px!important;">
        <div class="serviceListTotal">共<span> <?php echo $this->pageNav->total; ?> </span>条服务信息</div>
        <div class="pull-right">
            <?php echo $this->pageNav->getPagesLinks(); ?>
        </div>
    </div>
</form>
