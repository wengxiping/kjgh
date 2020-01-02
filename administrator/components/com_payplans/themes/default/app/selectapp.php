<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
if(defined('_JEXEC')===false) die();?>

<div class="pp-app-select pp-gap-bottom20">

	<div class="row-fluid pp-gap-bottom05 clearfix">
	<span class="span3">&nbsp;</span>
	<span class="span3 pull-left">
		<input  style="width:65%" name="pp-input-app" id="pp-app-search" placeholder="Search your app..">
	</span>
	 
	<span class="pull-left ">
			<?php  echo PayplansHtml::_('apptags.edit', 'filtertags', '', array('all'=>true), array());?>
		</span>
	</div>
		
		<?php foreach($apps as $key => $app) :?> 		
			<?php if($app == 'adminpay') continue; ?>
		
		<div filter="<?php echo strtolower($appdata[$app]['name']); ?>"    class="pp-appscreen row-fluid pull-left  <?php echo ' f-'.implode(' f-',array_map('ucfirst',$appdata[$app]['tags'])); ?>">
			<div class="pp-apps span7 pp-plan-description" >
					
					<div class="row-fluid">
						<div  class="span2 center pp-app-icon" >
								<img class="pp-icon" src="<?php echo PayplansHelperUtils::pathFS2URL($appdata[$app]['icon']); ?>" />
						</div>
						
						<div class="span10" >
										<h3 class="row-fluid pp-title"><?php echo JText::_($appdata[$app]['name']); ?></h3> 
										
										<div class="clearfix ">
											<span class="muted pull-left pp-app-margin hidden-phone"  >
											  <?php echo JText::_('COM_PAYPLANS_APP_VERSION');?><?php echo isset($appdata[$app]['version'])?$appdata[$app]['version']:"3.1.0";?>
												
											</span>
											 <span class="clearfix muted pp-app-margin hidden-phone pull-left">|</span>
											<span class="clearfix muted pull-left pp-app-margin pp-align-center">
												<?php if(isset($appCount[$app])): ?>
												<a href="index.php?option=com_payplans&view=app&active_tab=manageapps&active_tab_content=ppmanage&filter_payplans_app_type[]=<?php echo $app;?>">
												<?php echo JText::_('COM_PAYPLANS_APP_TOTAL_INSTANCE');?> <?php echo isset($appCount[$app])?$appCount[$app]->count:0;?></a>
												<?php else: ?>
												<?php echo JText::_('COM_PAYPLANS_APP_TOTAL_INSTANCE');?> 0
												<?php endif;?>
											</span>
									   </div>
						 </div>
					</div>	  
 
					<div class="clearfix pp-app-doc pp-font-size" >
									<span class=" pull-left pp-app-margin hidden-phone"   style="padding-left: 1.1em;"><a target="_blank" href="index.php?option=com_rbinstaller&view=item&product_tag=rbappspayplans&tmpl=component#/app/<?php echo $appdata[$app]['alias']; ?>"> <?php echo JText::_("COM_PAYPLANS_APP_OVERVIEW"); ?></a>
									
									</span>
									<span class=" muted pp-app-margin hidden-phone pull-left">|</span>
									<span class=" pull-left pp-app-margin hidden-phone" ><?php if(!empty($appdata[$app]['documentation'])):?>   
									<a target="_blank" href="<?php echo $appdata[$app]['documentation'];?>"><?php echo JText::_("COM_PAYPLANS_APP_DOCUMENTATION");?></a>
									<?php else:?> 
									<?php echo JText::_("COM_PAYPLANS_APP_DOCUMENTATION");?>
									
									<?php endif; ?>
									
								</span> 
								<span class="clearfix pull-right pp-align-center" id = "<?php echo $app;?>"onClick="payplans.url.redirect('index.php?option=com_payplans&view=app&task=edit&type=<?php echo $app; ?>');">
									<span class="btn btn-primary pp-font-size"><?php echo JText::_("COM_PAYPLANS_APP_CREATE_INSTANCE");?></span></span>		

					</div>			  		
					
			  </div>
				

		   </div>
		<?php endforeach;?>
	
		<?php foreach ($pluginData as $plugin => $data):
		
		?>
		<div filter="<?php echo strtolower($data['name']); ?>"  class="pp-appscreen row-fluid pull-left <?php echo ' f-'.implode(' f-',array_map('ucfirst',$data['tags'])); ?>">
			<div class="pp-apps span7 pp-plan-description">
					 <div class="row-fluid">
					 <div  class="span2 center pp-app-icon">
						<img class="pp-icon" src="<?php echo $data['icon']; ?>"/>
					</div>
					<div class="span10" >
						<h3 class="row-fluid pp-title"><?php echo JText::_($data['name']); ?>&nbsp;&nbsp;<span class="label label-warning"><?php echo JText::_('COM_PAYPLANS_APP_PLUGIN'); ?></span></h3>
								<div class="clearfix">
								
									<span class="muted pull-left pp-app-margin hidden-phone" >
									 <?php echo JText::_('COM_PAYPLANS_APP_VERSION');?> <?php echo isset($data['version'])?$data['version']:"3.1.0";?>
									</span>
						   </div>
				
					</div>
							   </div>
					
						<div class="clearfix pp-app-doc pp-font-size">
							
							<span class="muted pull-left pp-app-margin hidden-phone" style="padding-left: 1.1em;"><a target="_blank" href="index.php?option=com_rbinstaller&view=item&product_tag=rbappspayplans&tmpl=component#/app/<?php echo $data['alias']; ?>"> <?php echo JText::_("COM_PAYPLANS_APP_OVERVIEW"); ?></a>
							</span>
							<span class="muted  pp-app-margin hidden-phone pull-left">|</span>
							<span class="pull-left pp-app-margin hidden-phone" >
							 <?php if(!empty($data['documentation'])):?>                                                                                                       
							<a target="_blank" href="<?php echo $data['documentation']; ?>"><?php echo JText::_("COM_PAYPLANS_APP_DOCUMENTATION");?></a>
							<?php else: 
								 echo JText::_("COM_PAYPLANS_APP_DOCUMENTATION");
							endif;
							?>
							
							</span>
							
						<span class="clearfix pull-right pp-align-center" id="<?php echo $plugin;?>" onClick="payplans.url.redirect('index.php?option=com_plugins&task=plugin.edit&extension_id=<?php echo $data['extension_id']; ?>');return false;">
									<span class="btn btn-primary pp-font-size"><?php echo JText::_("COM_PAYPLANS_APP_CONFIGURE_PLUGIN");?></span></span>
				
		  </div>
		</div>			
	</div>
		<?php endforeach;?>
	<div class="row-fluid clearfix pp-no-app" style="padding-top:4em; ">
	&nbsp;</div>
	<input type="hidden" id="payplans-app-new-next" type="submit" name="appnext" value="#" />
	<input type="hidden" name="task" value="new" />
	<input type="hidden" id="type" name="type" value="#" />
</div>
<?php 
