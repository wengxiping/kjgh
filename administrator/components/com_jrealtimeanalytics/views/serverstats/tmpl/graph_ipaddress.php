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
<table id="jrealtime_table_serverstats_ipaddress" class="adminlist table table-striped table-hover">
	<thead>
		<tr>
			<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_IPADDRESS');?></span></th>
			<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_EVENTTITLE_GEOLOCATION');?></span></th>
			<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_LASTVISIT');?></span></th>
			<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_VISIT_LIFE');?></span></th>
			<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_VISITED_PAGES');?></span></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($this->data[TOTALVISITEDPAGESPERIPADDRESS] as $user):?> 
			<tr>
				<td><a data-ip="<?php echo $user[2];?>" class="hasClickPopover" href="javascript:void(0);"><?php echo $user[2];?></a></td>
				<td><?php echo $user[4];?> <img onerror="this.style.display='none'" src="<?php echo $this->livesite;?>administrator/components/com_jrealtimeanalytics/images/flags/<?php echo strtolower($user[4]);?>.png"/></td>
				<td><?php echo date('Y-m-d H:i:s', $user[1]);?></td>
				<td><?php echo gmdate('H:i:s', $user[3] * $this->cparams->get('daemonrefresh'));?></td>
				<td><a class="preview badge badge-info" href="index.php?option=com_jrealtimeanalytics&amp;task=serverstats.showEntity&amp;tmpl=component&amp;details=ip&amp;identifier=<?php echo $user[2];?>" class="preview"><?php echo $user[0];?></a></td>
			</tr>
		<?php endforeach;?> 
	</tbody>
</table>  