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
<table id="jrealtime_table_serverstats_landing" class="adminlist table table-striped table-hover">
	<thead>
		<tr>
			<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_PAGE');?></span></th>
			<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_NUMUSERS');?></span></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($this->data[LANDING_PAGES] as $page):?> 
		<tr>
			<td><?php echo $page[1];?></td>
			<td><?php echo $page[0];?></td> 
		</tr>
	<?php endforeach;?> 
  	</tbody>
</table>