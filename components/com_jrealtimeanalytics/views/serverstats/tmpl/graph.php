<?php
/** 
 * @package JREALTIMEANALYTICS::SERVERSTATS::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage serverstats
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

// title
if ( $this->cparams->get ( 'show_page_heading', 0 )) {
	$title = $this->cparams->get ( 'page_heading', $this->menuTitle);
	echo '<h1>' . $title . '</h1>';
}
$cssClass = $this->cparams->get ( 'pageclass_sfx', null);
?>
<form action="<?php echo JRoute::_('index.php?option=com_jrealtimeanalytics&task=serverstats.display');?>" method="post" class="jes jesform <?php echo $cssClass;?>" id="adminForm" name="adminForm">
	<?php if($this->canExport):?>
		<div class="btn-toolbar well" id="toolbar">
			<div class="btn-wrapper pull-left" id="toolbar-download">
				<button onclick="jQuery.submitbutton('serverstats.displaycsv')" class="btn btn-primary btn-xs">
					<span class="glyphicon glyphicon-download-alt"></span> <?php echo JText::_('COM_JREALTIME_EXPORTCSV');?>
				</button>
			</div>
			<div class="btn-wrapper pull-left" id="toolbar-download">
				<button onclick="jQuery.submitbutton('serverstats.displayxls')" class="btn btn-primary btn-xs">
					<span class="glyphicon glyphicon-download-alt"></span> <?php echo JText::_('COM_JREALTIME_EXPORTXLS');?>
				</button>
			</div>
			<div class="btn-wrapper pull-left" id="toolbar-download">
				<button onclick="jQuery.submitbutton('serverstats.displaypdf')" class="btn btn-primary btn-xs">
					<span class="glyphicon glyphicon-download-alt"></span> <?php echo JText::_('COM_JREALTIME_EXPORTPDF');?>
				</button>
			</div>
		</div>
	<?php endif;?>
	<div class="headerlist well">
		<?php if($this->cparams->get('show_date_filters', true)):?>
			<div class="input-prepend active">
				<span class="add-on"><span class="icon-calendar"></span> <?php echo JText::_('COM_JREALTIME_FILTER_BY_DATE_FROM' ); ?>:</span>
				<input type="text" name="fromperiod" id="fromPeriod" data-role="calendar" value="<?php echo $this->dates['start'];?>" class="text_area"/>
			</div>
			
			<div class="input-prepend active">
				<span class="add-on"><span class="icon-calendar"></span> <?php echo JText::_('COM_JREALTIME_FILTER_BY_DATE_TO' ); ?>:</span>
				<input type="text" name="toperiod" id="toPeriod" data-role="calendar" value="<?php echo $this->dates['to'];?>" class="text_area"/>
			</div>
			<button class="btn btn-primary btn-xs" onclick="this.form.submit();"><?php echo JText::_('COM_JREALTIME_GO' ); ?></button>
		<?php else:?>
			<?php echo JText::_('COM_JREALTIME_FILTER_BY_DATE_FROM' ); ?>:
			<span class="label label-primary"><?php echo $this->dates['start'];?></span>
			<?php echo JText::_('COM_JREALTIME_FILTER_BY_DATE_TO' ); ?>:
			<span class="label label-primary"><?php echo $this->dates['to'];?></span>
			<div class="clr"></div>
		<?php endif;?>
		<div class="clearfix"></div>
		<div class="input-prepend active blockfield">
			<span class="add-on"><span class="icon-filter"></span> <?php echo JText::_('COM_JREALTIME_GRAPH_THEME' ); ?>:</span>
			<?php echo $this->lists['graphTheme'];?> 
		</div>
	</div>
	 
	<div class="row tablestats no-margin">
		<?php if($this->cparams->get('details_stats', true)): ?>
			<div class="accordion-group responsivestats">
				<div class="accordion-heading opened">
					<div class="accordion-toggle accordion_lightblue noaccordion">
						<h3><span class="icon-chart"></span><?php echo JText::_('COM_JREALTIME_SERVERSTATS_DETAILS' ); ?></h3>
					</div>
				</div>
				<div class="accordion-body accordion-inner collapse in"><?php echo $this->loadTemplate('details');?></div>
			</div>
		<?php endif;?>
	
		<?php if($this->cparams->get('geolocation_stats', true)): ?>
			<div class="accordion-group responsivestats">
				<div class="accordion-heading opened">
					<div class="accordion-toggle accordion_lightblue noaccordion">
						<h3>
							<span class="icon-picture"></span><?php echo JText::_('COM_JREALTIME_SERVERSTATS_GEOLOCATION' ); ?>
							<button id="open_visualmap" class="btn btn-primary pull-right" href="#fancybox_visualmap">
								<span class="icon-chart"></span>
								<?php echo JText::_('COM_JREALTIME_VISUALMAP_OPEN' ); ?>
							</button>
						</h3>
					</div>
				</div>
				<div class="accordion-body accordion-inner collapse in"><?php echo $this->loadTemplate('geolocation');?></div>
			</div>
		<?php endif;?>
	</div>
	
	<div class="row tablestats no-margin">
		<?php if($this->cparams->get('os_stats', true)): ?>
			<div class="accordion-group responsivestats">
				<div class="accordion-heading opened">
					<div class="accordion-toggle accordion_lightblue noaccordion">
						<h3><span class="icon-cog"></span><?php echo JText::_('COM_JREALTIME_SERVERSTATS_OS' ); ?></h3>
					</div>
				</div>
				<div class="accordion-body accordion-inner collapse in"><?php echo $this->loadTemplate('os');?></div>
			</div>
		<?php endif;?>
	
		<?php if($this->cparams->get('browser_stats', true)): ?>
			<div class="accordion-group responsivestats">
				<div class="accordion-heading opened">
					<div class="accordion-toggle accordion_lightblue noaccordion">
						<h3><span class="icon-compass"></span><?php echo JText::_('COM_JREALTIME_SERVERSTATS_BROWSER' ); ?></h3>
					</div>
				</div>
				<div class="accordion-body accordion-inner collapse in"><?php echo $this->loadTemplate('browser');?></div>
			</div>
		<?php endif;?>
		
		<?php if($this->cparams->get('device_stats', true)): ?>
			<div class="accordion-group responsivestats">
				<div class="accordion-heading opened">
					<div class="accordion-toggle accordion_lightblue noaccordion">
						<h3><span class="icon-compass"></span><?php echo JText::_('COM_JREALTIME_SERVERSTATS_DEVICE' ); ?></h3>
					</div>
				</div>
				<div class="accordion-body accordion-inner collapse in"><?php echo $this->loadTemplate('device');?></div>
			</div>
		<?php endif;?>
	</div>
	
	<div class="row tablestats no-margin">
		<?php if($this->cparams->get('landing_stats', true)): ?>
		<div class="accordion-group responsivestats">
			<div class="accordion-heading opened">
				<div class="accordion-toggle accordion_lightblue noaccordion accordion_filtered">
					<h3><span class="icon-copy"></span><?php echo JText::_('COM_JREALTIME_LANDING_PAGES' ); ?></h3>
					<input type="text" data-role="filter" data-field="1" placeholder="<?php echo JText::_('COM_JREALTIME_FILTER_BY_PAGE' );?>"/>
					<button data-role="reset-filter" class="btn btn-default btn-xs"><?php echo JText::_('COM_JREALTIME_RESET' ); ?></button>
				</div>
			</div>
			<div class="accordion-body noaccordion accordion-inner collapse in"><?php echo $this->loadTemplate('landing');?></div>
		</div>
		<?php endif; ?>
	
		<?php if($this->cparams->get('leaveoff_stats', true)): ?>
		<div class="accordion-group responsivestats">
			<div class="accordion-heading opened">
				<div class="accordion-toggle accordion_lightblue noaccordion accordion_filtered">
					<h3><span class="icon-copy"></span><?php echo JText::_('COM_JREALTIME_LEAVEOFF_PAGES' ); ?></h3>
					<input type="text" data-role="filter" data-field="1" placeholder="<?php echo JText::_('COM_JREALTIME_FILTER_BY_PAGE' );?>"/>
					<button data-role="reset-filter" class="btn btn-default btn-xs"><?php echo JText::_('COM_JREALTIME_RESET' ); ?></button>
				</div>
			</div>
			<div class="accordion-body noaccordion accordion-inner collapse in"><?php echo $this->loadTemplate('leaved');?></div>
		</div>
		<?php endif; ?>
	</div>
	
	<div class="accordion" id="jrealtime_serverstats_accordion">
		<?php if($this->cparams->get('visitsbypage_stats', true)): ?>
		<div class="row tablestats no-margin">
			<div class="accordion-group">
				<div class="accordion-heading">
					<div class="accordion-toggle accordion_lightblue noaccordion accordion_filtered" data-toggle="collapse" data-parent="#jrealtime_serverstats_accordion" href="#jrealtime_serverstats_pages">
						<h3><span class="icon-copy"></span><?php echo JText::_('COM_JREALTIME_SERVERSTATS_PAGES' ); ?></h3>
						<input type="text" data-role="filter" data-field="1" placeholder="<?php echo JText::_('COM_JREALTIME_FILTER_BY_PAGE' );?>"/>
						<button data-role="reset-filter" class="btn btn-default btn-xs"><?php echo JText::_('COM_JREALTIME_RESET' ); ?></button>
					</div>
				</div>
				<div id="jrealtime_serverstats_pages" class="accordion-body accordion-inner collapse" data-height="350">
					<?php echo $this->loadTemplate('pages');?>
				</div>
			</div>
		</div>
		<?php endif; ?>
		
		<?php if($this->cparams->get('visitsbyuser_stats', true)): ?>
		<div class="row tablestats no-margin">
			<div class="accordion-group">
				<div class="accordion-heading">
					<div class="accordion-toggle accordion_lightblue noaccordion accordion_filtered" data-toggle="collapse" data-parent="#jrealtime_serverstats_accordion" href="#jrealtime_serverstats_visitors">
						<h3><span class="icon-users"></span><?php echo JText::_('COM_JREALTIME_SERVERSTATS_USERS' ); ?></h3>
						<input type="text" data-role="filter" data-field="1" placeholder="<?php echo JText::_('COM_JREALTIME_FILTER_BY_NAME' );?>"/>
						<button data-role="reset-filter" class="btn btn-default btn-xs"><?php echo JText::_('COM_JREALTIME_RESET' ); ?></button>
					</div>
				</div>
				<div id="jrealtime_serverstats_visitors" class="accordion-body accordion-inner collapse" data-height="350">
					<?php echo $this->loadTemplate('visitors');?>
				</div>
			</div>
		</div>
		<?php endif; ?>
		
		<?php if($this->cparams->get('visitsbyip_stats', true)): ?>
		<div class="row tablestats no-margin">
			<div class="accordion-group">
				<div class="accordion-heading">
					<div class="accordion-toggle accordion_lightblue noaccordion accordion_filtered" data-toggle="collapse" data-parent="#jrealtime_serverstats_accordion" href="#jrealtime_serverstats_ipaddress">
						<h3><span class="icon-location"></span><?php echo JText::_('COM_JREALTIME_SERVERSTATS_VISITSBY_IPADDRESS' ); ?></h3>
						<input type="text" data-role="filter" data-field="1" placeholder="<?php echo JText::_('COM_JREALTIME_FILTER_BY_IPADDRESS' );?>"/>
						<button data-role="reset-filter" class="btn btn-default btn-xs"><?php echo JText::_('COM_JREALTIME_RESET' ); ?></button>
					</div>
				</div>
				<div id="jrealtime_serverstats_ipaddress" class="accordion-body accordion-inner collapse" data-height="350">
					<?php echo $this->loadTemplate('ipaddress');?>
				</div>
			</div>
		</div>
		<?php endif; ?>
		
		<?php if($this->cparams->get('referral_stats', true)): ?>
		<div class="row tablestats no-margin">
			<div class="accordion-group">
				<div class="accordion-heading">
					<div class="accordion-toggle accordion_lightblue noaccordion accordion_filtered" data-toggle="collapse" data-parent="#jrealtime_serverstats_accordion" href="#jrealtime_serverstats_referral">
						<h3><span class="icon-contract"></span><?php echo JText::_('COM_JREALTIME_SERVERSTATS_REFERRAL' ); ?></h3>
						<input type="text" data-role="filter" data-field="1" placeholder="<?php echo JText::_('COM_JREALTIME_FILTER_BY_SOURCE' );?>"/>
						<input type="text" data-role="filter" data-field="2" placeholder="<?php echo JText::_('COM_JREALTIME_FILTER_BY_IPADDRESS' );?>"/>
						<button data-role="reset-filter" class="btn btn-default btn-xs"><?php echo JText::_('COM_JREALTIME_RESET' ); ?></button>
					</div>
				</div>
				<div id="jrealtime_serverstats_referral" class="accordion-body accordion-inner collapse" data-height="350">
					<?php echo $this->loadTemplate('referral');?>
				</div>
			</div>
		</div>
		<?php endif; ?>
		
		<?php if($this->cparams->get('searchkeys_stats', true)): ?>
		<div class="row tablestats no-margin">
			<div class="accordion-group">
				<div class="accordion-heading">
					<div class="accordion-toggle accordion_lightblue noaccordion accordion_filtered" data-toggle="collapse" data-parent="#jrealtime_serverstats_accordion" href="#jrealtime_serverstats_searches">
						<h3><span class="icon-search"></span><?php echo JText::_('COM_JREALTIME_SERVERSTATS_SEARCHES' ); ?></h3>
						<input type="text" data-role="filter" data-field="1" placeholder="<?php echo JText::_('COM_JREALTIME_FILTER_BY_KEYWORDS' );?>"/>
						<button data-role="reset-filter" class="btn btn-default btn-xs"><?php echo JText::_('COM_JREALTIME_RESET' ); ?></button>
					</div>
				</div>
				<div id="jrealtime_serverstats_searches" class="accordion-body accordion-inner collapse" data-height="350">
					<?php echo $this->loadTemplate('searches');?>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>
	
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="boxchecked" value="1"/>
	<input type="hidden" name="task" value="serverstats.display" />   
	
	<div class="fancybox geomap">
		<div id="fancybox_visualmap" data-bind="geomap"></div>
	</div>
</form>
