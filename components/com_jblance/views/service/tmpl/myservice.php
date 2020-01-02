<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	04 November 2014
 * @file name	:	views/service/tmpl/myservice.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	List of services provided by users (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

 JHtml::_('bootstrap.framework');

 $doc = Jfactory::getDocument();
 $doc->addStyleSheet("components/com_jblance/css/customer/service.css");

 $model = $this->getModel();

 $link_edit_service	= JRoute::_('index.php?option=com_jblance&view=service&layout=editservice');
 ?>
<script>

    function pageItemList(ii, totalCurrent, t) {
        var T = 0;
        var TT = 0;
        var sArr = [];//开始数组
        var eArr = [];//结束数据
        if ((ii - 1 + 4) > totalCurrent) {//后面页数
            for (var i1 = 1; i1 <= ii; i1++) {//当前页向前
                if (i1 == ii - 3) {
                    sArr.unshift('<li class="page-active">...</li>');//将字符加入数组头部
                    break;
                } else {
                    sArr.unshift('<li onclick="getHighAjaxData(' + (ii - i1) + ',' + t + ')">' + (ii - i1) + '</li>');//将数据推入数组中
                }
            }
            for (var i2 = ii + 1; i2 <= totalCurrent; i2++) {
                eArr.push('<li onclick="getHighAjaxData(' + i2 + ',' + t + ')">' + i2 + '</li>');
            }
        } else {
            for (var i1 = ii - 1; i1 > 0; i1--) {//当前页向前
                if (T == 2) {
                    sArr.unshift('<li class="page-active">...</li>');//将字符加入数组头部
                    break;
                } else {
                    sArr.unshift('<li onclick="getHighAjaxData(' + i1 + ',' + t + ')">' + i1 + '</li>');//将数据推入数组中
                }
                T++;
            }
            for (var i2 = ii + 1; i2 <= totalCurrent; i2++) {
                if ((parseInt(TT) + parseInt(T)) == 4) {
                    eArr.push('<li class="page-active">...</li>');
                    break;
                } else {
                    eArr.push('<li onclick="getHighAjaxData(' + i2 + ',' + t + ')">' + i2 + '</li>');
                }
                TT++;
            }
        }

        return {
            "start": sArr.join(""),
            "end": eArr.join("")
        }
    }

</script>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userForm">
<div class="myserviceBox">
	<div class="clearfix"></div>
	<div class="jbl_h3title">
	    <span class="pull-left"><?php echo JText::_('COM_JBLANCE_MY_SERVICES');?></span>
	    <div class="pull-right">
    		<a href="<?php echo $link_edit_service; ?>" class="btn btn-primary">
    		<span>
<!--    		      添加新服务 -->
    		  <?php echo JText::_('COM_JBLANCE_ADD_SERVICE'); ?>
    		</span>
    		</a>
    	</div>
	</div>

	<?php if(empty($this->rows)) : ?>
	<div class="alert alert-info">
  		<?php echo JText::_('COM_JBLANCE_NO_SERVICES_POSTED_YET'); ?>
	</div>
	<?php else : ?>
	<?php
	for($i=0; $i < count($this->rows); $i++){
		$row = $this->rows[$i];
		$link_edit	= JRoute::_('index.php?option=com_jblance&view=service&layout=editservice&id='.$row->id);
		$link_view	= JRoute::_('index.php?option=com_jblance&view=service&layout=viewservice&id='.$row->id);
		$link_sold	= JRoute::_('index.php?option=com_jblance&view=service&layout=servicesold&id='.$row->id);
		$attachments = JBMediaHelper::processAttachment($row->attachment, 'service');
		$getStatusCounts = $model->getServiceProgressCounts($row->id);
	?>
	<div class="row-fluid">
		<div class="span2">
			<img class="img-polaroid" src="<?php echo $attachments[0]['thumb']; ?>" width="80" />
		</div>
		<div class="span6">
			  <div class="span6-title">
			      <?php echo $row->service_title; ?>
              	 <span> <?php if($row->approved == 0) echo '<span class="label label-important">'.JText::_('COM_JBLANCE_PENDING_APPROVAL').'</span>';?></span>
              </div>
              <?php
              if(isset($row->categoryLiist))
              foreach(explode(',',$row->categoryLiist) as $val){?>
              <div class="span6-tags">
                 <?php echo $val;?>
              </div>
              <?php  }?>

				<?php if($row->buycount > 0){ ?>
				<a href="<?php echo $link_sold; ?>">
					<span class="label label-success"><?php echo JText::plural('COM_JBLANCE_N_SERVICES_BOUGHT', $row->buycount); ?></span>
				</a>
				<div class="small"><?php echo $getStatusCounts; ?></div>
				<?php } ?>
            <div style="display:block;margin-top:70px;">
                 <span class="font20"><?php echo JblanceHelper::formatCurrency($row->price, true, false, 0); ?></span>
                        <!-- <?php echo JText::_('COM_JBLANCE_IN'); ?> -->
                 <span class="font16">/<?php echo JText::plural('COM_JBLANCE_N_DAYS', $row->duration); ?></span>
            </div>

		</div>
		<div class="span4 text-center">

			<div class="btn-group">
				<a href="<?php echo $link_view; ?>" class="btn">
					<!-- <span class="glyphicon glyphicon-search"></span> -->
					<?php echo JText::_('COM_JBLANCE_VIEW_SERVICE'); ?>
				</a>
                <a href="<?php echo $link_edit; ?>"  class="btn"><?php echo JText::_('COM_JBLANCE_EDIT_SERVICE'); ?></a>
              </div>
		</div>
	</div>
	<?php
	} ?>

        <div class="clear"></div>
        <div class="xp-pagination" style="margin-top: 56px!important;">
            <div class="serviceListTotal">共<span> <?php echo $this->pageNav->total;?> </span>条服务信息</div>
            <div class="pull-right">
                <?php echo $this->pageNav->getPagesLinks(); ?>
            </div>
        </div>


	<?php endif; ?>
</div>
</form>
