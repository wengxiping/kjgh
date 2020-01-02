<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	26 March 2012
 * @file name	:	views/project/tmpl/showmybid.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	List of projects posted by the user (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

 JHtml::_('jquery.framework');
 JHtml::_('bootstrap.tooltip');
 JHtml::_('behavior.modal', 'a.jb-modal');

 $doc = JFactory::getDocument();
 $doc->addScript("components/com_jblance/js/simplemodal.js");
 $doc->addStyleSheet("components/com_jblance/css/simplemodal.css");
$doc->addStyleSheet("components/com_jblance/css/customer/showmybid.css");

 $model 				= $this->getModel();
 $user					= JFactory::getUser();
 $config 				= JblanceHelper::getConfig();
 $projhelp 				= JblanceHelper::get('helper.project');		// create an instance of the class ProjectHelper

 $enableEscrowPayment 	= $config->enableEscrowPayment;
 $sealProjectBids	  	= $config->sealProjectBids;

 JText::script('COM_JBLANCE_CLOSE');
 JText::script('COM_JBLANCE_YES');

 $link_deposit  = JRoute::_('index.php?option=com_jblance&view=membership&layout=depositfund', false);
?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userForm">
<!--	<div class="jbl_h3title">--><?php //echo JText::_('COM_JBLANCE_MY_BIDS'); ?><!--</div>-->
    <div class='select-user'>
        <div class='select-user-p'><?php echo JText::_('COM_JBLANCE_MY_BIDS'); ?></div>
    </div>
    <div class='partition-line'></div>

	<?php if(empty($this->rows)) : ?>
	<div class="alert alert-info">
  		<?php echo JText::_('COM_JBLANCE_NO_BIDS_YET'); ?>
	</div>
	<?php else : ?>
	<?php
	for($i=0; $i < count($this->rows); $i++){
		$row 			  = $this->rows[$i];
		$link_accept_bid  = JRoute::_('index.php?option=com_jblance&task=project.acceptbid&id='.$row->id.'&'.JSession::getFormToken().'=1');
		$link_deny_bid	  = JRoute::_('index.php?option=com_jblance&task=project.denybid&id='.$row->id.'&'.JSession::getFormToken().'=1');
		$link_retract_bid = JRoute::_('index.php?option=com_jblance&task=project.retractbid&id='.$row->id.'&'.JSession::getFormToken().'=1');
		$link_edit_bid    = JRoute::_('index.php?option=com_jblance&view=project&layout=placebid&id='.$row->project_id);
		$link_invoice 	  = JRoute::_('index.php?option=com_jblance&view=membership&layout=invoice&id='.$row->project_id.'&tmpl=component&print=1&type=project&usertype=freelancer');
		$link_pay_comp	  = JRoute::_('index.php?option=com_jblance&task=project.paymentcomplete&id='.$row->project_id.'&'.JSession::getFormToken().'=1');
		$link_progress 	  = JRoute::_('index.php?option=com_jblance&view=project&layout=projectprogress&id='.$row->id);	// id is the bid id and NOT project id
	?>
	<div class="row-fluid">
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
           <div class="select-user-container">
               <div class="span5">
                   <div>
                       <div class="select-user-h"><?php echo LinkHelper::getProjectLink($row->project_id, $row->project_title); ?></div>
                       <div class="select-user-s">
                           <?php echo JText::_('COM_JBLANCE_STATUS'); ?> :
                           <?php
                           echo $model->getLabelProjectStatus($row->proj_status);
                           ?>

                       </div>

                       <ul class="promotions" style="margin-top: 5px;">
<!--                           <li data-promotion="featured">--><?php //echo $row->proj_status,"&nbsp;",$row->status; ?><!--</li>-->
                           <?php if($row->is_featured) : ?>
                               <li data-promotion="featured"><?php echo JText::_('COM_JBLANCE_FEATURED'); ?></li>
                           <?php endif; ?>
                           <?php if($row->is_private) : ?>
                               <li data-promotion="private"><?php echo JText::_('COM_JBLANCE_PRIVATE'); ?></li>
                           <?php endif; ?>
                           <?php if($row->is_urgent) : ?>
                               <li data-promotion="urgent"><?php echo JText::_('COM_JBLANCE_URGENT'); ?></li>
                           <?php endif; ?>
                           <?php if($sealProjectBids || $row->is_sealed) : ?>
                               <li data-promotion="sealed"><?php echo JText::_('COM_JBLANCE_SEALED'); ?></li>
                           <?php endif; ?>
                           <?php if($row->is_nda) : ?>
                               <li data-promotion="nda"><?php echo JText::_('COM_JBLANCE_NDA'); ?></li>
                           <?php endif; ?>
                       </ul>
                   </div>
                   <div  style="margin-top: 16px;">
                       <?php echo JText::_('COM_JBLANCE_BIDS'); ?> : <span class="font16 boldfont"><?php echo JblanceHelper::formatCurrency($row->amount, true, false, 0); ?></span><?php echo ($row->project_type == 'COM_JBLANCE_HOURLY') ? ' / '.JText::_('COM_JBLANCE_HR') : ''; ?><br>
                       <?php if(!empty($row->status)){?><?php echo JText::_('COM_JBLANCE_BID_STATUS'); ?> : <span class="label label-success"><?php echo JText::_($row->status); ?></span><br><?php } ?>
                   </div>
               </div>
               <div class="span7 text-center">

                   <div class="action-bar">
                       <div class='need-accept-container'>
                       <?php
                     //  echo$row->assigned_userid ,$user->id,$row->status;
                    //   die;
                       if($row->assigned_userid == $user->id){//如果当前需求

                           if($row->status == ''){ ?>

                                   <div style='margin-right:80px'>
                                       <img src="/components/com_jblance/images/need/Winning_the_bid.png" alt=""
                                            style='width:80px;height:80px'>
                                   </div>

                                   <div class="submit-commit-btn">
                                       <div class="xp-btn"><a href="javascript:void(0);" class="btn btn-success btn-small" onclick="javascript:modalConfirm('<?php echo JText::_('COM_JBLANCE_ACCEPT'); ?>', '<?php echo JText::_('COM_JBLANCE_CONFIRM_ACCEPT_BID_NO_FEE', true); ?>', '<?php echo $link_accept_bid; ?>');" ><?php echo JText::_('COM_JBLANCE_ACCEPT'); ?></a></div>
                                       <div class="xp-btn"><a href="javascript:void(0);" class="btn btn-danger btn-small" onclick="javascript:modalConfirm('<?php echo JText::_('COM_JBLANCE_DENY'); ?>', '<?php echo JText::_('COM_JBLANCE_CONFIRM_DENY_BID'); ?>', '<?php echo $link_deny_bid; ?>');" ><?php echo JText::_('COM_JBLANCE_DENY'); ?></a></div>
                                   </div>
                               <?php
                           }
                           elseif($row->status == 'COM_JBLANCE_ACCEPTED'){
                               $hasRated = $model->hasRated($row->project_id, $user->id);
                               if($row->p_status == 'COM_JBLANCE_COMPLETED' && !$hasRated){
                                   $link_rate = JRoute::_('index.php?option=com_jblance&view=project&layout=rateuser&id='.$row->project_id); ?>
                           <div class="submit-commit-btn">
                               <div class="xp-btn">
                                   <a href="<?php echo $link_rate; ?>" class="btn btn-primary btn-small">评价卖家</a>
                               </div>
                           </div>
                               <?php
                               }elseif($row->p_status == '' || $row->p_status == 'COM_JBLANCE_IN_PROGRESS'){//接受状态
                               ?>
                               <div class="progress progress-success progress-striped" title="<?php echo JText::_('COM_JBLANCE_PROGRESS').' : '.$row->p_percent.'%'; ?>" style="margin: 0px auto; float: none; width:50%">
                                   <div class="bar" style="width: <?php echo $row->p_percent; ?>%"></div>
                                   <div class="progress-title" style="width: 100%;height: 14px;text-align: center;font-size: 10px;"><?php echo $row->p_percent.'%'; ?></div>
                               </div>
                               <div class="progress-button"><a href="<?php echo $link_progress; ?>" class="btn btn-primary btn-small"><?php echo JText::_('COM_JBLANCE_UPDATE_PROGRESS'); ?></a></div>
                               <?php
                               }
                           }
                       }
                       elseif($row->proj_status == 'COM_JBLANCE_OPEN') { ?>
                           <div class="submit-commit-btn">
                               <div class="xp-btn">
                           <a href="javascript:void(0);" class="btn btn-danger btn-small" onclick="javascript:modalConfirm('<?php echo JText::_('COM_JBLANCE_RETRACT_BID'); ?>', '<?php echo JText::_('COM_JBLANCE_CONFIRM_RETRACT_BID'); ?>', '<?php echo $link_retract_bid; ?>');" ><?php echo JText::_('COM_JBLANCE_RETRACT_BID'); ?></a>
                               </div>
                               <div class="xp-btn">
                                   <a href="<?php echo $link_edit_bid; ?>" class="btn btn-primary btn-small"><?php echo JText::_('COM_JBLANCE_EDIT_BID'); ?></a>
                               </div>
                           </div>
                                   <?php
                       }
                       ?>
                       <!-- show the print invoice if the commission is > 0 and status is accepted -->
                       <?php if(($row->lancer_commission > 0) && ($row->status == 'COM_JBLANCE_ACCEPTED')){ ?>
                           <a rel="{handler: 'iframe', size: {x: 650, y: 500}}" href="<?php echo $link_invoice; ?>" class="jb-modal btn btn-success btn-small"><?php echo JText::_('COM_JBLANCE_PRINT_INVOICE'); ?></a>
                       <?php } ?>


                       <?php if($row->status == 'COM_JBLANCE_ACCEPTED'){ ?>
                       <?php if($row->proj_status=='COM_JBLANCE_OPEN'){?>
<!--                       <span class="label label-success">--><?php //echo (!empty($row->p_status)) ? JText::_($row->p_status) : JText::_('COM_JBLANCE_NOT_YET_STARTED'); ?><!--</span><div class="sp10">&nbsp;</div>-->
                       <div class="progress progress-success progress-striped" title="<?php echo JText::_('COM_JBLANCE_PROGRESS').' : '.$row->p_percent.'%'; ?>" style="margin: 0px auto; float: none; width:50%">
                           <div class="bar" style="width: <?php echo $row->p_percent; ?>%"></div>
                           <div class="progress-title" style="width: 100%;height: 14px;text-align: center;font-size: 10px;"><?php echo $row->p_percent.'%'; ?></div>
                       </div>
<!--                       <div class="sp10">&nbsp;</div>-->
                       <div class="progress-button"><a href="<?php echo $link_progress; ?>" class="btn btn-primary btn-small"><?php echo JText::_('COM_JBLANCE_UPDATE_PROGRESS'); ?></a></div>
<!--                       <div class="sp10">&nbsp;</div>-->

                       <div class="small payment-status" style="display: none;">
                           <?php if($enableEscrowPayment) { ?>
                               <?php
                               if($row->status == 'COM_JBLANCE_ACCEPTED' && $row->project_type == 'COM_JBLANCE_FIXED'){
                                   $perc = ($row->paid_amt/$row->amount)*100; $perc = round($perc, 2).'%'; ?>
                                   <div class="progress progress-success progress-striped" title="<?php echo JText::_('COM_JBLANCE_PAYMENT_STATUS').' : '.$perc; ?>" style="margin: 0px auto; float: none; width:50%">
                                       <div class="bar" style="width: <?php echo $perc; ?>"></div>
                                       <div class="progress-title"><?php echo JText::_('COM_JBLANCE_PAYMENT_STATUS').' : '.$perc; ?></div>
                                   </div>
                                   <?php
                               }
                               elseif($row->status == 'COM_JBLANCE_ACCEPTED' && $row->project_type == 'COM_JBLANCE_HOURLY'){
                                   if($row->paid_status == 'COM_JBLANCE_PYMT_PARTIAL'){ ?>
                                       <a href="javascript:void(0);" class="btn btn-primary btn-small" onclick="javascript:modalConfirm('<?php echo JText::_('COM_JBLANCE_PAYMENT_COMPLETE'); ?>', '<?php echo JText::_('COM_JBLANCE_CONFIRM_PAYMENT_COMPLETE'); ?>', '<?php echo $link_pay_comp; ?>');" ><?php echo JText::_('COM_JBLANCE_PAYMENT_COMPLETE'); ?></a>
                                       <?php
                                   }
                                   if($row->paid_status == 'COM_JBLANCE_PYMT_COMPLETE'){ ?>
                                       <div class="progress progress-success progress-striped" title="<?php echo JText::_('COM_JBLANCE_PAYMENT_STATUS').' : '.'100%'; ?>" style="margin: 0px auto; float: none; width:50%">
                                           <div class="bar" style="width: <?php echo '100%'; ?>"></div>
                                       </div>
                                       <?php
                                   }
                                   ?>
                                   <?php
                               }
                               ?>
                           <?php } ?>
                       </div>

                          <?php }else{?>
<!--                           <div class="progress-button"><a href="--><?php //echo $link_progress; ?><!--" class="btn btn-primary btn-small">查看</a></div>-->
                   <?php }}?>
                       <?php if($row->status == 'COM_JBLANCE_DENIED'){?>
                           <div class="progress-button"><a href="<?php echo $link_progress; ?>" class="btn btn-primary btn-small">查看</a></div>
                       <?php }?>
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
        <div class="clear"></div>
        <div class="xp-pagination" style="margin-top: 56px!important;">
            <div class="serviceListTotal">共<span> <?php echo $this->pageNav->total; ?> </span>条服务信息</div>
            <div class="pull-right">
                <?php echo $this->pageNav->getPagesLinks(); ?>
            </div>
        </div>
	<?php endif; ?>
</form>
