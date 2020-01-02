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
<table id="jrealtime_table_serverstats_searches" class="adminlist table table-striped table-hover">
	<thead>
		<tr>
			<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_SEARCHES_PHRASE');?></span></th>
			<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_SEARCHES_COUNTER');?></span></th>
			<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_SEARCHES_PERCENTAGE');?></span></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($this->data[SEARCHEDPHRASE] as $phrase):?> 
			<tr>
				<td><?php echo $phrase[0];?></td>
				<td><?php echo $phrase[1];?></td>
				<td><?php printf('%.2f', ($phrase[1] / $phrase[2]) * 100);?>%</td>
			</tr>
		<?php endforeach;?> 
	</tbody>
</table>