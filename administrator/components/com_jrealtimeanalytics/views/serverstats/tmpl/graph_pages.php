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
<table id="jrealtime_table_serverstats_pages" class="adminlist table table-striped table-hover">
	<thead>
		<tr>
			<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_PAGE');?></span></th>
			<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_LASTVISIT');?></span></th>
			<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_NUMVISITS');?></span></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($this->data[VISITSPERPAGE] as $page):?> 
			<tr>
				<td><a target="_blank" href="<?php echo $page[2];?>"><?php echo $page[2];?> <span class="icon-out"></span></a></td>
				<td><?php echo date('Y-m-d H:i:s', $page[1]);?></td> 
				<td><a class="preview badge badge-info" href="<?php echo JRoute::_('index.php?option=com_jrealtimeanalytics&task=serverstats.showEntity&tmpl=component&details=page&identifier=' . rawurlencode($page[2]));?>" class="preview"><?php echo $page[0];?></a></td>
			</tr>
		<?php endforeach;?> 
	</tbody>
</table>
					

  