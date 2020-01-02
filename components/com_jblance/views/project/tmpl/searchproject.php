<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	28 March 2012
 * @file name	:	views/project/tmpl/searchproject.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Search projects (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

 use Joomla\Utilities\ArrayHelper;

 JHtml::_('jquery.framework');
 JHtml::_('bootstrap.tooltip');
 JHtml::_('formbehavior.chosen', '.advancedSelect');
 JHtml::_('formbehavior.chosen', '#id_categ.advancedSelectCateg', null, array('placeholder_text_multiple'=>JText::_('COM_JBLANCE_FILTER_PROJECT_BY_SKILLS')));

 $doc 	 = JFactory::getDocument();
 $doc->addScript("components/com_jblance/js/btngroup.js");
 $doc->addScript("components/com_jblance/js/bootstrap-slider.js");
 $doc->addStyleSheet("components/com_jblance/css/slider.css");
$doc->addStyleSheet("components/com_jblance/css/customer/service.css");
$doc->addStyleSheet("components/com_jblance/css/customer/new_service.css");
//$doc->addStyleSheet("components/com_jblance/css/customer/service.css");
 $app  		 = JFactory::getApplication();
 $user		 = JFactory::getUser();
 $model 	 = $this->getModel();
 $now 		 = JFactory::getDate();
 $projHelper = JblanceHelper::get('helper.project');		// create an instance of the class ProjectHelper
 $select 	 = JblanceHelper::get('helper.select');		// create an instance of the class SelectHelper
 $userHelper = JblanceHelper::get('helper.user');		// create an instance of the class UserHelper

 $keyword	  = $this->state->get('keyword');
 $phrase	  = $this->state->get('phrase');
 $id_categ	  = $this->state->get('id_categ');
 $id_location = $this->state->get('id_location');
 $proj_type	  = $this->state->get('project_type');
 $budget 	  = $this->state->get('budget');
 $status	  = $this->state->get('status');

 $id_categ 	  = ArrayHelper::toInteger($id_categ);
 $id_location = ArrayHelper::toInteger($id_location);

 $config 		  = JblanceHelper::getConfig();
 $currencysym 	  = $config->currencySymbol;
 $currencycode 	  = $config->currencyCode;
 $dformat 		  = $config->dateFormat;
 $sealProjectBids = $config->sealProjectBids;
 $showUsername 	  = $config->showUsername;

 $nameOrUsername = ($showUsername) ? 'username' : 'name';

 $action = JRoute::_('index.php?option=com_jblance&view=project&layout=searchproject');
?>
<script type="text/javascript">
<!--
jQuery(document).ready(function($){
	$("#budget").sliderz({});

});
//-->
</script>
<form action="<?php echo $action; ?>" method="get" name="userFormJob" enctype="multipart/form-data">


		<div class="row-fluid">
            <div class="thumbnails">
      		<!--Body content-->
      			<?php
				for ($i=0, $x=count($this->rows); $i < $x; $i++){
					$row = $this->rows[$i];
					$buyer = $userHelper->getUser($row->publisher_userid);
					$daydiff = $row->daydiff;

					if($daydiff == -1){
						$startdate = JText::_('COM_JBLANCE_YESTERDAY');
					}
					elseif($daydiff == 0){
						$startdate = JText::_('COM_JBLANCE_TODAY');
					}
					else {
						$startdate =  JHtml::_('date', $row->start_date, $dformat, true);
					}

					// calculate expire date and check if expired
					$expiredate = JFactory::getDate($row->start_date);
					$expiredate->modify("+$row->expires days");
					$isExpired = ($now > $expiredate) ? true : false;

					/* if($isExpired)
						$statusLabel = 'label';
					else */if($row->status == 'COM_JBLANCE_OPEN')
						$statusLabel = 'label label-success';
					elseif($row->status == 'COM_JBLANCE_FROZEN')
						$statusLabel = 'label label-warning';
					elseif($row->status == 'COM_JBLANCE_CLOSED')
						$statusLabel = 'label label-important';
					elseif($row->status == 'COM_JBLANCE_EXPIRED')
						$statusLabel = 'label';

					$bidsCount = $model->countBids($row->id);

					//calculate average bid
					$avg = $projHelper->averageBidAmt($row->id);
					$avg = round($avg, 0);

					// 'private invite' project shall be visible only to invitees and project owner
					$isMine = ($row->publisher_userid == $user->id);
					if($row->is_private_invite){
						$invite_ids = explode(',', $row->invite_user_id);
						if(!in_array($user->id, $invite_ids) && !$isMine)
							continue;
					}
				?>
				<div class="span3 thumbfix">
                    <div class="thumbnail">
                            <div class="title_container">
                                <?php echo LinkHelper::getProjectLink($row->id, $row->project_title); ?>
                            </div>
                            <div class="item-info">
                                <div><?php echo JText::_($row->status); ?></div>
                                <div> <?php echo JText::_('COM_JBLANCE_BIDS'); ?> :
                                    <?php if($sealProjectBids || $row->is_sealed) : ?>
                                        <span class=""><?php echo JText::_('COM_JBLANCE_SEALED'); ?></span>
                                    <?php else : ?>
                                        <span class=""><?php echo $bidsCount; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="jbf-image">

                                <?php
                                $attrib = 'width=56 height=56 class="img-polaroid"';
                                $avatar = JblanceHelper::getLogo($row->publisher_userid, $attrib);
//                                echo !empty($avatar) ? LinkHelper::GetProfileLink($row->publisher_userid, $avatar) : '&nbsp;'
                                ?>
                                <div class="search-img"><?php echo $avatar;?></div>
                            </div>
                            <div class="category">
                                <?php echo JblanceHelper::getCategoryNames($row->id_category, 'myself-category', 'project'); ?>
                            </div>


                        <div class="caption">
                            <div class="">
                                <span class="author"><i class="glyphicon glyphicon-user"></i> <?php echo JText::_('COM_JBLANCE_POSTED_BY'); ?>：  <?php echo LinkHelper::GetProfileLink($row->publisher_userid, $buyer->$nameOrUsername); ?></span>
                                <span class="author"><i class="glyphicon glyphicon-map-marker"></i> <?php echo JblanceHelper::getLocationNames($row->id_location,''); ?></span>
                            </div>
                            <div class="row-fluid">
                                <!-- 设计图上没有先注释 -->
                                <!-- <div class="span6 text-right"><span><small><i class="jbf-icon-clock"></i> <?php echo JText::plural('COM_JBLANCE_N_DAYS', $row->duration); ?></small></span></div> -->
                            </div>
                            <div class="span6 price"><span class="boldfont">价格<?php echo JblanceHelper::formatCurrency($avg, true, false, 0); ?></span></div>

                        </div>

<!--                            <div class="font14">-->
<!--                                <strong>--><?php //echo JText::_('COM_JBLANCE_POSTED_BY'); ?><!--</strong>: --><?php //echo LinkHelper::GetProfileLink($row->publisher_userid, $buyer->$nameOrUsername); ?>
<!--                            </div>-->

<!--                            <div class="font14">-->
<!--                                <strong>--><?php //echo JText::_('COM_JBLANCE_LOCATION'); ?><!--</strong>: <span class="">--><?php //echo JblanceHelper::getLocationNames($row->id_location,'only-location'); ?><!--</span>-->
<!--                            </div>-->
<!--                            <div class="font14">--><?php //echo JblanceHelper::formatCurrency($avg, true, false, 0); ?><!--</div>-->
<!--                            <ul class="promotions">-->
<!--                                --><?php //if($row->is_featured) : ?>
<!--                                <li data-promotion="featured">--><?php //echo JText::_('COM_JBLANCE_FEATURED'); ?><!--</li>-->
<!--                                --><?php //endif; ?>
<!--                                --><?php //if($row->is_private) : ?>
<!--                                <li data-promotion="private">--><?php //echo JText::_('COM_JBLANCE_PRIVATE'); ?><!--</li>-->
<!--                                --><?php //endif; ?>
<!--                                --><?php //if($row->is_urgent) : ?>
<!--                                <li data-promotion="urgent">--><?php //echo JText::_('COM_JBLANCE_URGENT'); ?><!--</li>-->
<!--                                --><?php //endif; ?>
<!--                                --><?php //if($sealProjectBids || $row->is_sealed) : ?>
<!--                                <li data-promotion="sealed">--><?php //echo JText::_('COM_JBLANCE_SEALED'); ?><!--</li>-->
<!--                                --><?php //endif; ?>
<!--                                --><?php //if($row->is_nda) : ?>
<!--                                <li data-promotion="nda">--><?php //echo JText::_('COM_JBLANCE_NDA'); ?><!--</li>-->
<!--                                --><?php //endif; ?>
<!--                            </ul>-->

<!--                        <div class="average-price">-->
<!--                            <div class="bid_project_left text-center">-->
<!--                                <div>--><?php //echo JText::_('COM_JBLANCE_AVG_BID'); ?><!--</div>-->
<!--                                --><?php //if($sealProjectBids || $row->is_sealed) : ?>
<!--                                <span class="label label-info">--><?php //echo JText::_('COM_JBLANCE_SEALED'); ?><!--</span>-->
<!--                                --><?php //else : ?>
<!--                                <span class="font16 boldfont">--><?php //echo JblanceHelper::formatCurrency($avg, true, false, 0); ?><!--</span>--><?php //echo ($row->project_type == 'COM_JBLANCE_HOURLY') ? ' / '.JText::_('COM_JBLANCE_HR') : ''; ?>
<!--                                --><?php //endif; ?>
<!--                            </div>-->
<!--                        </div>-->
                    </div>
				</div>
<!--				<div class="lineseparator"></div>-->
				<?php
				}
				?>
				<?php if(!count($this->rows)){ ?>
				<div class="alert alert-info">
					<?php echo JText::_('COM_JBLANCE_NO_MATCHING_RESULTS_FOUND'); ?>
				</div>
				<?php } ?>

                <div class="clear"></div>
                <div class="xp-pagination" style="margin-top: 56px!important;">
                    <div class="serviceListTotal">共<span> <?php echo $this->pageNav->total;?> </span>条服务信息</div>
                    <div class="pull-right">
                        <?php echo $this->pageNav->getPagesLinks(); ?>
                    </div>
                </div>
            </div>
		</div>


	<input type="hidden" name="option" value="com_jblance" />
	<input type="hidden" name="view" value="project" />
	<input type="hidden" name="layout" value="searchproject" />
	<input type="hidden" name="task" value="" />
</form>
