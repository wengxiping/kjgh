<?php 
/** 
 * @package JREALTIMEANALYTICS::EVENTSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage eventstats
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
?>
<style>
	div.statslabel {
		margin-bottom: 10px;
	}
	.label {
		display: inline-block;
		padding: 4px 4px;
		font-size: 15px;
		font-weight: bold;
		color: #fff;
		white-space: nowrap;
		text-shadow: 0 -1px 0 rgba(0,0,0,0.25);
		background-color: #3a87ad;
		height: 20px;
		width: 200px;
	}
	.label-info {
		font-size: 12px;
	}
	.badge {
		background-color: #FFF;
		color: #3a87ad !important;
		padding: 15px;
	}
</style>

<br/><br/>
<img src="<?php echo JUri::root();?>administrator/components/com_jrealtimeanalytics/images/icon-48-statspdf.png"/>&nbsp;&nbsp;&nbsp;&nbsp;<b style="font-size:18px;">
	<?php echo JText::_('COM_JREALTIME_EVENT_REPORT' ) . JText::sprintf('COM_JREALTIME_EVENT_OCCURENCES', count($this->eventDetails)); ?>
	<br/>
	<?php echo '<span class="label label-info">' . $this->record->name . '</span>';?>
</b>
<hr/>
<table>
	<tr>
		<td width="20%">
			<b style="font-size:14px;color: #3a87ad;"><?php echo JText::_('COM_JREALTIME_EVENTTITLE_USERNAME');?></b><br/>
		</td>
		<td width="20%">
			<b style="font-size:14px;color: #3a87ad;"><?php echo JText::_('COM_JREALTIME_EVENTTITLE_EVENTDATE');?></b>
		</td>
		<td width="13%">
			<b style="font-size:13px;color: #3a87ad;"><?php echo JText::_('COM_JREALTIME_EVENTTITLE_GEOLOCATION');?></b>
		</td>
		<?php if(!$this->cparams->get('anonymize_ipaddress', 0)):?>
			<td width="13%">
				<b style="font-size:13px;color: #3a87ad;"><?php echo JText::_('COM_JREALTIME_EVENTTITLE_IPADDRESS');?></b>
			</td>
		<?php endif;?>
		<td width="13%">
			<b style="font-size:13px;color: #3a87ad;"><?php echo JText::_('COM_JREALTIME_EVENTTITLE_BROWSER');?></b>
		</td>
		<td width="15%">
			<b style="font-size:13px;color: #3a87ad;"><?php echo JText::_('COM_JREALTIME_EVENTTITLE_OS');?></b>
		</td>
	</tr>
	<?php foreach ($this->eventDetails as $index=>$eventDetail):?> 
		<tr>
			<td><?php echo $eventDetail->customer_name ? $eventDetail->customer_name : JText::_('COM_JREALTIME_NOTSET');?></td>
			<td><?php echo date('Y-m-d H:i:s', $eventDetail->event_timestamp);?></td>
			<td><?php echo $eventDetail->geolocation ? $eventDetail->geolocation . ' <img src="' . JPATH_ROOT . '/administrator/components/com_jrealtimeanalytics/images/flags/' . strtolower($eventDetail->geolocation) . '.png"/>' : JText::_('COM_JREALTIME_NOTSET');?></td>
			<?php if(!$this->cparams->get('anonymize_ipaddress', 0)):?>
				<td><?php echo $eventDetail->ip ? $eventDetail->ip : JText::_('COM_JREALTIME_NOTSET');?></td>
			<?php endif;?>
			<td><?php echo $eventDetail->browser ? $eventDetail->browser : JText::_('COM_JREALTIME_NOTSET');?></td>
			<td><?php echo $eventDetail->os ? $eventDetail->os : JText::_('COM_JREALTIME_NOTSET');?></td>
		</tr>
	<?php endforeach;?> 
</table>