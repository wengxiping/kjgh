<?php 
/** 
 * @package JREALTIMEANALYTICS::REALSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage realstats
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );  ?>
<div class="row no-margin">
	<div class="accordion-group responsivestats span4">
		<div class="accordion-heading opened">
			<div class="accordion-toggle accordion_lightblue noaccordion">
				<h3><span class="icon-chart"></span><?php echo JText::_('COM_JREALTIME_TEXTSTATSTITLE' ); ?></h3>
			</div>
		</div>
		<div id="placeholder_textstats" class="accordion-body accordion-inner collapse in"></div>
	</div>
	
	<div class="accordion-group responsivestats span4">
		<div class="accordion-heading opened">
			<div class="accordion-toggle accordion_lightblue noaccordion">
				<h3><span class="icon-pie"></span><?php echo JText::_('COM_JREALTIME_PIEGRAPHTITLE' ); ?></h3>
			</div>
		</div>
		<div id="placeholder_chartpie" class="accordion-body accordion-inner collapse in"></div>
	</div>
	
	<div class="accordion-group responsivestats span4">
		<div class="accordion-heading opened">
			<div class="accordion-toggle accordion_lightblue noaccordion">
				<h3><span class="icon-bars"></span><?php echo JText::_('COM_JREALTIME_BARGRAPHTITLE' ); ?></h3>
			</div>
		</div>
		<div id="placeholder_chartbar" class="accordion-body accordion-inner collapse in"></div>
	</div>
</div>

<div class="accordion" id="jrealtime_realstats_accordion">
	<div class="row tablestats no-sorting no-margin">
		<div class="accordion-group span12">
			<div class="accordion-heading">
				<div class="accordion-toggle accordion_lightblue" data-toggle="collapse" data-parent="#jrealtime_realstats_accordion" href="#jrealtime_realstats_byuser">
					<h3><span class="icon-users"></span><?php echo JText::_('COM_JREALTIME_USERSSTATSTITLE' ); ?></h3>
				</div>
			</div>
			<div id="jrealtime_realstats_byuser" class="accordion-body accordion-inner collapse" data-height="350">
				<table class="adminlist table table-striped table-hover">
					<thead>
						<tr>
							<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_TITLENAME');?></span></th>
							<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_TITLEUSERNAME');?></span></th>
							<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_TITLETYPE');?></span></th>
							<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_TITLETIME');?></span></th>
							<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_GEOLOCATION');?></span></th>
							<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_DEVICE');?></span></th>
							<th style="width:40%" class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_TITLENOWPAGE');?></span></th>
						</tr>
					</thead>
					<tbody id="placeholder_text"></tbody>
				</table>
			</div>
		</div>
	</div>
	
	<div class="row tablestats no-sorting no-margin">
		<div class="accordion-group span12">
			<div class="accordion-heading">
				<div class="accordion-toggle accordion_lightblue" data-toggle="collapse" data-parent="#jrealtime_realstats_accordion" href="#jrealtime_realstats_bypage">
					<h3><span class="icon-copy"></span><?php echo JText::_('COM_JREALTIME_PERPAGESTATSTITLE' ); ?></h3>
				</div>
			</div>
			<div id="jrealtime_realstats_bypage" class="accordion-body accordion-inner collapse" data-height="350">
				<table class="adminlist table table-striped table-hover">
					<thead>
						<tr>
							<th style="width:40%" class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_TITLENOWPAGE');?></span></th>
							<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_TITLENUMUSERS');?></span></th>
							<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_TITLELASTVISIT');?></span></th>
						</tr>
					</thead>
					<tbody id="placeholder_perpage"></tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<form action="index.php" method="post" id="adminForm" name="adminForm"> 
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="realstats.display" />   
</form>

<!-- Go to bottom -->
<div class="label label-default" id="gobottom">
	<span class="icon-arrow-down"></span> <?php echo JText::_('COM_JREALTIME_GO_TO_BOTTOM');?>
</div>

<!-- Back to top -->
<div class="label label-default" id="backtop">
	<span class="icon-arrow-up"></span> <?php echo JText::_('COM_JREALTIME_BACK_TO_TOP');?>
</div>