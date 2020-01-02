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
$rowSpacer = '<p></p>';
?>
<font size="4" color="#0028D3">
	<?php echo JText::_('COM_JREALTIME_EVENT_REPORT' ) . JText::sprintf('COM_JREALTIME_EVENT_OCCURENCES', count($this->eventDetails)) . $rowSpacer; ?>
	<?php echo '<font size="3" color="##0028D3"><b>' . $this->record->name . '</font>';?>
</font>

<table>
	<tr>
		<td>
			<font size="3" color="#CE1300"><b><?php echo JText::_('COM_JREALTIME_EVENTTITLE_USERNAME');?></b></font>
		</td>
		<td>
			<font size="3" color="#CE1300"><b><?php echo JText::_('COM_JREALTIME_EVENTTITLE_EVENTDATE');?></b></font>
		</td>
		<td>
			<font size="3" color="#CE1300"><b><?php echo JText::_('COM_JREALTIME_EVENTTITLE_GEOLOCATION');?></b></font>
		</td>
		<?php if(!$this->cparams->get('anonymize_ipaddress', 0)):?>
			<td>
				<font size="3" color="#CE1300"><b><?php echo JText::_('COM_JREALTIME_EVENTTITLE_IPADDRESS');?></b></font>
			</td>
		<?php endif;?>
		<td>
			<font size="3" color="#CE1300"><b><?php echo JText::_('COM_JREALTIME_EVENTTITLE_BROWSER');?></b></font>
		</td>
		<td>
			<font size="3" color="#CE1300"><b><?php echo JText::_('COM_JREALTIME_EVENTTITLE_OS');?></b></font>
		</td>
	</tr>
	<?php foreach ($this->eventDetails as $index=>$eventDetail):?> 
		<tr>
			<td><?php echo $eventDetail->customer_name ? $eventDetail->customer_name : JText::_('COM_JREALTIME_NOTSET');?></td>
			<td><?php echo date('Y-m-d H:i:s', $eventDetail->event_timestamp);?></td>
			<td><?php echo $eventDetail->geolocation ? $eventDetail->geolocation : JText::_('COM_JREALTIME_NOTSET');?></td>
			<?php if(!$this->cparams->get('anonymize_ipaddress', 0)):?>
				<td><?php echo $eventDetail->ip ? "( " . $eventDetail->ip . " )" : JText::_('COM_JREALTIME_NOTSET');?></td>
			<?php endif;?>
			<td><?php echo $eventDetail->browser ? $eventDetail->browser : JText::_('COM_JREALTIME_NOTSET');?></td>
			<td><?php echo $eventDetail->os ? $eventDetail->os : JText::_('COM_JREALTIME_NOTSET');?></td>
		</tr>
	<?php endforeach;?> 
</table>