<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	26 March 2012
 * @file name	:	views/project/tmpl/listproject.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Shows list of projects (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

 $model 		  = $this->getModel();
 $user			  = JFactory::getUser();
 $now 		 	  = JFactory::getDate();
 $config 		  = JblanceHelper::getConfig();
 $currencycode 	  = $config->currencyCode;
 $dformat 		  = $config->dateFormat;
 $showUsername 	  = $config->showUsername;
 $sealProjectBids = $config->sealProjectBids;
$doc 	 = JFactory::getDocument();
$doc->addStyleSheet("components/com_jblance/css/customer/service.css");
$doc->addStyleSheet("components/com_jblance/css/customer/new_service.css");
 $nameOrUsername = ($showUsername) ? 'username' : 'name';

 $action	= JRoute::_('index.php?option=com_jblance&view=project&layout=listproject');
 $link_search	= JRoute::_('index.php?option=com_jblance&view=project&layout=searchproject');

 $projHelper = JblanceHelper::get('helper.project');		// create an instance of the class ProjectHelper
 $userHelper = JblanceHelper::get('helper.user');		// create an instance of the class UserHelper
?>
<form action="<?php echo $action; ?>" method="post" name="userForm">
<!--	<a href="--><?php //echo $link_search; ?><!--" class="pull-right btn btn-primary">--><?php //echo JText::_('COM_JBLANCE_SEARCH_PROJECTS'); ?><!--</a>-->
<!--	<div class="sp10">&nbsp;</div>-->
	<div class="jbl_h3title" style="display: flex;justify-content: flex-start;align-items: center;"><?php echo $this->escape($this->params->get('page_heading', JText::_('COM_JBLANCE_LIST_OF_PROJECTS'))); ?></div>
    <div class="row-fluid">
        <div class="thumbnails">
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
                <div class="title_container"><?php echo LinkHelper::getProjectLink($row->id, $row->project_title); ?>
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
                echo !empty($avatar) ? LinkHelper::GetProfileLink($row->publisher_userid, $avatar) : '&nbsp;' ?>
            </div>

            <div class="category">
                <?php echo JblanceHelper::getCategoryNames($row->id_category, 'myself-category', 'project'); ?>
            </div>
            <div class="caption">
                <div class="">
                    <span class="author"><i class="glyphicon glyphicon-user"></i> <?php echo JText::_('COM_JBLANCE_POSTED_BY'); ?>：  <?php echo LinkHelper::GetProfileLink($row->publisher_userid, $buyer->$nameOrUsername); ?></span>
                    <span class="author"><i class="glyphicon glyphicon-map-marker"></i> <?php echo JblanceHelper::getLocationNames($row->id_location,'only-location'); ?></span>
                </div>
                <div class="span6 price"><span class="boldfont">价格<?php echo JblanceHelper::formatCurrency($avg, true, false, 0); ?></span></div>
            </div>
            </div>
		<?php if($row->is_private_invite) : ?>
		<div class="row-fluid">
			<div class="span12">
				<p class="alert alert-info"><?php echo JText::_('COM_JBLANCE_THIS_IS_A_PRIVATE_INVITE_PROJECT_VISIBLE_TO_OWNER_INVITEES'); ?></p>
			</div>
		</div>

		<?php endif; ?>
	</div>
	<?php
	}
	?>
        <?php if(!count($this->rows)){ ?>
        <div class="alert alert-info">
            <?php echo JText::_('COM_JBLANCE_NO_PROJECT_POSTED'); ?>
        </div>
        <?php } ?>
    </div></div>
    <div class="clear"></div>
    <div class="xp-pagination" style="margin-top: 56px!important;">
        <div class="serviceListTotal">共<span> <?php echo $this->pageNav->total;?> </span>条服务信息</div>
        <div class="pull-right">
            <?php echo $this->pageNav->getPagesLinks(); ?>
        </div>
    </div>
	<?php
	$link_rss = JRoute::_('index.php?option=com_jblance&view=project&format=feed');
	$rssvisible = (!$config->showRss) ? 'style=display:none' : '';
	?>
	<div class="jbrss" <?php echo $rssvisible; ?>>
		<div id="showrss" class="pull-right">
			<a href="<?php echo $link_rss; ?>" target="_blank">
				<img src="components/com_jblance/images/rss.png" alt="RSS" title="<?php echo JText::_('COM_JBLANCE_RSS_IMG_ALT'); ?>">
			</a>
		</div>
	</div>
	<input type="hidden" name="option" value="com_jblance" />
	<input type="hidden" name="task" value="" />

</form>
