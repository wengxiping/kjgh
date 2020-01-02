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
		<div class="accordion-heading opened">
			<div class="accordion-toggle noaccordion">
				<h4><span class="icon-chart"></span><?php echo JText::_('COM_JREALTIME_EVENT_REPORT' ) . JText::sprintf('COM_JREALTIME_EVENT_OCCURENCES', count($this->eventDetails)); ?></h4>
			</div>
		</div>
		<div class="accordion-body accordion-inner collapse fancybox">
			<table id="jrealtime_table_eventstats_report_stats" class="adminlist table table-striped table-hover">
				<thead>
					<tr>
						<th><span class="label label-info"><?php echo JText::_('COM_JREALTIME_EVENTTITLE_USERNAME');?></span></th>
						<th><span class="label label-info"><?php echo JText::_('COM_JREALTIME_EVENTTITLE_EVENTDATE');?></span></th>
						<th><span class="label label-info"><?php echo JText::_('COM_JREALTIME_EVENTTITLE_GEOLOCATION');?></span></th>
						<?php if(!$this->cparams->get('anonymize_ipaddress', 0)):?>
							<th><span class="label label-info"><?php echo JText::_('COM_JREALTIME_EVENTTITLE_IPADDRESS');?></span></th>
						<?php endif;?>
						<th><span class="label label-info"><?php echo JText::_('COM_JREALTIME_EVENTTITLE_BROWSER');?></span></th>
						<th><span class="label label-info"><?php echo JText::_('COM_JREALTIME_EVENTTITLE_OS');?></span></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($this->eventDetails as $index=>$eventDetail):?> 
						<tr>
							<td><?php echo $eventDetail->customer_name ? $eventDetail->customer_name : JText::_('COM_JREALTIME_NOTSET');?></td>
							<td><label class="hasPopover" data-content="<?php echo date('Y-m-d H:i:s', $eventDetail->event_timestamp);?>"><?php echo date('Y-m-d H:i:s', $eventDetail->event_timestamp);?></label></td>
							<td><?php echo $eventDetail->geolocation ? $eventDetail->geolocation . ' <img onerror="this.style.display=\'none\'" src="' . $this->livesite . '/administrator/components/com_jrealtimeanalytics/images/flags/' . strtolower($eventDetail->geolocation) . '.png"/>' : JText::_('COM_JREALTIME_NOTSET');?></td>
							<?php if(!$this->cparams->get('anonymize_ipaddress', 0)):?>
								<td>
									<?php if($eventDetail->ip):?>
										<a data-title="<?php echo JText::_('COM_JREALTIME_DETAILS');?>" class="hasTooltip" target="_blank" href="http://whois.domaintools.com/<?php echo $eventDetail->ip;?>"><?php echo $eventDetail->ip;?></a>
									<?php else:
										echo JText::_('COM_JREALTIME_NOTSET');
										endif;
									?>
								</td>
							<?php endif;?>
							<td><?php echo $eventDetail->browser ? $eventDetail->browser : JText::_('COM_JREALTIME_NOTSET');?></td>
							<td><?php echo $eventDetail->os ? $eventDetail->os : JText::_('COM_JREALTIME_NOTSET');?></td>
						</tr>
					<?php endforeach;?> 
				</tbody>
			</table>
		</div>
	</div>
</div>