<?php 
/** 
 * @package JREALTIMEANALYTICS::CPANEL::administrator::components::com_com_jrealtimeanalytics
 * @subpackage views
 * @subpackage cpanel
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
?>
<!-- CPANEL ICONS -->

<div class="row no-margin">
	<div class="accordion-group span5">
		<div class="accordion-heading opened">
			<div class="accordion-toggle accordion_lightblue noaccordion">
				<h3><span class="icon-pencil"></span><?php echo JText::_('COM_JREALTIME_CPANEL_TASKS' ); ?></h3>
			</div>
		</div>
		<div id="placeholder_cpanelicons" class="accordion-body accordion-inner collapse in">
			<div id="cpanel">
				<div id="updatestatus">
					<?php 
					if(is_object($this->updatesData)) {
						if(version_compare($this->updatesData->latest, $this->currentVersion, '>')) { 
							$updatesACLClass = JFactory::getUser()->authorise('core.manage', 'com_installer') ? 'label-important' : 'label-warning';?>
							<a href="http://storejextensions.org/extensions/jrealtime_analytics.html" target="_blank" alt="storejoomla link">
								<label data-content="<?php echo JText::sprintf('COM_JREALTIME_GET_LATEST', $this->currentVersion, $this->updatesData->latest, $this->updatesData->relevance);?>" class="label <?php echo $updatesACLClass;?> hasPopover">
									<span class="icon-warning"></span>
									<?php echo JText::sprintf('COM_JREALTIME_OUTDATED', $this->updatesData->latest);?>
								</label>
							</a>
						<?php } else { ?>
							<label data-content="<?php echo JText::sprintf('COM_JREALTIME_YOUHAVE_LATEST', $this->currentVersion);?>" class="label label-success hasPopover">
								<span class="icon-checkmark"></span>
								<?php echo JText::sprintf('COM_JREALTIME_UPTODATE', $this->updatesData->latest);?>
							</label>	
						<?php }
					}
					?>
				</div>
				<?php echo $this->icons; ?>
			</div>
		</div>
	</div>
	
	<div class="accordion span7" id="jrealtime_accordion_cpanel">
		<div class="accordion-group">
	    	<div class="accordion-heading">
	    		<div class="accordion-toggle" data-toggle="collapse" data-parent="#jrealtime_accordion_cpanel" href="#jrealtime_stats">
		      		<h4 class="accordion-title">
		      			<span class="icon-chart"></span>
		      			<?php echo JText::sprintf('COM_JREALTIME_CPANEL_STATS', $this->cParams->get('cpanelstats_period_interval', 'week')); ?>
	      			</h4>
	      		</div>
	    	</div>
	    	
			<div id="jrealtime_stats" class="accordion-body collapse">
				<div class="accordion-inner">
					<div class="statslabel_container">
						<div class="statslabel blue">
							<span class="statslabel_value icon-users"><?php echo $this->infoData['chart_period_canvas'] ['totalusers'];?></span>
							<span class="statslabel_text"><?php echo JText::_('COM_JREALTIME_TOTALUSERS_CHART');?></span>
						</div>
						<div class="statslabel green">
							<span class="statslabel_value icon-copy"><?php echo $this->infoData['chart_period_canvas'] ['totalpages'];?></span>
							<span class="statslabel_text"><?php echo JText::_('COM_JREALTIME_TOTALPAGES_CHART');?></span>
						</div>
					</div>
					<div class="chart_container">
						<canvas data-bind="{chart_period_canvas}"></canvas>
					</div>
					
					<div class="statslabel_container">
						<div class="statslabel yellow">
							<span class="statslabel_value icon-key"><?php echo $this->infoData['chart_generic_canvas'] ['systemusers'];?></span>
							<span class="statslabel_text"><?php echo JText::_('COM_JREALTIME_SYSTEMUSERS_CHART');?></span>
						</div>
						<div class="statslabel red">
							<span class="statslabel_value icon-lightning"><?php echo $this->infoData['chart_generic_canvas'] ['systemevents'];?></span>
							<span class="statslabel_text"><?php echo JText::_('COM_JREALTIME_SYSTEMEVENTS_CHART');?></span>
						</div>
					</div>
					<div class="chart_container">
						<canvas data-bind="{chart_generic_canvas}"></canvas>
					</div>
				</div>
			</div>
		</div>
		
		<div class="accordion-group">
		    <div class="accordion-heading">
				<div class="accordion-toggle" data-toggle="collapse" data-parent="#jrealtime_accordion_cpanel" href="#jrealtime_status">
					<h4 class="accordion-title">
						<span class="icon-help"></span>
						<?php echo JText::_('COM_JREALTIME_ABOUT');?>
					</h4>
		      	</div>
	    	</div>
		    <div id="jrealtime_status" class="accordion-body collapse">
		 		<div class="accordion-inner">
					<div class="single_container">
				 		<label class="label label-warning"><?php echo JText::_('COM_JREALTIME_CURRENT_VERSION') . $this->currentVersion;?></label>
			 		</div>
			 		
			 		<div class="single_container">
				 		<label class="label label-info"><?php echo JText::_('COM_JREALTIME_AUTHOR_COMPONENT');?></label>
			 		</div>
			 		
			 		<div class="single_container">
				 		<label class="label label-info"><?php echo JText::_('COM_JREALTIME_SUPPORTLINK');?></label>
			 		</div>
			 		
			 		<div class="single_container">
				 		<label class="label label-info"><?php echo JText::_('COM_JREALTIME_DEMOLINK');?></label>
			 		</div>
				</div>
		    </div>
	 	</div>
	</div>
</div>

<div class="clr vspacer"></div>

<div class="row no-margin">
	<div class="accordion-group span6">
		<div class="accordion-heading opened">
			<div class="accordion-toggle accordion_lightblue noaccordion">
				<h3><span class="icon-dashboard"></span><?php echo JText::_('COM_JREALTIME_CPANEL_SITESPEED' ); ?>
					<button id="execute_sitespeed_test" class="btn btn-primary hasPopover" data-content="Test <?php echo JUri::root();?>">
						<span class="icon-loop"></span><?php echo JText::_('COM_JREALTIME_TESTNOW');?> 
					</button>
				</h3>
			</div>
		</div>
		<div id="sitespeed_container" data-bind="{jr-sitespeed}" class="placeholder_lowstats accordion-body accordion-inner collapse in"></div>
	</div>
	
	<div class="accordion-group span6">
		<div class="accordion-heading opened">
			<div class="accordion-toggle accordion_lightblue noaccordion">
				<h3><span class="icon-picture"></span><?php echo JText::sprintf('COM_JREALTIME_CPANEL_VISITSMAP', $this->cParams->get('cpanelstats_period_interval', 'week')); ?></h3>
			</div>
		</div>
		<div data-bind="{jr-mapcharts}" class="placeholder_lowstats accordion-body accordion-inner collapse in"></div>
	</div>
</div>
	
	

<form name="adminForm" id="adminForm" action="index.php">
	<input type="hidden" name="option" value="<?php echo $this->option;?>"/>
	<input type="hidden" name="task" value=""/>
</form>