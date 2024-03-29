<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	28 January 2015
 * @file name	:	modules/mod_jblanceservice/tmpl/default.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 // no direct access
 defined('_JEXEC') or die('Restricted access');
 
 JHtml::_('jquery.framework');
 JHtml::_('bootstrap.framework');
 JHtml::_('bootstrap.carousel');
 
 $set_Itemid	= intval($params->get('set_itemid', 101));
 $Itemid = ($set_Itemid > 101) ? '&Itemid='.$set_Itemid : '';

 $config 		  = JblanceHelper::getConfig();
 $showUsername 	  = $config->showUsername;
 
 $nameOrUsername = ($showUsername) ? 'username' : 'name';

 $document = JFactory::getDocument();
 $direction = $document->getDirection();
 $document->addStyleSheet("components/com_jblance/css/style.css");
 $document->addStyleSheet("modules/mod_jblanceservice/css/style.css");
 if($direction === 'rtl')
 	$document->addStyleSheet("components/com_jblance/css/style-rtl.css");
 
 if($config->loadBootstrap){
 	JHtml::_('bootstrap.loadCss', true, $direction);
 }

 $link_listproject = JRoute::_('index.php?option=com_jblance&view=project&layout=listproject'.$Itemid); 

 $lang = JFactory::getLanguage();
 $lang->load('com_jblance', JPATH_SITE);
 
 $totalServices = count($rows);
 $servicesPerSlide = 4;
 $totalSlides = ceil($totalServices / $servicesPerSlide);
?>
<script type="text/javascript">
<!--
jQuery(document).ready(function($){
	$("#myCarousel").carousel({
	interval: '<?php echo $scroll_interval; ?>'
	})
});
//-->
</script>
<?php if($totalServices > 0) : ?>
<div class="row-fluid">
	<div class="span12">
		<div class="well white">
			<div id="myCarousel" class="carousel slide">
				<ol class="carousel-indicators">
				<?php 
				for($a=0; $a < $totalSlides; $a++){ 
					$active = ($a == 0) ? 'active' : ''; ?>
					<li data-target="#myCarousel" data-slide-to="<?php echo $a; ?>" class="<?php echo $active; ?>"></li>
				<?php 
				} ?>
				</ol>
    			<!-- Carousel items -->
				<div class="carousel-inner">
				<?php 
				for($k=0; $k < $totalSlides; $k++){
					$active = ($k == 0) ? 'active' : ''; ?>
					<div class="item <?php echo $active; ?>">
						<div class="row-fluid">
						<?php 
						$maxLimit = $totalServices;
						
						if(($k+1)*$servicesPerSlide < $totalServices)
							$maxLimit = ($k+1)*$servicesPerSlide;
						else 
							$maxLimit = $totalServices;
						
						for($i=$k*$servicesPerSlide; $i<$maxLimit; $i++){
							$row = $rows[$i];
							$attachments = JBMediaHelper::processAttachment($row->attachment, 'service');		//from the list, show the first image
							$link_view	= JRoute::_('index.php?option=com_jblance&view=service&layout=viewservice&id='.$row->id.$Itemid);
							$sellerInfo = JFactory::getUser($row->user_id);
						?>						
							<div class="span3" style="margin-bottom: 10px;">
								<div class="thumbnail">
									<a href="<?php echo $link_view; ?>">
										<div class="jbf-image" style="background-image: url('<?php echo $attachments[0]['location']; ?>');"></div>
									</a>
									<div class="caption">
										<div class="row-fluid">
											<div class="span6"><span class="boldfont"><?php echo JblanceHelper::formatCurrency($row->price); ?></span></div>
											<div class="span6 text-right"><span><small><i class="jbf-icon-clock"></i> <?php echo JText::plural('COM_JBLANCE_N_DAYS', $row->duration); ?></small></span></div>
										</div>
										<div class="title_container"><a href="<?php echo $link_view; ?>"><?php echo $row->service_title; ?></a></div>
										<div class="">
											<?php
											$attrib = 'width=32 height=32 class=""';
											$avatar = JblanceHelper::getLogo($row->user_id, $attrib);
											echo !empty($avatar) ? LinkHelper::GetProfileLink($row->user_id, $avatar) : '&nbsp;' ?>
											<span><?php echo LinkHelper::GetProfileLink($row->user_id, $sellerInfo->username); ?></span>
										</div>
									</div>
								</div>
							</div>
						<?php } ?>
						</div><!--/row-fluid-->
					</div><!--/item-->
				<?php 
				}
				?>
				</div><!--/carousel-inner-->
				<a class="left carousel-control" href="#myCarousel" data-slide="prev">‹</a>
				<a class="right carousel-control" href="#myCarousel" data-slide="next">›</a>
			</div><!--/myCarousel-->
		</div><!--/well-->
	</div>
</div>
<?php else : ?>
<div class="alert alert-info">
	<?php echo JText::_('MOD_JBLANCE_NO_SERVICE_POSTED'); ?>
</div>
<?php endif; ?>
