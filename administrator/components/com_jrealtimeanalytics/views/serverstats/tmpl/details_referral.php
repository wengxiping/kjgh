<?php 
/** 
 * @package JREALTIMEANALYTICS::SERVERSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage serverstats
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );  ?>
<div class="row tablestats no-margin">
	<div class="accordion-group">
		<div class="accordion-heading">
			<div class="accordion-toggle accordion_lightblue noaccordion" data-toggle="collapse">
				<h3><?php echo JText::sprintf('COM_JREALTIME_SERVERSTATS_REFERRAL_DETAILS', '<span class="badge badge-info">' . $this->app->input->getString('identifier')) . '</span>'; ?></h3>
			</div>
		</div>
		<div class="accordion-body accordion-inner collapse fancybox">
			<table id="jrealtime_table_serverstats_details_referral" class="adminlist table table-striped table-hover">
				<thead>
					<tr>
						<th><span class="label label-info"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_IPADDRESS');?></span></th>
						<th><span class="label label-info"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_GEOLOCATION_STATS');?></span></th>
						<th><span class="label label-info"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_USERS_DETAILS_LASTVISIT');?></span></th>
					</tr>
				</thead>
				<tbody>
					<?php 
						foreach ($this->detailData as $index=>$userDetail):
					?> 
						<tr>
							<td><a data-title="<?php echo JText::_('COM_JREALTIME_DETAILS');?>" class="hasTooltip" target="_blank" href="http://whois.domaintools.com/<?php echo $userDetail->ip;?>"><?php echo $userDetail->ip;?></a></td>
							<td><?php if($userDetail->geolocation) : ?>
									<?php echo $userDetail->geolocation;?> 
									<img onerror="this.style.display='none'" src="<?php echo $this->livesite;?>administrator/components/com_jrealtimeanalytics/images/flags/<?php echo strtolower($userDetail->geolocation);?>.png"/>
								<?php else : ?>
									<?php echo JText::_('COM_JREALTIME_NOTSET');?>
								<?php endif; ?>
							</td>
							<td><?php echo $userDetail->record_date;?></td>
						</tr>
					<?php 
						endforeach;
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<a class="headstats btn btn-primary csv" download href="index.php?option=com_jrealtimeanalytics&amp;task=serverstats.showEntitycsv&amp;tmpl=component&amp;details=referral&amp;identifier=<?php echo rawurlencode($this->app->input->getString('identifier'));?>">
	<span class="icon-chart"></span>
	<?php echo JText::_('COM_JREALTIME_EXPORTCSV' ); ?>
</a>
<a class="headstats btn btn-primary xls" download href="index.php?option=com_jrealtimeanalytics&amp;task=serverstats.showEntityxls&amp;tmpl=component&amp;details=referral&amp;identifier=<?php echo rawurlencode($this->app->input->getString('identifier'));?>">
	<span class="icon-chart"></span>
	<?php echo JText::_('COM_JREALTIME_EXPORTXLS' ); ?>
</a>
<a class="headstats btn btn-primary" download href="index.php?option=com_jrealtimeanalytics&amp;task=serverstats.showEntitypdf&amp;tmpl=component&amp;details=referral&amp;identifier=<?php echo rawurlencode($this->app->input->getString('identifier'));?>">
	<span class="icon-chart"></span>
	<?php echo JText::_('COM_JREALTIME_EXPORTPDF' ); ?>
</a>