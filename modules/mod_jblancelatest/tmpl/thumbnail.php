<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	29 March 2012
 * @file name	:	modules/mod_jblancelatest/tmpl/default.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 // no direct access
 defined('_JEXEC') or die('Restricted access');
 
 $show_logo = intval($params->get('show_logo', 1));
 $set_Itemid	= intval($params->get('set_itemid', 0));
 $Itemid = ($set_Itemid > 0) ? '&Itemid='.$set_Itemid : '';
 
 $n = 0;
 if(!empty($rows)) {
    $n = count($rows);
 }

 $user			  = JFactory::getUser();
 $config 		  = JblanceHelper::getConfig();
 $currencycod 	  = $config->currencyCode;
 $dformat 		  = $config->dateFormat;
 $showUsername 	  = $config->showUsername;
 $sealProjectBids = $config->sealProjectBids;
 
 $nameOrUsername = ($showUsername) ? 'username' : 'name';

 $document = JFactory::getDocument();
 $direction = $document->getDirection();
 $document->addStyleSheet("components/com_jblance/css/style.css");
 $document->addStyleSheet("modules/mod_jblancecategory/css/style.css");
 if($direction === 'rtl')
 	$document->addStyleSheet("components/com_jblance/css/style-rtl.css");
 
 if($config->loadBootstrap){
 	JHtml::_('bootstrap.loadCss', true, $direction);
 }

 $link_listproject = JRoute::_('index.php?option=com_jblance&view=project&layout=listproject'.$Itemid); 
 
 $projHelper = JblanceHelper::get('helper.project');		// create an instance of the class ProjectHelper

 $lang = JFactory::getLanguage();
 $lang->load('com_jblance', JPATH_SITE);
?>
<?php 
if($n){ ?>
<div class="row-fluid">
<ul class="thumbnails">
	<?php 
	for($i=0; $i < $n; $i++){
		$row = $rows[$i];
		$attachments = JBMediaHelper::processAttachment($row->project_image, 'project');		//from the list, show the first image
		$buyer = JFactory::getUser($row->publisher_userid);
		
		$expiredate = JFactory::getDate($row->start_date);
		$expiredate->modify("+$row->expires days");
		
		$link_proj_detail	= JRoute::_('index.php?option=com_jblance&view=project&layout=detailproject&id='.$row->id.$Itemid);
	?>
	<li class="span3 thumbfix">
		<div class="thumbnail">
			<a href="<?php echo $link_proj_detail; ?>">
				<div class="jbf-image" style="background-image: url('<?php echo $attachments[0]['location']; ?>');"></div>
			</a>
			<div class="caption">
				<div class="row-fluid">
					<div class="span6">
						<?php 
						$avg = $projHelper->averageBidAmt($row->id);
						$avg = round($avg, 0);
						?>
					
						<span class="boldfont" title="<?php echo JText::_('COM_JBLANCE_AVG_BID'); ?>">
							<?php if($sealProjectBids || $row->is_sealed) : ?>
				  				-
				  			<?php else : ?>
				  				<?php echo JblanceHelper::formatCurrency($avg, true, false, 0); ?><span class="font12"><?php echo ($row->project_type == 'COM_JBLANCE_HOURLY') ? ' / '.JText::_('COM_JBLANCE_HR') : ''; ?></span>
				  			<?php endif; ?>
						</span>
					</div>
					<div class="span6 text-right">
						<span title="<?php echo JText::_('COM_JBLANCE_ENDS'); ?>"><small><i class="jbf-icon-clock"></i> <?php echo JblanceHelper::showRemainingDHM($expiredate, 'SHORT', 'COM_JBLANCE_PROJECT_EXPIRED_SHORT'); ?></small></span>
					</div>
				</div>
				<div class="title_container"><a href="<?php echo $link_proj_detail; ?>"><?php echo $row->project_title; ?></a></div>
				<div class="">
					<?php
					$attrib = 'width=32 height=32 class=""';
					$avatar = JblanceHelper::getLogo($row->publisher_userid, $attrib);
					echo !empty($avatar) ? LinkHelper::GetProfileLink($row->publisher_userid, $avatar) : '&nbsp;' ?>
					<span><?php echo LinkHelper::GetProfileLink($row->publisher_userid, $buyer->username); ?></span>
				</div>
			</div>
		</div>
	</li>
	<?php
	} ?>
</ul>
</div>
<p class="text-center">
	<a href="<?php echo $link_listproject; ?>" class="btn btn-primary"><?php echo JText::_('MOD_JBLANCE_MORE_PROJECTS'); ?></a>
</p>
<?php	
}
else { ?>
<div class="alert alert-info">
	<?php echo JText::_('COM_JBLANCE_NO_PROJECT_POSTED'); ?>
</div>
<?php 
}
?>
