<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	12 November 2014
 * @file name	:	views/service/tmpl/listservice.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	List of all services (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

 use Joomla\Utilities\ArrayHelper;

 JHtml::_('jquery.framework');
 JHtml::_('formbehavior.chosen', '#id_categ');

 $doc 	 = JFactory::getDocument();
 $doc->addStyleSheet("components/com_jblance/css/customer/service.css");

 $app  = JFactory::getApplication();
 $n = count($this->rows);
 $select = JblanceHelper::get('helper.select');		// create an instance of the class SelectHelper

 $keyword	  = $app->input->get('keyword', '', 'string');
 $id_categ	  = $app->input->get('id_categ', array(), 'array');
 $id_categ	  = ArrayHelper::toInteger($id_categ);
 ?>

<form action="<?php echo JRoute::_('index.php?option=com_jblance&view=service&layout=listservice'); ?>" method="post" name="userForm" class="form-inline" style="background: #FFFFFF;padding-bottom: 20px;">
<!--	<div class="jbl_h3title">--><?php //echo JText::_('COM_JBLANCE_SERVICES'); ?><!--</div>-->
<!---->
<!--	<div class="row-fluid">-->
<!--		<div class="span12">-->
<!--			<div class="pull-right">    -->
<!--			<input type="text" name="keyword" id="keyword" value="--><?php //echo $keyword; ?><!--" class="input-large" placeholder="--><?php //echo JText::_('COM_JBLANCE_KEYWORDS'); ?><!--" />-->
<!--  			--><?php
//			$attribs = 'class="input-large" size="1"';
//			$categtree = $select->getSelectCategoryTree('id_categ[]', $id_categ, 'COM_JBLANCE_ALL_CATEGORIES', $attribs, '', true);
//			echo $categtree; ?>
<!--  			<input type="submit" value="--><?php //echo JText::_('COM_JBLANCE_SEARCH'); ?><!--" class="btn btn-primary" />-->
<!--  			</div>-->
<!--		</div>-->
<!--	</div>-->
<!--	<div class="sp10">&nbsp;</div>-->
	<?php
	if($n){ ?>
	<div class="row-fluid">
	<ul class="thumbnails">
		<?php
		for($i=0; $i < $n; $i++){
			$row = $this->rows[$i];
			$attachments = JBMediaHelper::processAttachment($row->attachment, 'service');		//from the list, show the first image
			$link_view	= JRoute::_('index.php?option=com_jblance&view=service&layout=viewservice&id='.$row->id);
			$sellerInfo = JFactory::getUser($row->user_id);
		?>
		<li class="span3 thumbfix">
			<div class="thumbnail">
				<div class="title_container"><a href="<?php echo $link_view; ?>"><?php echo $row->service_title; ?></a></div>
				<div class="item-info">
					<div>进行中</div>
					<div>销量：<span><?php echo $row->sell_nums?></span></div>
				</div>
				<a href="<?php echo $link_view; ?>">
					<div class="jbf-image" style="background-image: url('<?php echo $attachments[0]['location']; ?>');"></div>
				</a>
                <div class="category">
                <?php
                if(!empty($row->categoryList))
                    foreach(explode(',',$row->categoryList) as $val){?>

                            <span><?php echo $val;?></span>

                    <?php  }?>
                </div>
<!--				<div class="category">-->
<!--					<span>123</span>-->
<!--					<span>321</span>-->
<!--					<span>123</span>-->
<!--					<span>5213562638552</span>-->
<!--				</div>-->
				<div class="caption">
				   <div class="">
						<!-- 设计图上没有先注释 -->
						<!-- <?php
						$attrib = 'width=32 height=32 class=""';
						$avatar = JblanceHelper::getLogo($row->user_id, $attrib);
						echo !empty($avatar) ? LinkHelper::GetProfileLink($row->user_id, $avatar) : '&nbsp;' ?> -->
						<span class="author"><i class="glyphicon glyphicon-user"></i> 发布者： <?php echo LinkHelper::GetProfileLink($row->user_id, $sellerInfo->username); ?></span>
						<span class="author"><i class="glyphicon glyphicon-map-marker"></i> <?php echo JblanceHelper::getLocationNames($row->id_location,''); ?></span>
					</div>
					<div class="row-fluid">

						<!-- 设计图上没有先注释 -->
						<!-- <div class="span6 text-right"><span><small><i class="jbf-icon-clock"></i> <?php echo JText::plural('COM_JBLANCE_N_DAYS', $row->duration); ?></small></span></div> -->
					</div>
					<div class="span6 price"><span class="boldfont">价格<?php echo JblanceHelper::formatCurrency($row->price); ?></span></div>

				</div>
			</div>
		</li>
		<?php
		} ?>
	</ul>
	</div>
	<?php
	}
	else { ?>
	<div class="alert">
		<?php echo JText::_('COM_JBLANCE_NO_SERVICE_POSTED_OR_MATCHING_YOUR_QUERY'); ?>
	</div>
	<?php
	}
	?>
    <div class="clear"></div>
    <div class="xp-pagination" style="margin-top: 56px!important;">
        <div class="serviceListTotal">共<span> <?php echo $this->pageNav->total;?> </span>条服务信息</div>
        <div class="pull-right">
            <?php echo $this->pageNav->getPagesLinks(); ?>
        </div>
    </div>
</form>
